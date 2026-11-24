<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanKoperasi extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pengaturan_koperasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nominal_simpanan_pokok',
        'nominal_simpanan_wajib',
        'persen_margin_barang',
        'diperbarui_oleh',
        'diperbarui_pada',
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
            'nominal_simpanan_wajib' => 'decimal:2',
            'persen_margin_barang' => 'decimal:2',
            'diperbarui_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function diperbaruiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperbarui_oleh');
    }
}
