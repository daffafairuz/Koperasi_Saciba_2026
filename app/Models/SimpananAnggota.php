<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimpananAnggota extends Model
{
    use HasFactory, HasUuids;

    public const string STATUS_POKOK_BELUM_BAYAR = 'BELUM_BAYAR';

    public const string STATUS_POKOK_LUNAS = 'LUNAS';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'simpanan_anggota';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'anggota_id',
        'nominal_simpanan_pokok',
        'status_simpanan_pokok',
        'tanggal_lunas_pokok',
        'saldo_sw_awal',
        'total_simpanan_wajib',
        'periode_sw_terakhir',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'total_simpanan_akhir',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_simpanan_pokok' => 'decimal:2',
            'tanggal_lunas_pokok' => 'datetime',
            'saldo_sw_awal' => 'decimal:2',
            'total_simpanan_wajib' => 'decimal:2',
        ];
    }

    /**
     * Hitung total simpanan akhir secara portabel (total SW + simpanan pokok jika LUNAS).
     *
     * @return Attribute<string, never>
     */
    protected function totalSimpananAkhir(): Attribute
    {
        return Attribute::get(function (): string {
            $totalWajibCents = (int) round(((float) $this->total_simpanan_wajib) * 100);
            $pokokCents = $this->status_simpanan_pokok === self::STATUS_POKOK_LUNAS
                ? (int) round(((float) $this->nominal_simpanan_pokok) * 100)
                : 0;

            return number_format(($totalWajibCents + $pokokCents) / 100, 2, '.', '');
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anggota_id');
    }

    /**
     * @return HasMany<DetailPembayaran, $this>
     */
    public function detailPembayaran(): HasMany
    {
        return $this->hasMany(DetailPembayaran::class, 'simpanan_anggota_id');
    }
}
