<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        return view('cart.index', ['cart' => $this->cart->summary()]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.config('shop.max_qty_per_line')],
        ]);

        try {
            $this->cart->add(
                Product::findOrFail($data['product_id']),
                isset($data['variant_id']) ? ProductVariant::find($data['variant_id']) : null,
                $data['quantity'] ?? 1,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return $request->boolean('buy_now')
            ? redirect()->route('checkout')
            : redirect()->route('cart')->with('success', 'Added to your cart.');
    }

    public function update(Request $request, int $item): RedirectResponse
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:0']]);
        $warning = $this->cart->updateQuantity($item, (int) $request->quantity);

        return back()->with($warning ? 'error' : 'success', $warning ?? 'Cart updated.');
    }

    public function remove(int $item): RedirectResponse
    {
        $this->cart->remove($item);

        return back()->with('success', 'Item removed.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:50']]);
        $error = $this->cart->applyCoupon($request->code);

        return back()->with($error ? 'error' : 'success', $error ?? 'Coupon applied.');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->cart->removeCoupon();

        return back()->with('success', 'Coupon removed.');
    }
}
