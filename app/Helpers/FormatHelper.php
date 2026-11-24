<?php

if (! function_exists('format_rupiah')) {
    /**
     * Memformat angka menjadi format Rupiah Indonesia tanpa koma desimal.
     * Contoh: 150000.00 -> "Rp 150.000", 0 -> "Rp 0".
     */
    function format_rupiah(int|float|string|null $nominal): string
    {
        if ($nominal === null || $nominal === '' || ! is_numeric($nominal)) {
            return 'Rp 0';
        }

        $nilai = round((float) $nominal);

        if ($nilai === 0.0) {
            return 'Rp 0';
        }

        $prefix = $nilai < 0 ? '-Rp ' : 'Rp ';

        return $prefix.number_format(abs($nilai), 0, ',', '.');
    }
}

if (! function_exists('format_periode')) {
    /**
     * Mengubah string periode YYYYMM (contoh: "202611") menjadi "November 2026".
     * Mengembalikan "-" jika null atau tidak valid.
     */
    function format_periode(?string $periode): string
    {
        if ($periode === null) {
            return '-';
        }

        $periode = trim($periode);

        if (preg_match('/^(\d{4})(\d{2})$/', $periode, $matches) !== 1) {
            return '-';
        }

        $tahun = $matches[1];
        $bulan = (int) $matches[2];

        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        if (! isset($namaBulan[$bulan])) {
            return '-';
        }

        return "{$namaBulan[$bulan]} {$tahun}";
    }
}
