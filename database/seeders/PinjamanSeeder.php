<?php

namespace Database\Seeders;

use App\Models\CicilanPinjaman;
use App\Models\Pinjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PinjamanSeeder extends Seeder
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

            $dewi = User::query()->where('nomor_anggota', 'K.10.103')->firstOrFail();
            $hendra = User::query()->where('nomor_anggota', 'K.10.104')->firstOrFail();

            $daftarPinjaman = [
                // 1. Pinjaman Migrasi Uang (margin 0% syariah) - Hendra Wijaya
                [
                    'nomor_pinjaman' => 'PJM-MIG-2026-001',
                    'anggota_id' => $hendra->id,
                    'jenis_pinjaman' => Pinjaman::JENIS_UANG,
                    'nama_barang' => null,
                    'pokok_pinjaman' => '5000000.00',
                    'persen_margin' => '0.00',
                    'tenor_bulan' => 10,
                    'tanggal_cair' => Carbon::create(2025, 12, 15),
                    'is_migrasi' => true,
                    'dicairkan_oleh' => $admin->id,
                ],
                // 2. Pinjaman Migrasi Barang (margin 15%) - Hendra Wijaya
                [
                    'nomor_pinjaman' => 'PJM-MIG-2026-002',
                    'anggota_id' => $hendra->id,
                    'jenis_pinjaman' => Pinjaman::JENIS_BARANG,
                    'nama_barang' => 'Kulkas 2 Pintu Sharp',
                    'pokok_pinjaman' => '3000000.00',
                    'persen_margin' => '15.00',
                    'tenor_bulan' => 6,
                    'tanggal_cair' => Carbon::create(2025, 12, 20),
                    'is_migrasi' => true,
                    'dicairkan_oleh' => $admin->id,
                ],
                // 3. Pinjaman Baru Uang (margin 0%) - Dewi Lestari
                [
                    'nomor_pinjaman' => 'PJM-2026-001',
                    'anggota_id' => $dewi->id,
                    'jenis_pinjaman' => Pinjaman::JENIS_UANG,
                    'nama_barang' => null,
                    'pokok_pinjaman' => '4000000.00',
                    'persen_margin' => '0.00',
                    'tenor_bulan' => 8,
                    'tanggal_cair' => Carbon::create(2026, 2, 1),
                    'is_migrasi' => false,
                    'dicairkan_oleh' => $admin->id,
                ],
                // 4. Pinjaman Baru Barang #1 (margin 15%) - Dewi Lestari (uji unlimited pinjaman barang)
                [
                    'nomor_pinjaman' => 'PJM-2026-002',
                    'anggota_id' => $dewi->id,
                    'jenis_pinjaman' => Pinjaman::JENIS_BARANG,
                    'nama_barang' => 'Laptop Lenovo ThinkPad',
                    'pokok_pinjaman' => '6000000.00',
                    'persen_margin' => '15.00',
                    'tenor_bulan' => 12,
                    'tanggal_cair' => Carbon::create(2026, 3, 1),
                    'is_migrasi' => false,
                    'dicairkan_oleh' => $admin->id,
                ],
                // 5. Pinjaman Baru Barang #2 (margin 15%, tenor ganjil 7 bulan) - Dewi Lestari
                [
                    'nomor_pinjaman' => 'PJM-2026-003',
                    'anggota_id' => $dewi->id,
                    'jenis_pinjaman' => Pinjaman::JENIS_BARANG,
                    'nama_barang' => 'Mesin Cuci Samsung 8Kg',
                    'pokok_pinjaman' => '2500000.00',
                    'persen_margin' => '15.00',
                    'tenor_bulan' => 7,
                    'tanggal_cair' => Carbon::create(2026, 5, 1),
                    'is_migrasi' => false,
                    'dicairkan_oleh' => $admin->id,
                ],
                // 6. Pinjaman Baru Barang #3 (margin 15%) - Dewi Lestari
                [
                    'nomor_pinjaman' => 'PJM-2026-004',
                    'anggota_id' => $dewi->id,
                    'jenis_pinjaman' => Pinjaman::JENIS_BARANG,
                    'nama_barang' => 'Smartphone Redmi Note 14',
                    'pokok_pinjaman' => '2000000.00',
                    'persen_margin' => '15.00',
                    'tenor_bulan' => 5,
                    'tanggal_cair' => Carbon::create(2026, 8, 1),
                    'is_migrasi' => false,
                    'dicairkan_oleh' => $admin->id,
                ],
            ];

            foreach ($daftarPinjaman as $item) {
                $pokokCents = (int) round(((float) $item['pokok_pinjaman']) * 100);
                $marginPersen = (float) $item['persen_margin'];
                $marginCents = (int) round($pokokCents * ($marginPersen / 100));
                $totalTagihanCents = $pokokCents + $marginCents;

                $tenor = (int) $item['tenor_bulan'];
                $cicilanDasarCents = intdiv($totalTagihanCents, $tenor);
                $cicilanTerakhirCents = $totalTagihanCents - ($cicilanDasarCents * ($tenor - 1));

                $pinjaman = Pinjaman::query()->firstOrCreate(
                    ['nomor_pinjaman' => $item['nomor_pinjaman']],
                    [
                        'anggota_id' => $item['anggota_id'],
                        'jenis_pinjaman' => $item['jenis_pinjaman'],
                        'nama_barang' => $item['nama_barang'],
                        'pokok_pinjaman' => $this->formatCents($pokokCents),
                        'persen_margin' => number_format($marginPersen, 2, '.', ''),
                        'total_tagihan' => $this->formatCents($totalTagihanCents),
                        'sisa_tagihan' => $this->formatCents($totalTagihanCents),
                        'tenor_bulan' => $tenor,
                        'cicilan_per_bulan' => $this->formatCents($cicilanDasarCents),
                        'status' => Pinjaman::STATUS_BERJALAN,
                        'tanggal_cair' => $item['tanggal_cair']->toDateString(),
                        'is_migrasi' => $item['is_migrasi'],
                        'dicairkan_oleh' => $item['dicairkan_oleh'],
                    ],
                );

                for ($cicilanKe = 1; $cicilanKe <= $tenor; $cicilanKe++) {
                    $nominalCicilanCents = $cicilanKe === $tenor
                        ? $cicilanTerakhirCents
                        : $cicilanDasarCents;

                    $jatuhTempo = $item['tanggal_cair']
                        ->copy()
                        ->addMonthsNoOverflow($cicilanKe)
                        ->day(10);

                    CicilanPinjaman::query()->firstOrCreate(
                        [
                            'pinjaman_id' => $pinjaman->id,
                            'cicilan_ke' => $cicilanKe,
                        ],
                        [
                            'jatuh_tempo' => $jatuhTempo->toDateString(),
                            'nominal_tagihan' => $this->formatCents($nominalCicilanCents),
                            'nominal_terbayar' => '0.00',
                            'status' => CicilanPinjaman::STATUS_BELUM_BAYAR,
                            'tanggal_bayar' => null,
                        ],
                    );
                }
            }
        });
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
