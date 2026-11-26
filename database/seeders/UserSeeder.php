<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Daftar UUID anggota hasil seeding berdasarkan nomor_anggota.
     *
     * @var array<string, string>
     */
    public static array $anggotaIds = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $passwordHash = Hash::make('password');

            $daftarAnggota = [
                [
                    'nomor_anggota' => 'K.10.001',
                    'nama_lengkap' => 'Sartono',
                    'no_hp' => '08999910448',
                    'password' => Hash::make('818181'),
                    'peran' => User::PERAN_ADMIN,
                    'status' => User::STATUS_AKTIF,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => Carbon::create(2020, 1, 10)->toDateString(),
                    'tanggal_keluar' => null,
                ],
                [
                    'nomor_anggota' => 'K.10.101',
                    'nama_lengkap' => 'Siti Aminah',
                    'no_hp' => '081200000101',
                    'password' => $passwordHash,
                    'peran' => User::PERAN_ANGGOTA,
                    'status' => User::STATUS_AKTIF,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => Carbon::create(2021, 3, 15)->toDateString(),
                    'tanggal_keluar' => null,
                ],
                [
                    'nomor_anggota' => 'K.10.102',
                    'nama_lengkap' => 'Ahmad Fauzi',
                    'no_hp' => '081200000102',
                    'password' => $passwordHash,
                    'peran' => User::PERAN_ANGGOTA,
                    'status' => User::STATUS_AKTIF,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => Carbon::create(2021, 6, 1)->toDateString(),
                    'tanggal_keluar' => null,
                ],
                [
                    'nomor_anggota' => 'K.10.103',
                    'nama_lengkap' => 'Dewi Lestari',
                    'no_hp' => '081200000103',
                    'password' => $passwordHash,
                    'peran' => User::PERAN_ANGGOTA,
                    'status' => User::STATUS_AKTIF,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => Carbon::create(2022, 2, 5)->toDateString(),
                    'tanggal_keluar' => null,
                ],
                [
                    'nomor_anggota' => 'K.10.104',
                    'nama_lengkap' => 'Hendra Wijaya',
                    'no_hp' => '081200000104',
                    'password' => $passwordHash,
                    'peran' => User::PERAN_ANGGOTA,
                    'status' => User::STATUS_AKTIF,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => Carbon::create(2020, 8, 20)->toDateString(),
                    'tanggal_keluar' => null,
                ],
                [
                    'nomor_anggota' => 'K.10.105',
                    'nama_lengkap' => 'Rina Kusuma',
                    'no_hp' => '081200000105',
                    'password' => $passwordHash,
                    'peran' => User::PERAN_ANGGOTA,
                    'status' => User::STATUS_KELUAR,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => Carbon::create(2021, 1, 12)->toDateString(),
                    'tanggal_keluar' => Carbon::create(2026, 6, 30)->toDateString(),
                ],
                [
                    'nomor_anggota' => 'K.10.106',
                    'nama_lengkap' => 'Dedi Pratama',
                    'no_hp' => '081200000106',
                    'password' => $passwordHash,
                    'peran' => User::PERAN_ANGGOTA,
                    'status' => User::STATUS_KELUAR,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => Carbon::create(2022, 5, 10)->toDateString(),
                    'tanggal_keluar' => Carbon::create(2026, 8, 15)->toDateString(),
                ],
            ];

            foreach ($daftarAnggota as $data) {
                $user = User::query()->firstOrCreate(
                    ['nomor_anggota' => $data['nomor_anggota']],
                    $data,
                );

                self::$anggotaIds[$user->nomor_anggota] = $user->id;
            }
        });
    }
}
