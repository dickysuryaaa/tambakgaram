<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($data['username']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $detik = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan masuk. Coba lagi dalam $detik detik.",
            ]);
        }

        // Hanya akun berstatus aktif yang boleh masuk.
        $ok = Auth::attempt([
            'username' => $data['username'],
            'password' => $data['password'],
            'status' => 'aktif',
        ], $request->boolean('remember'));

        if (! $ok) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'username' => 'Nama pengguna atau kata sandi salah, atau akun tidak aktif.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda sudah keluar.');
    }
}
