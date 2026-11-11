<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                        Textarea::make('excerpt')->rows(3)->helperText('Short summary shown on cards. Leave empty to use the start of the post.'),
                        RichEditor::make('body')->required()->columnSpanFull(),
                        TextInput::make('meta_title'),
                        TextInput::make('meta_description'),
                    ]),
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('image')->label('Cover image')->image()->directory('posts'),
                        Toggle::make('is_published')->label('Published')->default(true),
                        DateTimePicker::make('published_at')
                            ->helperText('Leave empty to publish immediately. A future date schedules the post.'),
                    ]),
            ]);
    }
}
