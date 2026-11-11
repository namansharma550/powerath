<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('image')->square()->size(44),
                TextColumn::make('name')->searchable()->sortable()->limit(50),
                TextColumn::make('type')->badge(),
                TextColumn::make('sku')->searchable()->placeholder('—'),
                TextColumn::make('price')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->isVariable()) {
                            $prices = $record->variants->pluck('price');

                            return $prices->isEmpty() ? '—' : '₹'.$prices->min().($prices->min() != $prices->max() ? ' – ₹'.$prices->max() : '');
                        }

                        return $state === null ? '—' : '₹'.$state;
                    }),
                TextColumn::make('stock_qty')->label('Stock')
                    ->formatStateUsing(fn ($state, $record) => $record->isVariable()
                        ? $record->variants->sum('stock_qty')
                        : ($record->manage_stock ? $state : ucfirst(str_replace('_', ' ', $record->stock_status)))),
                TextColumn::make('categories.name')->badge()->limitList(2),
                ToggleColumn::make('is_featured')->label('Featured'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                SelectFilter::make('type')->options(['simple' => 'Simple', 'variable' => 'Variable']),
                SelectFilter::make('categories')->relationship('categories', 'name')->multiple()->preload(),
                SelectFilter::make('brand')->relationship('brand', 'name'),
                TernaryFilter::make('is_active')->label('Active'),
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
