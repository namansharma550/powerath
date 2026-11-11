<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_VARIABLE = 'variable';

    protected $guarded = [];

    // Mirror the column defaults so the saving hook sees them on freshly created models.
    protected $attributes = [
        'type' => self::TYPE_SIMPLE,
        'manage_stock' => true,
        'stock_qty' => 0,
        'stock_status' => 'in_stock',
        'is_active' => true,
    ];

    protected $casts = [
        'gallery' => 'array',
        'manage_stock' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (blank($product->sku)) {
                $product->sku = null; // keep unique index happy
            }
            if ($product->type === self::TYPE_SIMPLE) {
                if ($product->manage_stock) {
                    $product->stock_status = $product->stock_qty > 0 ? 'in_stock' : 'out_of_stock';
                }
                $effective = $product->isOnSale() ? $product->sale_price : $product->price;
                $product->min_price = $product->max_price = $effective;
            }
        });
    }

    /** Recompute the cached price range (variable products derive it from active variants). */
    public function syncPriceRange(): void
    {
        if ($this->type !== self::TYPE_VARIABLE) {
            return;
        }

        $prices = $this->variants()->where('is_active', true)->get()->map->final_price;

        $this->forceFill(['min_price' => $prices->min(), 'max_price' => $prices->max()])->saveQuietly();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /** Total sellable stock (null = not tracked / unlimited). */
    public function availableStock(): ?int
    {
        if ($this->isVariable()) {
            return (int) $this->variants()->where('is_active', true)->sum('stock_qty');
        }

        return $this->manage_stock ? $this->stock_qty : null;
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isVariable(): bool
    {
        return $this->type === self::TYPE_VARIABLE;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    /** Price a customer pays for a simple product. */
    public function getFinalPriceAttribute(): ?float
    {
        if ($this->isVariable()) {
            return $this->variants->where('is_active', true)->map->final_price->min();
        }

        return (float) ($this->isOnSale() ? $this->sale_price : $this->price);
    }

    public function isInStock(): bool
    {
        if ($this->isVariable()) {
            return $this->variants->where('is_active', true)->where('stock_qty', '>', 0)->isNotEmpty();
        }

        return $this->stock_status === 'in_stock';
    }
}
