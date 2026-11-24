<?php

namespace Database\Seeders;

use App\Models\RiwayatStatusAnggota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiwayatStatusAnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()
                ->where('peran', User::PERAN_ADMIN)
                ->firstOrFail();

            $alasanPerAnggota = [
                'K.10.105' => 'Pindah domisili ke luar kota dan mengajukan pengunduran diri dari keanggotaan koperasi.',
                'K.10.106' => 'Pensiun dari instansi dan mengajukan pengunduran diri secara sukarela.',
            ];

            $anggotaKeluar = User::query()
                ->where('status', User::STATUS_KELUAR)
                ->get();

            foreach ($anggotaKeluar as $anggota) {
                $tanggalKeluar = $anggota->tanggal_keluar
                    ? Carbon::parse($anggota->tanggal_keluar)
                    : Carbon::now();

                RiwayatStatusAnggota::query()->firstOrCreate(
                    [
                        'anggota_id' => $anggota->id,
                        'status_sebelumnya' => RiwayatStatusAnggota::STATUS_AKTIF,
                        'status_baru' => RiwayatStatusAnggota::STATUS_KELUAR,
                    ],
                    [
                        'alasan' => $alasanPerAnggota[$anggota->nomor_anggota] ?? 'Pengunduran diri sukarela dari keanggotaan koperasi.',
                        'tanggal_pengajuan' => $tanggalKeluar->copy()->subDays(7)->setTime(9, 0, 0),
                        'disetujui_oleh' => $admin->id,
                        'tanggal_disetujui' => $tanggalKeluar->copy()->setTime(14, 0, 0),
                    ],
                );
            }
        });
    }
}
