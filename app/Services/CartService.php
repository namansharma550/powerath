<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CartService
{
    private ?Cart $cart = null;

    public function cart(bool $create = false): ?Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        $cart = null;
        if ($id = session('cart_id')) {
            $cart = Cart::find($id);
        }
        if (! $cart && ($user = auth()->user())) {
            $cart = Cart::where('user_id', $user->id)->latest()->first();
        }
        if (! $cart && $create) {
            $cart = Cart::create(['user_id' => auth()->id(), 'session_id' => session()->getId()]);
        }
        if ($cart) {
            session(['cart_id' => $cart->id]);
        }

        return $this->cart = $cart;
    }

    /** Called after login/registration: attach the guest cart to the user, merging any saved one. */
    public function attachToUser(int $userId): void
    {
        $guest = ($id = session('cart_id')) ? Cart::find($id) : null;
        $saved = Cart::where('user_id', $userId)->when($guest, fn ($q) => $q->where('id', '!=', $guest->id))->latest()->first();

        if ($guest && $saved) {
            foreach ($guest->items as $item) {
                $this->mergeInto($saved, $item->product_id, $item->product_variant_id, $item->quantity);
            }
            $saved->update(['coupon_id' => $saved->coupon_id ?? $guest->coupon_id]);
            $guest->delete();
            $guest = $saved;
        } elseif ($guest) {
            $guest->update(['user_id' => $userId]);
        } elseif ($saved) {
            $guest = $saved;
        }

        $this->cart = null;
        if ($guest) {
            session(['cart_id' => $guest->id]);
        }
    }

    public function add(Product $product, ?ProductVariant $variant, int $qty): void
    {
        if ($product->isVariable() && (! $variant || $variant->product_id !== $product->id || ! $variant->is_active)) {
            throw new InvalidArgumentException('Please choose a valid option.');
        }
        if (! $product->is_active) {
            throw new InvalidArgumentException('This product is no longer available.');
        }

        $cart = $this->cart(true);
        $this->mergeInto($cart, $product->id, $variant?->id, $qty, true);
        $this->cart = null;
    }

    private function mergeInto(Cart $cart, int $productId, ?int $variantId, int $qty, bool $strict = false): void
    {
        $item = $cart->items()->where('product_id', $productId)->where('product_variant_id', $variantId)->first();
        $newQty = min(config('shop.max_qty_per_line'), ($item?->quantity ?? 0) + max(1, $qty));

        $product = Product::find($productId);
        $variant = $variantId ? ProductVariant::find($variantId) : null;
        if (! $product) {
            return;
        }
        $stock = $variant ? $variant->stock_qty : ($product->manage_stock ? $product->stock_qty : null);
        $inStock = $variant ? $variant->stock_qty > 0 : $product->stock_status === 'in_stock';

        if (! $inStock || ($stock !== null && $stock <= 0)) {
            if ($strict) {
                throw new InvalidArgumentException('Sorry, this item is out of stock.');
            }

            return;
        }
        if ($stock !== null && $newQty > $stock) {
            if ($strict) {
                throw new InvalidArgumentException("Only {$stock} left in stock.");
            }
            $newQty = $stock;
        }

        if ($item) {
            $item->update(['quantity' => $newQty]);
        } else {
            $cart->items()->create(['product_id' => $productId, 'product_variant_id' => $variantId, 'quantity' => $newQty]);
        }
    }

    public function updateQuantity(int $itemId, int $qty): ?string
    {
        $item = $this->cart()?->items()->find($itemId);
        if (! $item) {
            return null;
        }
        if ($qty <= 0) {
            $item->delete();

            return null;
        }

        $stock = $item->product_variant_id ? $item->variant?->stock_qty : ($item->product->manage_stock ? $item->product->stock_qty : null);
        $max = min(config('shop.max_qty_per_line'), $stock ?? PHP_INT_MAX);
        $item->update(['quantity' => min($qty, $max)]);

        return $qty > $max ? "Only {$max} available." : null;
    }

    public function remove(int $itemId): void
    {
        $this->cart()?->items()->whereKey($itemId)->delete();
    }

    public function clear(): void
    {
        if ($cart = $this->cart()) {
            $cart->delete();
        }
        session()->forget('cart_id');
        $this->cart = null;
    }

    public function applyCoupon(string $code): ?string
    {
        $cart = $this->cart();
        if (! $cart || $cart->items()->doesntExist()) {
            return 'Your cart is empty.';
        }

        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();
        if (! $coupon) {
            return 'Invalid coupon code.';
        }
        if ($error = $coupon->validationError($this->summary()['subtotal'], auth()->id())) {
            return $error;
        }

        $cart->update(['coupon_id' => $coupon->id]);
        $this->cart = null;

        return null;
    }

    public function removeCoupon(): void
    {
        $this->cart()?->update(['coupon_id' => null]);
        $this->cart = null;
    }

    public function count(): int
    {
        return (int) ($this->cart()?->items()->sum('quantity') ?? 0);
    }

    /**
     * Priced view of the cart. Prices always come from the database, never from the client.
     *
     * @return array{lines: Collection, subtotal: float, discount: float, shipping: float, total: float, coupon: ?Coupon, coupon_error: ?string, count: int, remaining_for_free_shipping: float}
     */
    public function summary(): array
    {
        $cart = $this->cart();
        $lines = collect();

        if ($cart) {
            $items = $cart->items()->with(['product', 'variant.attributeValues.attribute'])->get();
            foreach ($items as $item) {
                if (! $item->product || ! $item->product->is_active || ($item->product_variant_id && (! $item->variant || ! $item->variant->is_active))) {
                    $item->delete();

                    continue;
                }
                $unit = $item->variant ? $item->variant->final_price : $item->product->final_price;
                $lines->push((object) [
                    'item' => $item,
                    'product' => $item->product,
                    'variant' => $item->variant,
                    'name' => $item->product->name,
                    'label' => $item->variant?->label,
                    'image' => $item->variant?->image ? asset('storage/'.$item->variant->image) : $item->product->image_url,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'unit_price' => (float) $unit,
                    'quantity' => $item->quantity,
                    'total' => round($unit * $item->quantity, 2),
                ]);
            }
        }

        $subtotal = round($lines->sum('total'), 2);
        $coupon = $cart?->coupon;
        $couponError = null;
        $discount = 0.0;

        if ($coupon) {
            $couponError = $coupon->validationError($subtotal, auth()->id());
            if ($couponError) {
                $cart->update(['coupon_id' => null]);
                $coupon = null;
            } else {
                $discount = $coupon->discountFor($subtotal);
            }
        }

        $net = $subtotal - $discount;
        $shipping = $lines->isEmpty() || $net >= config('shop.free_shipping_threshold') ? 0.0 : (float) config('shop.flat_shipping');

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => round($net + $shipping, 2),
            'coupon' => $coupon,
            'coupon_error' => $couponError,
            'count' => (int) $lines->sum('quantity'),
            'remaining_for_free_shipping' => max(0, config('shop.free_shipping_threshold') - $net),
        ];
    }
}
