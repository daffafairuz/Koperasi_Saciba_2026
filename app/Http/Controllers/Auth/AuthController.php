<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login atau mengarahkan user yang sudah login sesuai perannya.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            return $this->redirectBasedOnRole($user);
        }

        return view('auth.login');
    }

    /**
     * Memproses login anggota menggunakan nomor HP (dinormalisasi) dan kata sandi.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_hp' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $normalizedHp = $this->normalizePhoneNumber($validated['no_hp']);

        $isExitedMember = User::query()
            ->where('no_hp', $normalizedHp)
            ->where('status', User::STATUS_KELUAR)
            ->exists();

        if ($isExitedMember) {
            return back()
                ->withErrors([
                    'no_hp' => 'Akun keanggotaan Anda sudah tidak aktif (KELUAR). Hubungi pengurus koperasi untuk informasi lebih lanjut.',
                ])
                ->onlyInput('no_hp');
        }

        $credentials = [
            'no_hp' => $normalizedHp,
            'password' => $validated['password'],
            'status' => User::STATUS_AKTIF,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            return $this->redirectBasedOnRole($user);
        }

        return back()
            ->withErrors([
                'no_hp' => 'Nomor HP atau kata sandi salah.',
            ])
            ->onlyInput('no_hp');
    }

    /**
     * Mengakhiri sesi login pengguna dan mengarahkan kembali ke halaman login.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Menormalisasi input nomor HP ke format standar awalan "08".
     * Contoh: "+62 812-345" atau "62812345" atau "812345" -> "0812345".
     */
    private function normalizePhoneNumber(string $noHp): string
    {
        $digits = preg_replace('/\D+/', '', $noHp) ?? '';

        if (str_starts_with($digits, '620')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0'.$digits;
        }

        return $digits;
    }

    /**
     * Mengarahkan pengguna ke dashboard sesuai peran (ADMIN atau ANGGOTA).
     */
    private function redirectBasedOnRole(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('anggota.dashboard');
    }
}
