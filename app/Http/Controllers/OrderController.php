<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('account.orders', [
            'orders' => auth()->user()->orders()->withCount('items')->latest()->paginate(10),
        ]);
    }

    public function show(string $number): View
    {
        $order = Order::where('order_number', $number)->with('items.product')->firstOrFail();

        $owns = auth()->check() && $order->user_id === auth()->id();
        $placedInSession = in_array($order->id, (array) session('guest_orders', []), true);
        abort_unless($owns || $placedInSession, 404);

        return view('orders.show', compact('order'));
    }

    /** Guest order tracking: order number + email. */
    public function track(Request $request): RedirectResponse|View
    {
        if (! $request->isMethod('post')) {
            return view('orders.track');
        }

        $data = $request->validate(['order_number' => ['required', 'string'], 'email' => ['required', 'email']]);
        $order = Order::where('order_number', strtoupper(trim($data['order_number'])))
            ->where('customer_email', strtolower($data['email']))->first();

        if (! $order) {
            return back()->withInput()->with('error', 'No order found with those details.');
        }

        session()->push('guest_orders', $order->id);

        return redirect()->route('orders.show', $order->order_number);
    }

    public function cancel(string $number): RedirectResponse
    {
        $order = Order::where('order_number', $number)->where('user_id', auth()->id())->firstOrFail();

        if ($order->status !== 'pending') {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        \DB::transaction(function () use ($order) {
            foreach ($order->items()->with(['product', 'variant'])->get() as $item) {
                if ($item->variant) {
                    $item->variant->increment('stock_qty', $item->quantity);
                } elseif ($item->product?->manage_stock) {
                    $item->product->increment('stock_qty', $item->quantity);
                    $item->product->refresh()->save();
                }
            }
            $order->coupon?->decrement('used_count');
            $order->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Order cancelled.');
    }
}
