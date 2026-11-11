<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateVariants')
                ->label('Generate variants')
                ->icon('heroicon-o-squares-plus')
                ->visible(fn () => $this->record->isVariable())
                ->schema([
                    TextInput::make('price')->numeric()->prefix('₹')->required(),
                    TextInput::make('stock_qty')->numeric()->default(0)->label('Stock per variant'),
                ])
                ->modalDescription('Creates a variant for every combination of the attributes saved on this product. Existing combinations are skipped.')
                ->action(function (array $data) {
                    $created = $this->generateVariants((float) $data['price'], (int) ($data['stock_qty'] ?? 0));

                    $this->refreshFormData(['variants']);

                    Notification::make()
                        ->title($created ? "{$created} variant(s) created" : 'No new combinations to create')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    private function generateVariants(float $price, int $stock): int
    {
        $product = $this->record->load('attributeValues', 'variants.attributeValues');

        $groups = $product->attributeValues->groupBy('attribute_id')->map->pluck('id')->values()->all();
        if (! $groups) {
            return 0;
        }

        $combos = [[]];
        foreach ($groups as $ids) {
            $next = [];
            foreach ($combos as $combo) {
                foreach ($ids as $id) {
                    $next[] = [...$combo, $id];
                }
            }
            $combos = $next;
        }

        $existing = $product->variants->map(
            fn ($v) => $v->attributeValues->pluck('id')->sort()->values()->implode('-')
        )->all();

        $created = 0;
        foreach ($combos as $combo) {
            sort($combo);
            if (in_array(implode('-', $combo), $existing, true)) {
                continue;
            }

            $variant = $product->variants()->create(['price' => $price, 'stock_qty' => $stock, 'is_active' => true]);
            $variant->attributeValues()->sync($combo);
            $created++;
        }

        return $created;
    }
}
