<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Account lockout — per username
        $limiter = app(RateLimiter::class);
        $key = 'login:' . $request->username;

        if ($limiter->tooManyAttempts($key, 5)) {
            $seconds = $limiter->availableIn($key);
            return back()->withErrors(['Terlalu banyak percobaan. Coba lagi ' . $seconds . ' detik.']);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $limiter->clear($key);

            $user = Auth::user();
            if ($user->isAdmin() || $user->hasPermission(config('permissions.DASHBOARD'))) {
                return redirect()->intended(route('dashboard'));
            }

            return redirect()->intended(route('stock.sales'));
        }

        $limiter->hit($key, 60);
        return back()->withErrors(['Username atau password salah.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
