<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pembatasan akses berdasarkan role. Pemakaian: ->middleware('role:petambak').
 * Akun yang sudah dinonaktifkan dikeluarkan pada request berikutnya.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $member = $request->user();

        if ($member && $member->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Akun Anda tidak aktif. Hubungi koordinator.']);
        }

        abort_unless($member && in_array($member->role, $roles, true), 403);

        return $next($request);
    }
}
