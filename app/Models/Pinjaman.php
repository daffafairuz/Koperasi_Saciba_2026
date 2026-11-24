<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    use HasFactory, HasUuids;

    public const string JENIS_UANG = 'UANG';

    public const string JENIS_BARANG = 'BARANG';

    public const string STATUS_BERJALAN = 'BERJALAN';

    public const string STATUS_LUNAS = 'LUNAS';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pinjaman';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nomor_pinjaman',
        'anggota_id',
        'jenis_pinjaman',
        'nama_barang',
        'pokok_pinjaman',
        'persen_margin',
        'total_tagihan',
        'sisa_tagihan',
        'tenor_bulan',
        'cicilan_per_bulan',
        'status',
        'tanggal_cair',
        'is_migrasi',
        'dicairkan_oleh',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pokok_pinjaman' => 'decimal:2',
            'persen_margin' => 'decimal:2',
            'total_tagihan' => 'decimal:2',
            'sisa_tagihan' => 'decimal:2',
            'tenor_bulan' => 'integer',
            'cicilan_per_bulan' => 'decimal:2',
            'tanggal_cair' => 'date',
            'is_migrasi' => 'boolean',
        ];
    }

    /**
     * Scope pinjaman yang masih berjalan.
     *
     * @param  Builder<Pinjaman>  $query
     * @return Builder<Pinjaman>
     */
    #[Scope]
    protected function berjalan(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BERJALAN);
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
    public function dicairkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicairkan_oleh');
    }

    /**
     * @return HasMany<CicilanPinjaman, $this>
     */
    public function cicilan(): HasMany
    {
        return $this->hasMany(CicilanPinjaman::class, 'pinjaman_id');
    }
}
