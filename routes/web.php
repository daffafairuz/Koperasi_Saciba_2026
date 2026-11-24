<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AnggotaController;
use App\Http\Controllers\Admin\PengaturanKoperasiController;
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
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::resource('anggota', AnggotaController::class)
                ->parameters(['anggota' => 'anggota'])
                ->except(['destroy']);
            Route::patch('/anggota/{anggota}/status', [AnggotaController::class, 'ubahStatus'])
                ->name('anggota.status');
            Route::patch('/anggota/{anggota}/reset-password', [AnggotaController::class, 'resetPassword'])
                ->name('anggota.reset-password');

            Route::get('/pengaturan', [PengaturanKoperasiController::class, 'index'])
                ->name('pengaturan.index');
            Route::put('/pengaturan', [PengaturanKoperasiController::class, 'update'])
                ->name('pengaturan.update');
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
