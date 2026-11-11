<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        return view('account.index', [
            'user' => $user,
            'recentOrders' => $user->orders()->withCount('items')->latest()->limit(3)->get(),
            'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s]{8,15}$/'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (blank($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }
}
