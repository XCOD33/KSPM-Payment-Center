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
            'member_id' => ['required', 'min: 9', 'max: 9'],
            'password' => ['required', 'min: 8', 'max: 64']
        ]);

        if (!Auth::attempt(['member_id' => $credentials['member_id'], 'password' => $credentials['password']], $request->get('remember'))) {
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
