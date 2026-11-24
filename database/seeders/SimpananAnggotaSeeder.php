<?php

namespace Database\Seeders;

use App\Models\PengaturanKoperasi;
use App\Models\SimpananAnggota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimpananAnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $pengaturan = PengaturanKoperasi::query()->firstOrFail();

            $saldoAwalExcel = [
                'K.10.001' => '9175000.00',
                'K.10.101' => '7500000.00',
                'K.10.102' => '6850000.00',
                'K.10.103' => '5400000.00',
                'K.10.104' => '8250000.00',
                'K.10.105' => '6100000.00',
                'K.10.106' => '5000000.00',
            ];

            $semuaAnggota = User::query()->orderBy('nomor_anggota')->get();

            foreach ($semuaAnggota as $anggota) {
                $saldoSwAwal = $saldoAwalExcel[$anggota->nomor_anggota] ?? '5000000.00';
                $tanggalGabung = Carbon::parse($anggota->tanggal_gabung);

                SimpananAnggota::query()->firstOrCreate(
                    ['anggota_id' => $anggota->id],
                    [
                        'nominal_simpanan_pokok' => $pengaturan->nominal_simpanan_pokok,
                        'status_simpanan_pokok' => SimpananAnggota::STATUS_POKOK_LUNAS,
                        'tanggal_lunas_pokok' => $tanggalGabung->copy()->setTime(10, 0, 0),
                        'saldo_sw_awal' => $saldoSwAwal,
                        'total_simpanan_wajib' => $saldoSwAwal,
                        'periode_sw_terakhir' => '202512',
                    ],
                );
            }
        });
    }
}
