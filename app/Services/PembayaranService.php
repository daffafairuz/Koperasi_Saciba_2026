<?php

namespace App\Services;

use App\Models\CicilanPinjaman;
use App\Models\DetailPembayaran;
use App\Models\PembayaranAnggota;
use App\Models\PengaturanKoperasi;
use App\Models\Pinjaman;
use App\Models\SimpananAnggota;
use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranService
{
    public function __construct(
        private readonly KasKoperasiService $kasKoperasiService,
    ) {}

    /**
     * Mengambil seluruh daftar kewajiban anggota (SP, tunggakan SW + info advance, cicilan berjalan).
     *
     * @return array<string, mixed>
     */
    public function getKewajibanAnggota(User $anggota): array
    {
        $simpanan = SimpananAnggota::query()
            ->where('anggota_id', $anggota->id)
            ->first();

        $pengaturan = PengaturanKoperasi::query()
            ->latest('diperbarui_pada')
            ->latest('created_at')
            ->first();

        $nominalSwAktif = (string) ($pengaturan?->nominal_simpanan_wajib ?? '50000.00');

        // 1. Simpanan Pokok
        $spBelumLunas = $simpanan !== null
            && $simpanan->status_simpanan_pokok !== SimpananAnggota::STATUS_POKOK_LUNAS
            && $this->kasKoperasiService->toCents((string) $simpanan->nominal_simpanan_pokok) > 0;

        $simpananPokok = [
            'simpanan_anggota_id' => $simpanan?->id,
            'nominal' => (float) ($simpanan?->nominal_simpanan_pokok ?? 0),
            'status' => $simpanan?->status_simpanan_pokok ?? SimpananAnggota::STATUS_POKOK_BELUM_BAYAR,
            'sisa' => $spBelumLunas ? (float) $simpanan->nominal_simpanan_pokok : 0.0,
            'bisa_dibayar' => $spBelumLunas,
        ];

        // 2. Tagihan Simpanan Wajib (BELUM_BAYAR / SEBAGIAN)
        $tagihanSw = TagihanSimpananWajib::query()
            ->where('anggota_id', $anggota->id)
            ->belumLunas()
            ->orderBy('periode')
            ->get()
            ->map(fn (TagihanSimpananWajib $tagihan): array => [
                'id' => $tagihan->id,
                'periode' => $tagihan->periode,
                'label_periode' => format_periode($tagihan->periode),
                'nominal_tagihan' => (float) $tagihan->nominal_tagihan,
                'nominal_terbayar' => (float) $tagihan->nominal_terbayar,
                'sisa_tagihan' => (float) $tagihan->sisa_tagihan,
                'status' => $tagihan->status,
            ])
            ->values()
            ->all();

        // Hitung periode YYYYMM berikutnya untuk bayar SW di muka (advance payment)
        $maxPeriodeTagihan = TagihanSimpananWajib::query()
            ->where('anggota_id', $anggota->id)
            ->max('periode');

        $periodeReferensi = $maxPeriodeTagihan;
        if ($simpanan?->periode_sw_terakhir !== null && ($periodeReferensi === null || $simpanan->periode_sw_terakhir > $periodeReferensi)) {
            $periodeReferensi = $simpanan->periode_sw_terakhir;
        }

        $periodeAdvanceBerikutnya = is_string($periodeReferensi) && preg_match('/^\d{6}$/', $periodeReferensi) === 1
            ? Carbon::createFromFormat('Ym', $periodeReferensi)->startOfMonth()->addMonth()->format('Ym')
            : now()->startOfMonth()->format('Ym');

        // 3. Cicilan Pinjaman dari pinjaman berstatus BERJALAN
        $cicilanPinjaman = CicilanPinjaman::query()
            ->with('pinjaman:id,nomor_pinjaman,jenis_pinjaman,nama_barang,tenor_bulan,status,anggota_id')
            ->whereHas('pinjaman', function ($query) use ($anggota): void {
                $query
                    ->where('anggota_id', $anggota->id)
                    ->where('status', Pinjaman::STATUS_BERJALAN);
            })
            ->belumLunas()
            ->orderBy('jatuh_tempo')
            ->orderBy('cicilan_ke')
            ->get()
            ->map(fn (CicilanPinjaman $cicilan): array => [
                'id' => $cicilan->id,
                'pinjaman_id' => $cicilan->pinjaman_id,
                'nomor_pinjaman' => $cicilan->pinjaman->nomor_pinjaman,
                'jenis_pinjaman' => $cicilan->pinjaman->jenis_pinjaman,
                'nama_barang' => $cicilan->pinjaman->nama_barang,
                'tenor_bulan' => $cicilan->pinjaman->tenor_bulan,
                'cicilan_ke' => $cicilan->cicilan_ke,
                'jatuh_tempo' => $cicilan->jatuh_tempo->translatedFormat('d M Y'),
                'nominal_tagihan' => (float) $cicilan->nominal_tagihan,
                'nominal_terbayar' => (float) $cicilan->nominal_terbayar,
                'sisa_tagihan' => (float) $cicilan->sisa_tagihan,
                'status' => $cicilan->status,
            ])
            ->values()
            ->all();

        return [
            'anggota' => [
                'id' => $anggota->id,
                'nomor_anggota' => $anggota->nomor_anggota,
                'nama_lengkap' => $anggota->nama_lengkap,
                'status' => $anggota->status,
                'wajib_simpanan' => (bool) $anggota->wajib_simpanan,
            ],
            'simpanan_pokok' => $simpananPokok,
            'tagihan_sw' => $tagihanSw,
            'advance_sw' => [
                'bisa_advance' => (bool) $anggota->wajib_simpanan,
                'periode_berikutnya' => $periodeAdvanceBerikutnya,
                'label_periode_berikutnya' => format_periode($periodeAdvanceBerikutnya),
                'nominal_sw_aktif' => (float) $nominalSwAktif,
            ],
            'cicilan_pinjaman' => $cicilanPinjaman,
        ];
    }

    /**
     * Memproses transaksi pembayaran gabungan (Bulk Payment) secara atomik dalam DB::transaction().
     *
     * @param  array{
     *     anggota_id: string,
     *     tanggal_bayar: string,
     *     metode_bayar: string,
     *     catatan?: string|null,
     *     items: list<array{
     *         jenis_pembayaran: string,
     *         simpanan_anggota_id?: string|null,
     *         tagihan_sw_id?: string|null,
     *         periode_advance?: string|null,
     *         cicilan_pinjaman_id?: string|null,
     *         nominal_bayar: int|float|string
     *     }>
     * }  $data
     */
    public function prosesPembayaran(array $data, User $admin): PembayaranAnggota
    {
        return DB::transaction(function () use ($data, $admin): PembayaranAnggota {
            $anggota = User::query()->findOrFail($data['anggota_id']);

            $simpanan = SimpananAnggota::query()
                ->where('anggota_id', $anggota->id)
                ->firstOrFail();

            $pengaturan = PengaturanKoperasi::query()
                ->latest('diperbarui_pada')
                ->latest('created_at')
                ->first();

            $nominalSwAktif = (string) ($pengaturan?->nominal_simpanan_wajib ?? '50000.00');

            $tanggalInput = trim((string) $data['tanggal_bayar']);
            $tanggalBayar = strlen($tanggalInput) === 10
                ? Carbon::parse($tanggalInput)->setTimeFromTimeString(now()->format('H:i:s'))
                : Carbon::parse($tanggalInput);

            $nomorTransaksi = $this->generateNomorTransaksi($tanggalBayar);

            $pembayaran = PembayaranAnggota::query()->create([
                'nomor_transaksi' => $nomorTransaksi,
                'anggota_id' => $anggota->id,
                'tanggal_bayar' => $tanggalBayar,
                'total_bayar' => '0.00',
                'metode_bayar' => $data['metode_bayar'],
                'catatan' => isset($data['catatan']) && trim((string) $data['catatan']) !== ''
                    ? trim((string) $data['catatan'])
                    : null,
                'diinput_oleh' => $admin->id,
            ]);

            $totalBayarCents = 0;
            $tambahanSwCents = 0;
            $periodeSwLunasTerbaru = null;
            $pinjamanTerdampak = [];

            foreach ($data['items'] as $index => $item) {
                $jenis = $item['jenis_pembayaran'];
                $nominalBayarCents = $this->kasKoperasiService->toCents($item['nominal_bayar']);

                if ($nominalBayarCents <= 0) {
                    throw ValidationException::withMessages([
                        "items.{$index}.nominal_bayar" => 'Nominal bayar setiap item harus lebih dari Rp 0.',
                    ]);
                }

                if ($jenis === DetailPembayaran::JENIS_SIMPANAN_POKOK) {
                    if ($simpanan->status_simpanan_pokok === SimpananAnggota::STATUS_POKOK_LUNAS) {
                        throw ValidationException::withMessages([
                            "items.{$index}.nominal_bayar" => 'Simpanan Pokok anggota ini sudah berstatus LUNAS.',
                        ]);
                    }

                    $nominalSpCents = $this->kasKoperasiService->toCents((string) $simpanan->nominal_simpanan_pokok);
                    if ($nominalBayarCents !== $nominalSpCents) {
                        throw ValidationException::withMessages([
                            "items.{$index}.nominal_bayar" => 'Simpanan Pokok harus dibayar penuh sebesar '.format_rupiah($simpanan->nominal_simpanan_pokok).'.',
                        ]);
                    }

                    DetailPembayaran::query()->create([
                        'pembayaran_id' => $pembayaran->id,
                        'jenis_pembayaran' => DetailPembayaran::JENIS_SIMPANAN_POKOK,
                        'simpanan_anggota_id' => $simpanan->id,
                        'tagihan_sw_id' => null,
                        'cicilan_pinjaman_id' => null,
                        'nominal_bayar' => $this->kasKoperasiService->formatCents($nominalBayarCents),
                    ]);

                    $simpanan->update([
                        'status_simpanan_pokok' => SimpananAnggota::STATUS_POKOK_LUNAS,
                        'tanggal_lunas_pokok' => $tanggalBayar,
                    ]);

                    $totalBayarCents += $nominalBayarCents;

                    continue;
                }

                if ($jenis === DetailPembayaran::JENIS_SIMPANAN_WAJIB) {
                    $tagihan = null;

                    if (! empty($item['tagihan_sw_id'])) {
                        $tagihan = TagihanSimpananWajib::query()
                            ->where('id', $item['tagihan_sw_id'])
                            ->where('anggota_id', $anggota->id)
                            ->first();

                        if ($tagihan === null) {
                            throw ValidationException::withMessages([
                                "items.{$index}.tagihan_sw_id" => 'Tagihan Simpanan Wajib tidak ditemukan untuk anggota ini.',
                            ]);
                        }
                    } elseif (! empty($item['periode_advance'])) {
                        $periodeAdvance = trim((string) $item['periode_advance']);

                        if (preg_match('/^(\d{4})(0[1-9]|1[0-2])$/', $periodeAdvance) !== 1) {
                            throw ValidationException::withMessages([
                                "items.{$index}.periode_advance" => "Format periode bayar di muka ({$periodeAdvance}) tidak valid.",
                            ]);
                        }

                        $tagihan = TagihanSimpananWajib::query()->firstOrCreate(
                            [
                                'anggota_id' => $anggota->id,
                                'periode' => $periodeAdvance,
                            ],
                            [
                                'nominal_tagihan' => $nominalSwAktif,
                                'nominal_terbayar' => '0.00',
                                'status' => TagihanSimpananWajib::STATUS_BELUM_BAYAR,
                                'tanggal_lunas' => null,
                            ],
                        );
                    } else {
                        throw ValidationException::withMessages([
                            "items.{$index}.jenis_pembayaran" => 'Item Simpanan Wajib memerlukan ID tagihan atau periode bayar di muka.',
                        ]);
                    }

                    $tagihanCents = $this->kasKoperasiService->toCents((string) $tagihan->nominal_tagihan);
                    $terbayarLamaCents = $this->kasKoperasiService->toCents((string) $tagihan->nominal_terbayar);
                    $sisaCents = max(0, $tagihanCents - $terbayarLamaCents);

                    if ($sisaCents === 0) {
                        throw ValidationException::withMessages([
                            "items.{$index}.nominal_bayar" => 'Tagihan Simpanan Wajib '.format_periode($tagihan->periode).' sudah lunas.',
                        ]);
                    }

                    if ($nominalBayarCents > $sisaCents) {
                        throw ValidationException::withMessages([
                            "items.{$index}.nominal_bayar" => 'Nominal bayar SW '.format_periode($tagihan->periode).' melebihi sisa tagihan ('.format_rupiah($sisaCents / 100).').',
                        ]);
                    }

                    DetailPembayaran::query()->create([
                        'pembayaran_id' => $pembayaran->id,
                        'jenis_pembayaran' => DetailPembayaran::JENIS_SIMPANAN_WAJIB,
                        'simpanan_anggota_id' => null,
                        'tagihan_sw_id' => $tagihan->id,
                        'cicilan_pinjaman_id' => null,
                        'nominal_bayar' => $this->kasKoperasiService->formatCents($nominalBayarCents),
                    ]);

                    $terbayarBaruCents = $terbayarLamaCents + $nominalBayarCents;
                    $isLunas = $terbayarBaruCents >= $tagihanCents;

                    $tagihan->update([
                        'nominal_terbayar' => $this->kasKoperasiService->formatCents($terbayarBaruCents),
                        'status' => $isLunas ? TagihanSimpananWajib::STATUS_LUNAS : TagihanSimpananWajib::STATUS_SEBAGIAN,
                        'tanggal_lunas' => $isLunas ? $tanggalBayar : null,
                    ]);

                    $tambahanSwCents += $nominalBayarCents;
                    $totalBayarCents += $nominalBayarCents;

                    if ($isLunas && ($periodeSwLunasTerbaru === null || $tagihan->periode > $periodeSwLunasTerbaru)) {
                        $periodeSwLunasTerbaru = $tagihan->periode;
                    }

                    continue;
                }

                if ($jenis === DetailPembayaran::JENIS_CICILAN_PINJAMAN) {
                    $cicilan = CicilanPinjaman::query()
                        ->with('pinjaman')
                        ->where('id', $item['cicilan_pinjaman_id'] ?? '')
                        ->first();

                    if ($cicilan === null || $cicilan->pinjaman->anggota_id !== $anggota->id) {
                        throw ValidationException::withMessages([
                            "items.{$index}.cicilan_pinjaman_id" => 'Cicilan pinjaman tidak ditemukan untuk anggota ini.',
                        ]);
                    }

                    $tagihanCents = $this->kasKoperasiService->toCents((string) $cicilan->nominal_tagihan);
                    $terbayarLamaCents = $this->kasKoperasiService->toCents((string) $cicilan->nominal_terbayar);
                    $sisaCents = max(0, $tagihanCents - $terbayarLamaCents);

                    if ($sisaCents === 0) {
                        throw ValidationException::withMessages([
                            "items.{$index}.nominal_bayar" => "Cicilan ke-{$cicilan->cicilan_ke} ({$cicilan->pinjaman->nomor_pinjaman}) sudah lunas.",
                        ]);
                    }

                    if ($nominalBayarCents > $sisaCents) {
                        throw ValidationException::withMessages([
                            "items.{$index}.nominal_bayar" => "Nominal bayar cicilan ke-{$cicilan->cicilan_ke} melebihi sisa tagihan (".format_rupiah($sisaCents / 100).').',
                        ]);
                    }

                    DetailPembayaran::query()->create([
                        'pembayaran_id' => $pembayaran->id,
                        'jenis_pembayaran' => DetailPembayaran::JENIS_CICILAN_PINJAMAN,
                        'simpanan_anggota_id' => null,
                        'tagihan_sw_id' => null,
                        'cicilan_pinjaman_id' => $cicilan->id,
                        'nominal_bayar' => $this->kasKoperasiService->formatCents($nominalBayarCents),
                    ]);

                    $terbayarBaruCents = $terbayarLamaCents + $nominalBayarCents;
                    $isLunas = $terbayarBaruCents >= $tagihanCents;

                    $cicilan->update([
                        'nominal_terbayar' => $this->kasKoperasiService->formatCents($terbayarBaruCents),
                        'status' => $isLunas ? CicilanPinjaman::STATUS_LUNAS : CicilanPinjaman::STATUS_SEBAGIAN,
                        'tanggal_bayar' => $tanggalBayar,
                    ]);

                    $pinjamanTerdampak[$cicilan->pinjaman_id] = $cicilan->pinjaman;
                    $totalBayarCents += $nominalBayarCents;
                }
            }

            if ($totalBayarCents <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Pilih minimal satu item pembayaran dengan nominal lebih dari Rp 0.',
                ]);
            }

            // Sinkronisasi simpanan_anggota jika ada pembayaran SW
            if ($tambahanSwCents > 0) {
                $totalSwBaruCents = $this->kasKoperasiService->toCents((string) $simpanan->total_simpanan_wajib) + $tambahanSwCents;
                $periodeTerakhir = $simpanan->periode_sw_terakhir;

                if ($periodeSwLunasTerbaru !== null && ($periodeTerakhir === null || $periodeSwLunasTerbaru > $periodeTerakhir)) {
                    $periodeTerakhir = $periodeSwLunasTerbaru;
                }

                $simpanan->update([
                    'total_simpanan_wajib' => $this->kasKoperasiService->formatCents($totalSwBaruCents),
                    'periode_sw_terakhir' => $periodeTerakhir,
                ]);
            }

            // Sinkronisasi sisa_tagihan & status pada setiap pinjaman terdampak
            foreach ($pinjamanTerdampak as $pinjaman) {
                $totalTerbayarCents = CicilanPinjaman::query()
                    ->where('pinjaman_id', $pinjaman->id)
                    ->get()
                    ->sum(fn (CicilanPinjaman $c): int => $this->kasKoperasiService->toCents((string) $c->nominal_terbayar));

                $totalTagihanCents = $this->kasKoperasiService->toCents((string) $pinjaman->total_tagihan);
                $sisaTagihanCents = max(0, $totalTagihanCents - $totalTerbayarCents);

                $pinjaman->update([
                    'sisa_tagihan' => $this->kasKoperasiService->formatCents($sisaTagihanCents),
                    'status' => $sisaTagihanCents <= 0 ? Pinjaman::STATUS_LUNAS : Pinjaman::STATUS_BERJALAN,
                ]);
            }

            // Perbarui header total_bayar
            $pembayaran->update([
                'total_bayar' => $this->kasKoperasiService->formatCents($totalBayarCents),
            ]);

            return $pembayaran;
        });
    }

    /**
     * Menghasilkan nomor transaksi unik format TRX-YYYYMMDD-XXXX.
     */
    private function generateNomorTransaksi(Carbon $tanggalBayar): string
    {
        $prefix = 'TRX-'.$tanggalBayar->format('Ymd').'-';

        $nomorTerakhir = PembayaranAnggota::query()
            ->where('nomor_transaksi', 'like', $prefix.'%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $urutanBerikutnya = 1;

        if (is_string($nomorTerakhir)) {
            $suffix = (int) substr($nomorTerakhir, strlen($prefix));
            $urutanBerikutnya = $suffix + 1;
        }

        return $prefix.str_pad((string) $urutanBerikutnya, 4, '0', STR_PAD_LEFT);
    }
}
