<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        $sync = fn (ProductVariant $variant) => $variant->product?->syncPriceRange();

        static::saved($sync);
        static::deleted($sync);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_value_product_variant');
    }

    public function getFinalPriceAttribute(): float
    {
        $onSale = $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;

        return (float) ($onSale ? $this->sale_price : $this->price);
    }

    /** e.g. "Chocolate / 2 kg" */
    public function getLabelAttribute(): string
    {
        return $this->attributeValues->sortBy('attribute.sort_order')->pluck('value')->implode(' / ');
    }
}
