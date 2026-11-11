<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\AttributeValue;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $isVariable = fn (Get $get) => $get('type') === 'variable';
        $isSimple = fn (Get $get) => $get('type') !== 'variable';

        return $schema
            ->columns(3)
            ->components([
                Tabs::make('Product')
                    ->columnSpan(2)
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                    if (blank($get('slug'))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                            Textarea::make('short_description')->rows(3),
                            RichEditor::make('description')->columnSpanFull(),
                        ]),

                        Tab::make('Pricing & Inventory')
                            ->visible($isSimple)
                            ->schema([
                                TextInput::make('price')->numeric()->prefix('₹')->minValue(0)
                                    ->required($isSimple),
                                TextInput::make('sale_price')->numeric()->prefix('₹')->minValue(0)
                                    ->lt('price'),
                                Toggle::make('manage_stock')->label('Track stock quantity')->default(true)->live(),
                                TextInput::make('stock_qty')->numeric()->default(0)->label('Stock quantity')
                                    ->visible(fn (Get $get) => (bool) $get('manage_stock')),
                                Select::make('stock_status')
                                    ->options(['in_stock' => 'In stock', 'out_of_stock' => 'Out of stock'])
                                    ->default('in_stock')
                                    ->visible(fn (Get $get) => ! $get('manage_stock')),
                            ]),

                        Tab::make('Variants')
                            ->visible($isVariable)
                            ->schema([
                                Repeater::make('variants')
                                    ->relationship()
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->addActionLabel('Add variant')
                                    ->helperText('Tip: select the product attributes in the sidebar, save, then use "Generate variants" at the top of the page.')
                                    ->itemLabel(fn (array $state) => $state['sku'] ?? 'Variant')
                                    ->schema([
                                        Select::make('attributeValues')
                                            ->label('Options')
                                            ->relationship('attributeValues', 'value', modifyQueryUsing: fn (Builder $query) => $query->with('attribute'))
                                            ->getOptionLabelFromRecordUsing(fn (AttributeValue $r) => $r->attribute->name.': '.$r->value)
                                            ->multiple()
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull(),
                                        TextInput::make('sku')->unique(ignoreRecord: true),
                                        TextInput::make('price')->numeric()->prefix('₹')->required(),
                                        TextInput::make('sale_price')->numeric()->prefix('₹'),
                                        TextInput::make('stock_qty')->numeric()->default(0)->label('Stock'),
                                        FileUpload::make('image')->image()->directory('variants'),
                                        Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                                    ]),
                            ]),

                        Tab::make('Images')->schema([
                            FileUpload::make('image')->label('Main image')->image()->directory('products'),
                            FileUpload::make('gallery')->label('Gallery')->image()->multiple()->reorderable()
                                ->directory('products'),
                        ]),

                        Tab::make('SEO')->schema([
                            TextInput::make('meta_title'),
                            Textarea::make('meta_description')->rows(3),
                        ]),
                    ]),

                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        Select::make('type')
                            ->options(['simple' => 'Simple product', 'variable' => 'Variable product'])
                            ->default('simple')
                            ->required()
                            ->live()
                            ->disabledOn('edit')
                            ->dehydrated(),
                        TextInput::make('sku')->label('SKU')->unique(ignoreRecord: true)
                            ->visible($isSimple),
                        Toggle::make('is_active')->label('Active')->default(true),
                        Toggle::make('is_featured')->label('Featured'),
                        Select::make('brand_id')->relationship('brand', 'name')->searchable()->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('slug')->required()->unique('brands', 'slug'),
                            ]),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('attributeValues')
                            ->label('Attributes (filters & variant options)')
                            ->relationship('attributeValues', 'value', modifyQueryUsing: fn (Builder $query) => $query->with('attribute'))
                            ->getOptionLabelFromRecordUsing(fn (AttributeValue $r) => $r->attribute->name.': '.$r->value)
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        TextInput::make('weight')->numeric()->suffix('kg'),
                    ]),
            ]);
    }
}
