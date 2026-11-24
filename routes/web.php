<?php

use App\Http\Controllers\Anggota\AnggotaPortalController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Grup rute khusus ADMIN
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:ADMIN')
        ->group(function (): void {
            Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        });

    // Grup rute portal read-only ANGGOTA (ADMIN juga diizinkan melihat data pribadinya)
    Route::prefix('anggota')
        ->name('anggota.')
        ->middleware('role:ANGGOTA,ADMIN')
        ->group(function (): void {
            Route::get('/dashboard', [AnggotaPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/simpanan', [AnggotaPortalController::class, 'simpanan'])->name('simpanan');
            Route::get('/pinjaman', [AnggotaPortalController::class, 'pinjaman'])->name('pinjaman');
            Route::get('/riwayat', [AnggotaPortalController::class, 'riwayat'])->name('riwayat');
        });
});
