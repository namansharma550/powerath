<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
        'min_subtotal' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(fn (Coupon $coupon) => $coupon->code = strtoupper(trim($coupon->code)));
    }

    /** Returns an error message when the coupon can't be applied, otherwise null. */
    public function validationError(float $subtotal, ?int $userId = null): ?string
    {
        if (! $this->is_active) {
            return 'This coupon is not active.';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'This coupon is not valid yet.';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This coupon has expired.';
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'This coupon has reached its usage limit.';
        }
        if ($this->min_subtotal !== null && $subtotal < (float) $this->min_subtotal) {
            return 'Minimum order amount for this coupon is ₹'.number_format((float) $this->min_subtotal, 2).'.';
        }
        if ($userId && $this->usage_limit_per_user !== null) {
            $used = Order::where('coupon_id', $this->id)->where('user_id', $userId)
                ->where('status', '!=', 'cancelled')->count();
            if ($used >= $this->usage_limit_per_user) {
                return 'You have already used this coupon.';
            }
        }

        return null;
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * (float) $this->value / 100
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
