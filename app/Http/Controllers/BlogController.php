<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('blog.index', ['posts' => Post::published()->latest('published_at')->paginate(9)]);
    }

    public function show(string $slug): View
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return view('blog.show', [
            'post' => $post,
            'more' => Post::published()->where('id', '!=', $post->id)->latest('published_at')->limit(3)->get(),
        ]);
    }
}
