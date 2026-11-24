<?php

namespace App\Services;

use App\Models\CicilanPinjaman;
use App\Models\PengaturanKoperasi;
use App\Models\Pinjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PinjamanService
{
    public function __construct(
        private readonly KasKoperasiService $kasKoperasiService,
    ) {}

    /**
     * Mencairkan pinjaman baru dan meng-generate seluruh jadwal cicilan secara atomik.
     *
     * @param  array{
     *     anggota_id: string,
     *     jenis_pinjaman: string,
     *     nama_barang?: string|null,
     *     pokok_pinjaman: int|float|string,
     *     tenor_bulan: int|string,
     *     tanggal_cair: string,
     *     keterangan?: string|null
     * }  $data
     */
    public function cairkanPinjaman(array $data, User $admin): Pinjaman
    {
        return DB::transaction(function () use ($data, $admin): Pinjaman {
            $anggota = User::query()->findOrFail($data['anggota_id']);

            if (! $anggota->isAktif()) {
                throw ValidationException::withMessages([
                    'anggota_id' => 'Hanya anggota berstatus AKTIF yang dapat menerima pencairan pinjaman.',
                ]);
            }

            $pokokCents = $this->kasKoperasiService->toCents($data['pokok_pinjaman']);
            $ringkasanKas = $this->kasKoperasiService->ringkasanKas();

            if ($pokokCents > $ringkasanKas['total_kas_koperasi_cents']) {
                throw ValidationException::withMessages([
                    'pokok_pinjaman' => 'Saldo kas koperasi tidak mencukupi untuk pencairan ini. Saldo tersedia saat ini: '.format_rupiah($ringkasanKas['total_kas_koperasi']).'.',
                ]);
            }

            $jenisPinjaman = $data['jenis_pinjaman'];
            $persenMargin = 0.0;

            if ($jenisPinjaman === Pinjaman::JENIS_BARANG) {
                $pengaturan = PengaturanKoperasi::query()
                    ->latest('diperbarui_pada')
                    ->latest('created_at')
                    ->first();

                $persenMargin = (float) ($pengaturan?->persen_margin_barang ?? 15.00);
            }

            $marginCents = (int) round($pokokCents * ($persenMargin / 100));
            $totalTagihanCents = $pokokCents + $marginCents;

            $tenor = (int) $data['tenor_bulan'];
            $cicilanDasarCents = intdiv($totalTagihanCents, $tenor);
            $cicilanTerakhirCents = $totalTagihanCents - ($cicilanDasarCents * ($tenor - 1));

            $tanggalCair = Carbon::parse($data['tanggal_cair']);
            $nomorPinjaman = $this->generateNomorPinjaman($tanggalCair);

            $pinjaman = Pinjaman::query()->create([
                'nomor_pinjaman' => $nomorPinjaman,
                'anggota_id' => $anggota->id,
                'jenis_pinjaman' => $jenisPinjaman,
                'nama_barang' => $jenisPinjaman === Pinjaman::JENIS_BARANG
                    ? trim((string) ($data['nama_barang'] ?? ''))
                    : null,
                'pokok_pinjaman' => $this->kasKoperasiService->formatCents($pokokCents),
                'persen_margin' => number_format($persenMargin, 2, '.', ''),
                'total_tagihan' => $this->kasKoperasiService->formatCents($totalTagihanCents),
                'sisa_tagihan' => $this->kasKoperasiService->formatCents($totalTagihanCents),
                'tenor_bulan' => $tenor,
                'cicilan_per_bulan' => $this->kasKoperasiService->formatCents($cicilanDasarCents),
                'status' => Pinjaman::STATUS_BERJALAN,
                'tanggal_cair' => $tanggalCair->toDateString(),
                'is_migrasi' => false,
                'dicairkan_oleh' => $admin->id,
            ]);

            for ($cicilanKe = 1; $cicilanKe <= $tenor; $cicilanKe++) {
                $nominalCicilanCents = $cicilanKe === $tenor
                    ? $cicilanTerakhirCents
                    : $cicilanDasarCents;

                CicilanPinjaman::query()->create([
                    'pinjaman_id' => $pinjaman->id,
                    'cicilan_ke' => $cicilanKe,
                    'jatuh_tempo' => $tanggalCair->copy()->addMonthsNoOverflow($cicilanKe)->toDateString(),
                    'nominal_tagihan' => $this->kasKoperasiService->formatCents($nominalCicilanCents),
                    'nominal_terbayar' => '0.00',
                    'status' => CicilanPinjaman::STATUS_BELUM_BAYAR,
                    'tanggal_bayar' => null,
                ]);
            }

            return $pinjaman;
        });
    }

    /**
     * Menghasilkan nomor kontrak pinjaman unik format PJM-YYYYMMDD-XXXX.
     */
    private function generateNomorPinjaman(Carbon $tanggalCair): string
    {
        $prefix = 'PJM-'.$tanggalCair->format('Ymd').'-';

        $nomorTerakhir = Pinjaman::query()
            ->where('nomor_pinjaman', 'like', $prefix.'%')
            ->orderByDesc('nomor_pinjaman')
            ->value('nomor_pinjaman');

        $urutanBerikutnya = 1;

        if (is_string($nomorTerakhir)) {
            $suffix = (int) substr($nomorTerakhir, strlen($prefix));
            $urutanBerikutnya = $suffix + 1;
        }

        return $prefix.str_pad((string) $urutanBerikutnya, 4, '0', STR_PAD_LEFT);
    }
}
