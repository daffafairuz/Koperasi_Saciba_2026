<?php

namespace Database\Seeders;

use App\Models\PengaturanKoperasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengaturanKoperasiSeeder extends Seeder
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

            if (PengaturanKoperasi::query()->exists()) {
                return;
            }

            PengaturanKoperasi::query()->create([
                'nominal_simpanan_pokok' => '100000.00',
                'nominal_simpanan_wajib' => '50000.00',
                'persen_margin_barang' => '15.00',
                'diperbarui_oleh' => $admin->id,
                'diperbarui_pada' => Carbon::create(2026, 1, 1, 8, 0, 0),
            ]);
        });
    }
}
