<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('code')->searchable()->sortable()->weight('bold'),
                TextColumn::make('type')->badge(),
                TextColumn::make('value')->formatStateUsing(
                    fn ($state, $record) => $record->type === 'percent' ? rtrim(rtrim($state, '0'), '.').'%' : '₹'.$state
                ),
                TextColumn::make('used_count')->label('Used')->formatStateUsing(
                    fn ($state, $record) => $state.($record->usage_limit ? ' / '.$record->usage_limit : '')
                ),
                TextColumn::make('expires_at')->dateTime()->placeholder('Never')->sortable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
