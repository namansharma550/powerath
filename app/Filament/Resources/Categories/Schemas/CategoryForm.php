<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('parent_id')
                            ->label('Parent category')
                            ->relationship('parent', 'name', ignoreRecord: true)
                            ->searchable()
                            ->preload(),
                        Textarea::make('description')->rows(4),
                        TextInput::make('meta_title'),
                        TextInput::make('meta_description'),
                    ]),
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('image')->image()->directory('categories'),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_active')->label('Active')->default(true),
                    ]),
            ]);
    }
}
