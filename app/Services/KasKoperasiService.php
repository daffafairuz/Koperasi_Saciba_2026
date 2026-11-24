<?php

namespace App\Services;

use App\Models\PembayaranAnggota;
use App\Models\Pinjaman;
use App\Models\TransaksiKasUsaha;

class KasKoperasiService
{
    /**
     * Menghitung ringkasan arus kas koperasi real-time dalam satuan sen (100% portabel SQLite & PostgreSQL).
     *
     * @return array{
     *     total_pembayaran_anggota_cents: int,
     *     total_kas_usaha_masuk_cents: int,
     *     total_kas_usaha_keluar_cents: int,
     *     total_pencairan_pinjaman_baru_cents: int,
     *     total_kas_koperasi_cents: int,
     *     total_kas_koperasi: string,
     *     total_pemasukan_usaha: string,
     *     total_pengeluaran_usaha: string,
     *     total_kas_masuk: string,
     *     total_kas_keluar: string
     * }
     */
    public function ringkasanKas(): array
    {
        $totalPembayaranAnggotaCents = $this->toCents(
            PembayaranAnggota::query()->sum('total_bayar'),
        );

        $totalKasUsahaMasukCents = $this->toCents(
            TransaksiKasUsaha::query()
                ->where('jenis_arus', TransaksiKasUsaha::ARUS_MASUK)
                ->sum('nominal'),
        );

        $totalKasUsahaKeluarCents = $this->toCents(
            TransaksiKasUsaha::query()
                ->where('jenis_arus', TransaksiKasUsaha::ARUS_KELUAR)
                ->sum('nominal'),
        );

        $totalPencairanPinjamanBaruCents = $this->toCents(
            Pinjaman::query()
                ->where('is_migrasi', false)
                ->sum('pokok_pinjaman'),
        );

        $totalKasKoperasiCents = $totalPembayaranAnggotaCents
            + $totalKasUsahaMasukCents
            - $totalKasUsahaKeluarCents
            - $totalPencairanPinjamanBaruCents;

        return [
            'total_pembayaran_anggota_cents' => $totalPembayaranAnggotaCents,
            'total_kas_usaha_masuk_cents' => $totalKasUsahaMasukCents,
            'total_kas_usaha_keluar_cents' => $totalKasUsahaKeluarCents,
            'total_pencairan_pinjaman_baru_cents' => $totalPencairanPinjamanBaruCents,
            'total_kas_koperasi_cents' => $totalKasKoperasiCents,
            'total_kas_koperasi' => $this->formatCents($totalKasKoperasiCents),
            'total_pemasukan_usaha' => $this->formatCents($totalKasUsahaMasukCents),
            'total_pengeluaran_usaha' => $this->formatCents($totalKasUsahaKeluarCents),
            'total_kas_masuk' => $this->formatCents($totalPembayaranAnggotaCents + $totalKasUsahaMasukCents),
            'total_kas_keluar' => $this->formatCents($totalKasUsahaKeluarCents + $totalPencairanPinjamanBaruCents),
        ];
    }

    public function toCents(int|float|string|null $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    public function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
