<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $withCard = ['brand:id,name', 'variants'];

        return view('home', [
            'categories' => Category::active()->whereNull('parent_id')->orderBy('sort_order')->orderBy('id')->limit(4)->get(),
            // Best sellers by units ordered; before there are any orders it falls back to featured, then newest.
            'bestsellers' => Product::active()->with($withCard)
                ->withSum('orderItems as sold_qty', 'quantity')
                ->orderByRaw('COALESCE(sold_qty, 0) DESC')->orderByDesc('is_featured')->orderByDesc('id')
                ->limit(8)->get(),
            'posts' => \App\Models\Post::published()->latest('published_at')->limit(3)->get(),
            'featured' =>Product::active()->with($withCard)
                ->orderByDesc('is_featured')->orderByDesc('id')->limit(4)->get(),
        ]);
    }
}
