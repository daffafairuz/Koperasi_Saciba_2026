<?php

namespace App\Console\Commands;

use App\Models\PengaturanKoperasi;
use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateTagihanSwCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koperasi:generate-tagihan-sw {--periode= : Periode target format YYYYMM (opsional, default bulan berjalan)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menerbitkan tagihan Simpanan Wajib bulanan untuk seluruh anggota aktif yang memenuhi syarat.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $periodeOption = $this->option('periode');
        $periode = ($periodeOption === null || trim((string) $periodeOption) === '')
            ? now('Asia/Jakarta')->format('Ym')
            : trim((string) $periodeOption);

        if (preg_match('/^(\d{4})(0[1-9]|1[0-2])$/', $periode) !== 1) {
            $this->error("Format periode tidak valid: '{$periode}'. Gunakan format YYYYMM (contoh: 202612).");

            return self::INVALID;
        }

        $akhirBulanTarget = Carbon::createFromFormat('Ym', $periode)
            ->endOfMonth()
            ->toDateString();

        $ringkasan = DB::transaction(function () use ($periode, $akhirBulanTarget): array {
            $pengaturan = PengaturanKoperasi::query()
                ->latest('diperbarui_pada')
                ->latest('created_at')
                ->first();

            $nominalSw = (string) ($pengaturan?->nominal_simpanan_wajib ?? '50000.00');

            $anggotaLayak = User::query()
                ->aktif()
                ->where('wajib_simpanan', true)
                ->whereDate('tanggal_gabung', '<=', $akhirBulanTarget)
                ->orderBy('nomor_anggota')
                ->get(['id', 'nomor_anggota', 'nama_lengkap', 'tanggal_gabung']);

            $tagihanBaru = 0;
            $dilewati = 0;

            foreach ($anggotaLayak as $anggota) {
                $tagihan = TagihanSimpananWajib::query()->firstOrCreate(
                    [
                        'anggota_id' => $anggota->id,
                        'periode' => $periode,
                    ],
                    [
                        'nominal_tagihan' => $nominalSw,
                        'nominal_terbayar' => '0.00',
                        'status' => TagihanSimpananWajib::STATUS_BELUM_BAYAR,
                        'tanggal_lunas' => null,
                    ],
                );

                if ($tagihan->wasRecentlyCreated) {
                    $tagihanBaru++;
                } else {
                    $dilewati++;
                }
            }

            return [
                'nominal_sw' => $nominalSw,
                'total_layak' => $anggotaLayak->count(),
                'tagihan_baru' => $tagihanBaru,
                'dilewati' => $dilewati,
            ];
        });

        $labelPeriode = format_periode($periode);

        $this->info("Penerbitan Tagihan Simpanan Wajib periode {$labelPeriode} ({$periode}) selesai.");
        $this->line('Tarif SW aktif            : '.format_rupiah($ringkasan['nominal_sw']));
        $this->line("Anggota aktif memenuhi    : {$ringkasan['total_layak']} orang");
        $this->line("Tagihan baru diterbitkan  : {$ringkasan['tagihan_baru']} tagihan");
        $this->line("Dilewati (sudah ada/lunas): {$ringkasan['dilewati']} anggota");

        return self::SUCCESS;
    }
}
