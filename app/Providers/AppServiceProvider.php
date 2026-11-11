<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartService::class);
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $view->with([
                'navCategories' => Category::active()->whereNull('parent_id')->orderBy('sort_order')->limit(6)->get(['id', 'name', 'slug']),
                'cartCount' => app(CartService::class)->count(),
            ]);
        });
    }
}
