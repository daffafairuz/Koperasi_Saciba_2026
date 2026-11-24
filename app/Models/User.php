<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    // 1. Arahkan secara eksplisit ke tabel 'anggota'
    protected $table = 'anggota';

    protected $fillable = [
        'nomor_anggota',
        'nama_lengkap',
        'no_hp',
        'password',
        'peran',
        'status',
        'wajib_simpanan',
        'tanggal_gabung',
        'tanggal_keluar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wajib_simpanan' => 'boolean',
            'tanggal_gabung' => 'date',
            'tanggal_keluar' => 'date',
        ];
    }

    // 2. Helper Behavior Role & Status (Mempermudah Middleware & Blade/API)
    public function isAdmin(): bool
    {
        return $this->peran === 'ADMIN';
    }

    public function isAktif(): bool
    {
        return $this->status === 'AKTIF';
    }

    // 3. Relasi Domain Anggota ke Modul Lain
    public function simpanan()
    {
        return $this->hasOne(SimpananAnggota::class, 'anggota_id');
    }

    public function tagihanSimpananWajib()
    {
        return $this->hasMany(TagihanSimpananWajib::class, 'anggota_id');
    }

    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class, 'anggota_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(PembayaranAnggota::class, 'anggota_id');
    }

    public function riwayatStatus()
    {
        return $this->hasMany(RiwayatStatusAnggota::class, 'anggota_id');
    }
}