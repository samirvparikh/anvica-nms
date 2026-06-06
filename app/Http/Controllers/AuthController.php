<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has expired. Please contact administrator.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
