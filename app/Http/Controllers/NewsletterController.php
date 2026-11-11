<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:190']]);

        Subscriber::firstOrCreate(['email' => strtolower($data['email'])]);

        // Same message whether new or existing, so the form can't be used to probe the list.
        return redirect(url()->previous().'#newsletter')->with('newsletter', 'Thanks for subscribing! Watch your inbox for exclusive deals.');
    }
}
