<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login_get()
    {
        return view('auth.login');
    }

    public function login_post(Request $request)
    {
        $credentials = $request->validate([
            'nim' => ['required', 'min: 10', 'max: 10'],
            'password' => ['required', 'min: 8', 'max: 64']
        ]);

        if (!Auth::attempt(['nim' => $credentials['nim'], 'password' => $credentials['password']], $request->get('remember'))) {
            return back()->withError('Invalid login credentials!');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
