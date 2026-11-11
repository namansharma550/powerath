<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order')->searchable()->sortable()->weight('bold'),
                TextColumn::make('created_at')->label('Date')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('customer_name')->label('Customer')->searchable(),
                TextColumn::make('items_count')->counts('items')->label('Items'),
                TextColumn::make('total')->money('INR')->sortable(),
                TextColumn::make('payment_method')->formatStateUsing(fn ($s) => Order::PAYMENT_METHODS[$s] ?? $s)->badge(),
                TextColumn::make('payment_status')->badge()->color(fn ($s) => match ($s) {
                    'paid' => 'success', 'failed' => 'danger', 'refunded' => 'gray', default => 'warning',
                }),
                TextColumn::make('status')->badge()->color(fn ($s) => match ($s) {
                    'delivered' => 'success', 'cancelled' => 'danger', 'shipped' => 'info', 'processing' => 'primary', default => 'warning',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options(Order::STATUSES),
                SelectFilter::make('payment_status')->options(Order::PAYMENT_STATUSES),
            ])
            ->recordActions([
                EditAction::make()->label('View / update'),
            ]);
    }
}
