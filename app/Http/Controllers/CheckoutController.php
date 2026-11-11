<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function show(): View|RedirectResponse
    {
        $summary = $this->cart->summary();
        if ($summary['lines']->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $user = auth()->user();
        $address = $user?->addresses()->orderByDesc('is_default')->first();

        return view('checkout.index', [
            'cart' => $summary,
            'prefill' => [
                'name' => $address?->name ?? $user?->name,
                'email' => $user?->email,
                'phone' => $address?->phone ?? $user?->phone,
                'line1' => $address?->line1,
                'line2' => $address?->line2,
                'city' => $address?->city,
                'state' => $address?->state,
                'postcode' => $address?->postcode,
            ],
        ]);
    }

    public function place(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{8,15}$/'],
            'line1' => ['required', 'string', 'max:190'],
            'line2' => ['nullable', 'string', 'max:190'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postcode' => ['required', 'string', 'max:12'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:'.implode(',', array_keys(Order::PAYMENT_METHODS))],
            'save_address' => ['nullable', 'boolean'],
        ]);

        $summary = $this->cart->summary();
        if ($summary['lines']->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        try {
            $order = DB::transaction(function () use ($data, $summary, $request) {
                // Lock stock rows so concurrent orders can't oversell.
                foreach ($summary['lines'] as $line) {
                    if ($line->variant) {
                        $variant = ProductVariant::lockForUpdate()->find($line->variant->id);
                        if (! $variant || $variant->stock_qty < $line->quantity) {
                            throw new RuntimeException("Not enough stock for {$line->name} ({$line->label}).");
                        }
                        $variant->decrement('stock_qty', $line->quantity);
                    } else {
                        $product = Product::lockForUpdate()->find($line->product->id);
                        if ($product->stock_status !== 'in_stock' || ($product->manage_stock && $product->stock_qty < $line->quantity)) {
                            throw new RuntimeException("Not enough stock for {$line->name}.");
                        }
                        if ($product->manage_stock) {
                            $product->decrement('stock_qty', $line->quantity);
                            $product->refresh()->save(); // refreshes derived stock status
                        }
                    }
                }

                $coupon = $summary['coupon'] ? Coupon::lockForUpdate()->find($summary['coupon']->id) : null;
                if ($coupon && $coupon->validationError($summary['subtotal'], auth()->id())) {
                    throw new RuntimeException('Your coupon is no longer valid. Please review your cart.');
                }

                $address = collect($data)->only(['name', 'phone', 'line1', 'line2', 'city', 'state', 'postcode'])->all() + ['country' => 'India'];

                $order = Order::create([
                    'order_number' => Order::generateNumber(),
                    'user_id' => auth()->id(),
                    'status' => 'pending',
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'pending',
                    'customer_name' => $data['name'],
                    'customer_email' => strtolower($data['email']),
                    'customer_phone' => $data['phone'],
                    'shipping_address' => $address,
                    'billing_address' => $address,
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'subtotal' => $summary['subtotal'],
                    'discount' => $summary['discount'],
                    'shipping' => $summary['shipping'],
                    'tax' => 0,
                    'total' => $summary['total'],
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($summary['lines'] as $line) {
                    $order->items()->create([
                        'product_id' => $line->product->id,
                        'product_variant_id' => $line->variant?->id,
                        'name' => $line->name,
                        'variant_label' => $line->label,
                        'sku' => $line->sku,
                        'price' => $line->unit_price,
                        'quantity' => $line->quantity,
                        'total' => $line->total,
                    ]);
                }

                $coupon?->increment('used_count');

                if (auth()->check() && $request->boolean('save_address')) {
                    auth()->user()->addresses()->updateOrCreate(
                        ['line1' => $address['line1'], 'postcode' => $address['postcode']],
                        $address + ['is_default' => auth()->user()->addresses()->doesntExist()],
                    );
                }

                return $order;
            });
        } catch (RuntimeException $e) {
            return redirect()->route('cart')->with('error', $e->getMessage());
        }

        $this->cart->clear();
        session()->push('guest_orders', $order->id);

        return redirect()->route('orders.show', $order->order_number)->with('success', 'Thank you! Your order has been placed.');
    }
}
