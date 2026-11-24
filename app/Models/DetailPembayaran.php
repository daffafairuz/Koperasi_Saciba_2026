<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembayaran extends Model
{
    use HasFactory, HasUuids;

    public const string JENIS_SIMPANAN_POKOK = 'SIMPANAN_POKOK';

    public const string JENIS_SIMPANAN_WAJIB = 'SIMPANAN_WAJIB';

    public const string JENIS_CICILAN_PINJAMAN = 'CICILAN_PINJAMAN';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'detail_pembayaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pembayaran_id',
        'jenis_pembayaran',
        'simpanan_anggota_id',
        'tagihan_sw_id',
        'cicilan_pinjaman_id',
        'nominal_bayar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_bayar' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PembayaranAnggota, $this>
     */
    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(PembayaranAnggota::class, 'pembayaran_id');
    }

    /**
     * @return BelongsTo<SimpananAnggota, $this>
     */
    public function simpananAnggota(): BelongsTo
    {
        return $this->belongsTo(SimpananAnggota::class, 'simpanan_anggota_id');
    }

    /**
     * @return BelongsTo<TagihanSimpananWajib, $this>
     */
    public function tagihanSimpananWajib(): BelongsTo
    {
        return $this->belongsTo(TagihanSimpananWajib::class, 'tagihan_sw_id');
    }

    /**
     * @return BelongsTo<CicilanPinjaman, $this>
     */
    public function cicilanPinjaman(): BelongsTo
    {
        return $this->belongsTo(CicilanPinjaman::class, 'cicilan_pinjaman_id');
    }
}
