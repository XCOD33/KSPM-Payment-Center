<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        Cache::remember('user', now()->addDay(), function () {
            return Auth::user();
        });

        return redirect()->intended(route('dashboard'));
    }
}
