<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = config("pages.pages.{$slug}");
        abort_unless($page, 404);

        return view('pages.show', ['page' => $page, 'slug' => $slug]);
    }
}
