<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::active()->where('slug', $slug)
            ->with(['brand', 'categories', 'attributeValues.attribute', 'variants' => fn ($q) => $q->where('is_active', true), 'variants.attributeValues.attribute'])
            ->firstOrFail();

        // Option groups for the variant picker, e.g. Flavour => [Chocolate, Vanilla], Size => [1kg, 2kg].
        $options = collect();
        $variantData = [];
        if ($product->isVariable()) {
            $options = $product->variants->flatMap->attributeValues
                ->unique('id')->groupBy('attribute_id')
                ->map(fn ($values) => (object) [
                    'attribute' => $values->first()->attribute,
                    'values' => $values->sortBy('sort_order')->values(),
                ])->sortBy(fn ($g) => $g->attribute->sort_order)->values();

            foreach ($product->variants as $variant) {
                $variantData[] = [
                    'id' => $variant->id,
                    'values' => $variant->attributeValues->pluck('id')->all(),
                    'price' => $variant->final_price,
                    'regular' => (float) $variant->price,
                    'on_sale' => $variant->final_price < (float) $variant->price,
                    'stock' => $variant->stock_qty,
                    'image' => $variant->image ? asset('storage/'.$variant->image) : null,
                    'sku' => $variant->sku,
                ];
            }
        }

        $related = Product::active()->where('id', '!=', $product->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->with(['brand:id,name', 'variants'])->inRandomOrder()->limit(4)->get();

        return view('products.show', compact('product', 'options', 'variantData', 'related'));
    }
}
