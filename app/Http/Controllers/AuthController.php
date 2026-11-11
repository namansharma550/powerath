<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);

        // Keep the guest cart across the session-id regeneration performed on login.
        $cartId = session('cart_id');
        $guestOrders = session('guest_orders', []);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->with('error', 'These credentials do not match our records.');
        }

        $request->session()->regenerate();
        session(['cart_id' => $cartId, 'guest_orders' => $guestOrders]);
        $this->cart->attachToUser(Auth::id());

        return redirect()->intended(route('account'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s]{8,15}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $cartId = session('cart_id');
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();
        session(['cart_id' => $cartId]);
        $this->cart->attachToUser($user->id);

        return redirect()->intended(route('account'))->with('success', 'Welcome to Powerath!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
