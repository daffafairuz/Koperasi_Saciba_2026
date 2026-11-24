<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusAnggota extends Model
{
    use HasFactory, HasUuids;

    public const string STATUS_AKTIF = 'AKTIF';

    public const string STATUS_KELUAR = 'KELUAR';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'riwayat_status_anggota';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'anggota_id',
        'status_sebelumnya',
        'status_baru',
        'alasan',
        'tanggal_pengajuan',
        'disetujui_oleh',
        'tanggal_disetujui',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
            'tanggal_disetujui' => 'datetime',
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
    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
