<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    public const string PERAN_ADMIN = 'ADMIN';

    public const string PERAN_ANGGOTA = 'ANGGOTA';

    public const string STATUS_AKTIF = 'AKTIF';

    public const string STATUS_KELUAR = 'KELUAR';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'anggota';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wajib_simpanan' => 'boolean',
            'tanggal_gabung' => 'date',
            'tanggal_keluar' => 'date',
        ];
    }

    /**
     * Kolom kredensial utama untuk autentikasi.
     */
    public function username(): string
    {
        return 'no_hp';
    }

    public function isAdmin(): bool
    {
        return $this->peran === self::PERAN_ADMIN;
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    /**
     * Scope anggota berstatus aktif.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    #[Scope]
    protected function aktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    /**
     * @return HasOne<SimpananAnggota, $this>
     */
    public function simpanan(): HasOne
    {
        return $this->hasOne(SimpananAnggota::class, 'anggota_id');
    }

    /**
     * @return HasMany<TagihanSimpananWajib, $this>
     */
    public function tagihanSimpananWajib(): HasMany
    {
        return $this->hasMany(TagihanSimpananWajib::class, 'anggota_id');
    }

    /**
     * @return HasMany<Pinjaman, $this>
     */
    public function pinjaman(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'anggota_id');
    }

    /**
     * @return HasMany<PembayaranAnggota, $this>
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranAnggota::class, 'anggota_id');
    }

    /**
     * @return HasMany<RiwayatStatusAnggota, $this>
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusAnggota::class, 'anggota_id');
    }

    /**
     * @return HasMany<PengaturanKoperasi, $this>
     */
    public function pengaturanDiperbarui(): HasMany
    {
        return $this->hasMany(PengaturanKoperasi::class, 'diperbarui_oleh');
    }

    /**
     * @return HasMany<RiwayatStatusAnggota, $this>
     */
    public function riwayatStatusDisetujui(): HasMany
    {
        return $this->hasMany(RiwayatStatusAnggota::class, 'disetujui_oleh');
    }

    /**
     * @return HasMany<Pinjaman, $this>
     */
    public function pinjamanDicairkan(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'dicairkan_oleh');
    }

    /**
     * @return HasMany<PembayaranAnggota, $this>
     */
    public function pembayaranDiinput(): HasMany
    {
        return $this->hasMany(PembayaranAnggota::class, 'diinput_oleh');
    }

    /**
     * @return HasMany<TransaksiKasUsaha, $this>
     */
    public function transaksiKasDiinput(): HasMany
    {
        return $this->hasMany(TransaksiKasUsaha::class, 'diinput_oleh');
    }
}
