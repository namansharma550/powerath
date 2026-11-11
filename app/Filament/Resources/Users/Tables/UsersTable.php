<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->placeholder('—'),
                TextColumn::make('orders_count')->counts('orders')->label('Orders'),
                IconColumn::make('is_admin')->boolean()->label('Admin'),
                TextColumn::make('created_at')->label('Registered')->date()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_admin')->label('Admin'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
