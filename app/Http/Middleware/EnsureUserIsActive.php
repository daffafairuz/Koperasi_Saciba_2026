<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Memastikan pengguna yang sedang login masih berstatus AKTIF.
     * Jika status berubah (misal menjadi KELUAR), sesi langsung diputus secara paksa.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()?->status !== User::STATUS_AKTIF) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'no_hp' => 'Sesi Anda berakhir karena status keanggotaan sudah tidak aktif.',
                ]);
        }

        return $next($request);
    }
}
