<?php

namespace Database\Seeders;

use App\Models\PengaturanKoperasi;
use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagihanSimpananWajibSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $pengaturan = PengaturanKoperasi::query()->firstOrFail();

            $periodeMulai = Carbon::create(2026, 1, 1);
            $periodeAkhir = Carbon::create(2026, 11, 1);

            $anggotaWajibSw = User::query()
                ->where('wajib_simpanan', true)
                ->orderBy('nomor_anggota')
                ->get();

            foreach ($anggotaWajibSw as $anggota) {
                $periodeGabung = Carbon::parse($anggota->tanggal_gabung)->format('Ym');
                $periodeKeluar = $anggota->status === User::STATUS_KELUAR && $anggota->tanggal_keluar !== null
                    ? Carbon::parse($anggota->tanggal_keluar)->format('Ym')
                    : null;

                $cursor = $periodeMulai->copy();

                while ($cursor->lessThanOrEqualTo($periodeAkhir)) {
                    $periode = $cursor->format('Ym');

                    $memenuhiAwal = $periode >= $periodeGabung;
                    $memenuhiAkhir = $periodeKeluar === null || $periode <= $periodeKeluar;

                    if ($memenuhiAwal && $memenuhiAkhir) {
                        TagihanSimpananWajib::query()->firstOrCreate(
                            [
                                'anggota_id' => $anggota->id,
                                'periode' => $periode,
                            ],
                            [
                                'nominal_tagihan' => $pengaturan->nominal_simpanan_wajib,
                                'nominal_terbayar' => '0.00',
                                'status' => TagihanSimpananWajib::STATUS_BELUM_BAYAR,
                                'tanggal_lunas' => null,
                            ],
                        );
                    }

                    $cursor->addMonth();
                }
            }
        });
    }
}
