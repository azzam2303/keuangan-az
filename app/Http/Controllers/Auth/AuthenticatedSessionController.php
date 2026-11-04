<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $enteredUsername = trim($credentials['username']);
        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [Str::lower($enteredUsername)])
            ->first();

        if (! $user) {
            return back()
                ->withErrors(['username' => 'Akun tidak terdaftar. Silakan buat akun baru terlebih dahulu.'])
                ->onlyInput('username');
        }

        $remember = $request->boolean('remember');

        if (! Auth::attempt(['username' => $user->username, 'password' => $credentials['password']], $remember)) {
            return back()
                ->withErrors(['username' => 'Kata sandi yang dimasukkan salah.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
