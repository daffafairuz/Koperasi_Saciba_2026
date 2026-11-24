<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database sesuai urutan dependensi Foreign Key.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PengaturanKoperasiSeeder::class,
            RiwayatStatusAnggotaSeeder::class,
            SimpananAnggotaSeeder::class,
            TagihanSimpananWajibSeeder::class,
            PinjamanSeeder::class,
            TransaksiKasUsahaSeeder::class,
            PembayaranAnggotaSeeder::class,
        ]);
    }
}
