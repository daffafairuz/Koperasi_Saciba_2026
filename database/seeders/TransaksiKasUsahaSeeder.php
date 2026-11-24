<?php

namespace Database\Seeders;

use App\Models\TransaksiKasUsaha;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiKasUsahaSeeder extends Seeder
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

            $daftarTransaksi = [
                [
                    'nomor_transaksi' => 'KAS-2026-001',
                    'tanggal_transaksi' => Carbon::create(2026, 1, 1)->toDateString(),
                    'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
                    'kategori' => 'SALDO_AWAL',
                    'keterangan' => 'Saldo awal kas koperasi pindahan tutup buku Excel tahun 2025.',
                    'nominal' => '250000000.00',
                ],
                [
                    'nomor_transaksi' => 'KAS-2026-002',
                    'tanggal_transaksi' => Carbon::create(2026, 2, 28)->toDateString(),
                    'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
                    'kategori' => 'USAHA_WARUNG',
                    'keterangan' => 'Setoran hasil bersih unit usaha warung koperasi bulan Februari 2026.',
                    'nominal' => '12500000.00',
                ],
                [
                    'nomor_transaksi' => 'KAS-2026-003',
                    'tanggal_transaksi' => Carbon::create(2026, 3, 5)->toDateString(),
                    'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
                    'kategori' => 'SEWA_KANTIN',
                    'keterangan' => 'Pendapatan sewa lapak kantin koperasi triwulan I 2026.',
                    'nominal' => '4500000.00',
                ],
                [
                    'nomor_transaksi' => 'KAS-2026-004',
                    'tanggal_transaksi' => Carbon::create(2026, 3, 20)->toDateString(),
                    'jenis_arus' => TransaksiKasUsaha::ARUS_KELUAR,
                    'kategori' => 'THR',
                    'keterangan' => 'Pembagian THR pengurus dan bingkisan lebaran anggota tahun 2026.',
                    'nominal' => '18000000.00',
                ],
                [
                    'nomor_transaksi' => 'KAS-2026-005',
                    'tanggal_transaksi' => Carbon::create(2026, 4, 30)->toDateString(),
                    'jenis_arus' => TransaksiKasUsaha::ARUS_KELUAR,
                    'kategori' => 'BIAYA_OPERASIONAL',
                    'keterangan' => 'Biaya ATK, listrik, internet, dan operasional sekretariat koperasi.',
                    'nominal' => '2350000.00',
                ],
                [
                    'nomor_transaksi' => 'KAS-2026-006',
                    'tanggal_transaksi' => Carbon::create(2026, 6, 30)->toDateString(),
                    'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
                    'kategori' => 'USAHA_WARUNG',
                    'keterangan' => 'Setoran hasil bersih unit usaha warung koperasi kuartal II 2026.',
                    'nominal' => '14200000.00',
                ],
                [
                    'nomor_transaksi' => 'KAS-2026-007',
                    'tanggal_transaksi' => Carbon::create(2026, 9, 15)->toDateString(),
                    'jenis_arus' => TransaksiKasUsaha::ARUS_KELUAR,
                    'kategori' => 'BIAYA_OPERASIONAL',
                    'keterangan' => 'Biaya rapat evaluasi triwulan dan pemeliharaan inventaris koperasi.',
                    'nominal' => '1850000.00',
                ],
            ];

            foreach ($daftarTransaksi as $transaksi) {
                TransaksiKasUsaha::query()->firstOrCreate(
                    ['nomor_transaksi' => $transaksi['nomor_transaksi']],
                    [
                        ...$transaksi,
                        'diinput_oleh' => $admin->id,
                    ],
                );
            }
        });
    }
}
