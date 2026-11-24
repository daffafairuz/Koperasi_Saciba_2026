<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembayaranAnggota extends Model
{
    use HasFactory, HasUuids;

    public const string METODE_TUNAI = 'TUNAI';

    public const string METODE_TRANSFER = 'TRANSFER';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pembayaran_anggota';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nomor_transaksi',
        'anggota_id',
        'tanggal_bayar',
        'total_bayar',
        'metode_bayar',
        'catatan',
        'diinput_oleh',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'datetime',
            'total_bayar' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anggota_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function diinputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }

    /**
     * @return HasMany<DetailPembayaran, $this>
     */
    public function detailPembayaran(): HasMany
    {
        return $this->hasMany(DetailPembayaran::class, 'pembayaran_id');
    }
}
