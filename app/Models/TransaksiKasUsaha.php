<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiKasUsaha extends Model
{
    use HasFactory, HasUuids;

    public const string ARUS_MASUK = 'MASUK';

    public const string ARUS_KELUAR = 'KELUAR';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaksi_kas_usaha';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nomor_transaksi',
        'tanggal_transaksi',
        'jenis_arus',
        'kategori',
        'keterangan',
        'nominal',
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
            'tanggal_transaksi' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function diinputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
