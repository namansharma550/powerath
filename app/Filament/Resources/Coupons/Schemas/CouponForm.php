<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(50)
                            ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),
                        TextInput::make('description'),
                        Select::make('type')
                            ->options(['percent' => 'Percentage (%)', 'fixed' => 'Fixed amount (₹)'])
                            ->default('percent')
                            ->required(),
                        TextInput::make('value')->numeric()->required()->minValue(0),
                        TextInput::make('min_subtotal')->label('Minimum order amount')->numeric()->prefix('₹'),
                        TextInput::make('max_discount')->label('Maximum discount')->numeric()->prefix('₹')
                            ->helperText('Cap for percentage coupons.'),
                        TextInput::make('usage_limit')->label('Total usage limit')->numeric()->minValue(1),
                        TextInput::make('usage_limit_per_user')->label('Usage limit per customer')->numeric()->minValue(1),
                        DateTimePicker::make('starts_at'),
                        DateTimePicker::make('expires_at'),
                        Toggle::make('is_active')->label('Active')->default(true),
                    ]),
            ]);
    }
}
