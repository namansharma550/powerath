<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Order')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')->disabled(),
                        TextInput::make('created_at')->label('Placed at')->disabled(),
                        TextInput::make('customer_name')->disabled(),
                        TextInput::make('customer_email')->disabled(),
                        TextInput::make('customer_phone')->disabled(),
                        Select::make('payment_method')->options(Order::PAYMENT_METHODS)->disabled(),
                        Textarea::make('notes')->disabled()->columnSpanFull(),
                    ]),
                Section::make('Status')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')->options(Order::STATUSES)->required(),
                        Select::make('payment_status')->options(Order::PAYMENT_STATUSES)->required(),
                    ]),
                Section::make('Items')
                    ->columnSpan(2)
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->schema([
                                TextInput::make('name')->columnSpan(2)->disabled(),
                                TextInput::make('variant_label')->label('Variant')->disabled(),
                                TextInput::make('sku')->disabled(),
                                TextInput::make('price')->prefix('₹')->disabled(),
                                TextInput::make('quantity')->disabled(),
                                TextInput::make('total')->prefix('₹')->disabled(),
                            ]),
                    ]),
                Section::make('Totals')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('subtotal')->prefix('₹')->disabled(),
                        TextInput::make('coupon_code')->disabled(),
                        TextInput::make('discount')->prefix('₹')->disabled(),
                        TextInput::make('shipping')->prefix('₹')->disabled(),
                        TextInput::make('tax')->prefix('₹')->disabled(),
                        TextInput::make('total')->prefix('₹')->disabled(),
                    ]),
                Section::make('Shipping address')
                    ->columnSpan(3)
                    ->columns(3)
                    ->schema([
                        TextInput::make('shipping_address.name')->label('Name')->disabled(),
                        TextInput::make('shipping_address.phone')->label('Phone')->disabled(),
                        TextInput::make('shipping_address.line1')->label('Address line 1')->disabled(),
                        TextInput::make('shipping_address.line2')->label('Address line 2')->disabled(),
                        TextInput::make('shipping_address.city')->label('City')->disabled(),
                        TextInput::make('shipping_address.state')->label('State')->disabled(),
                        TextInput::make('shipping_address.postcode')->label('Postcode')->disabled(),
                        TextInput::make('shipping_address.country')->label('Country')->disabled(),
                    ]),
            ]);
    }
}
