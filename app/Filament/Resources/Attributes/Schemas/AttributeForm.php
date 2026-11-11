<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                        Select::make('type')
                            ->options(['select' => 'Dropdown / list', 'button' => 'Buttons', 'color' => 'Color swatch'])
                            ->default('select')
                            ->live()
                            ->required(),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('is_filterable')->label('Show in layered navigation')->default(true),
                    ]),
                Section::make('Values')
                    ->schema([
                        Repeater::make('values')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Add value')
                            ->schema([
                                TextInput::make('value')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                                TextInput::make('slug')->required(),
                                ColorPicker::make('color')
                                    ->visible(fn (Get $get) => $get('../../type') === 'color'),
                            ]),
                    ]),
            ]);
    }
}
