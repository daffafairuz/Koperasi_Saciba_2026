<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanSimpananWajib extends Model
{
    use HasFactory, HasUuids;

    public const string STATUS_BELUM_BAYAR = 'BELUM_BAYAR';

    public const string STATUS_SEBAGIAN = 'SEBAGIAN';

    public const string STATUS_LUNAS = 'LUNAS';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tagihan_simpanan_wajib';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'anggota_id',
        'periode',
        'nominal_tagihan',
        'nominal_terbayar',
        'status',
        'tanggal_lunas',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_tagihan' => 'decimal:2',
            'nominal_terbayar' => 'decimal:2',
            'tanggal_lunas' => 'datetime',
        ];
    }

    /**
     * Sisa nominal tagihan SW yang belum terbayar.
     *
     * @return Attribute<string, never>
     */
    protected function sisaTagihan(): Attribute
    {
        return Attribute::get(function (): string {
            $tagihanCents = (int) round(((float) $this->nominal_tagihan) * 100);
            $terbayarCents = (int) round(((float) $this->nominal_terbayar) * 100);

            return number_format(max(0, $tagihanCents - $terbayarCents) / 100, 2, '.', '');
        });
    }

    /**
     * Scope tagihan yang masih memiliki tunggakan.
     *
     * @param  Builder<TagihanSimpananWajib>  $query
     * @return Builder<TagihanSimpananWajib>
     */
    #[Scope]
    protected function belumLunas(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_BELUM_BAYAR, self::STATUS_SEBAGIAN]);
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
        return $this->hasMany(DetailPembayaran::class, 'tagihan_sw_id');
    }
}
