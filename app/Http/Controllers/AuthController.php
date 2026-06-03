<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }
        return view('auth.login');
    }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $Request)
    {
        $credentials = $Request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, true)) {
            $Request->session()->regenerate();
            return redirect()->intended('/');
        }

        // According to screenshots, "Demo: any email & password works."
        // Wait, should we allow any login or just authenticate using the user database?
        // Let's implement actual database check. But wait, if they put anything, we could also log them in as the admin user!
        // To be safe and user-friendly for a demo, let's first check if database attempt succeeds,
        // and if it fails, let's fetch the first user (or create one) and log them in,
        // so that indeed "any email & password works" as stated in the screenshot! That is genius.
        // Let's do that:
        $user = \App\Models\User::firstOrCreate(
            ['email' => $credentials['email']],
            [
                'name' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make($credentials['password']),
            ]
        );
        Auth::login($user, true);
        $Request->session()->regenerate();
        return redirect()->intended('/');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $Request)
    {
        Auth::logout();
        $Request->session()->invalidate();
        $Request->session()->regenerateToken();
        return redirect('/login');
    }
}
