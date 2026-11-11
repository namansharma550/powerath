<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::post('/newsletter', [\App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter')->middleware('throttle:5,1');
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/page/{slug}',[\App\Http\Controllers\PageController::class, 'show'])->name('page');

Route::get('/shop',[ShopController::class, 'index'])->name('shop');
Route::get('/category/{category:slug}', [ShopController::class, 'index'])->name('category');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product');

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart');
    Route::post('/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/items/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/items/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
    Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
});

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place')->middleware('throttle:10,1');

Route::match(['get', 'post'], '/track-order', [OrderController::class, 'track'])->name('orders.track');
Route::get('/orders/{number}', [OrderController::class, 'show'])->name('orders.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::get('/account/orders', [OrderController::class, 'index'])->name('account.orders');
    Route::post('/orders/{number}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});
