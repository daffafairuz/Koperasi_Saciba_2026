<?php

namespace App\Console\Commands;

use App\Models\CicilanPinjaman;
use App\Models\PengaturanKoperasi;
use App\Models\Pinjaman;
use App\Models\RiwayatStatusAnggota;
use App\Models\SimpananAnggota;
use App\Models\TagihanSimpananWajib;
use App\Models\TransaksiKasUsaha;
use App\Models\User;
use App\Services\KasKoperasiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportDataExcelKoperasiCommand extends Command
{
    /**
     * =========================================================================
     * KONFIGURASI MAPPING EXCEL (SESUAIKAN DENGAN FILE EXCEL ASLI KOPERASI)
     * =========================================================================
     */

    /** @var list<string> Urutan prioritas nama tab sheet untuk data Anggota & SW Awal */
    public const array SHEET_ANGGOTA_CANDIDATES = ['Anggota_sw', 'Data_Koperasi'];

    /** @var list<string> Urutan prioritas nama tab sheet untuk data Piutang Berjalan */
    public const array SHEET_PIUTANG_CANDIDATES = ['Piutang'];

    /** @var list<string> Urutan prioritas nama tab sheet untuk Saldo Kas Awal */
    public const array SHEET_KAS_CANDIDATES = ['Neraca 2025', 'Dashboard'];

    /** Baris pertama data (di bawah baris header) pada masing-masing sheet */
    public const int ANGGOTA_START_ROW = 2;

    public const int PIUTANG_START_ROW = 2;

    /**
     * Mapping huruf kolom untuk sheet Anggota_sw / Data_Koperasi.
     *
     * @var array<string, string>
     */
    public const array ANGGOTA_COLUMNS = [
        'nomor_anggota' => 'A',
        'nama_lengkap' => 'B',
        'no_hp' => 'C',
        'status' => 'D',                 // AKTIF atau KELUAR (opsional, default AKTIF)
        'tanggal_gabung' => 'E',         // YYYY-MM-DD / format tanggal Excel (opsional)
        'tanggal_keluar' => 'F',         // Kosong jika AKTIF (opsional)
        'nominal_simpanan_pokok' => 'G', // Kosong -> gunakan tarif PengaturanKoperasi
        'status_simpanan_pokok' => 'H',  // LUNAS atau BELUM_BAYAR (opsional, default LUNAS)
        'saldo_sw_awal' => 'I',          // Saldo tutup buku SW terakhir dari Excel
    ];

    /**
     * Mapping huruf kolom untuk sheet Piutang.
     *
     * @var array<string, string>
     */
    public const array PIUTANG_COLUMNS = [
        'nomor_pinjaman' => 'A', // Kosong -> dibuat otomatis format PJM-MIG-...
        'nomor_anggota' => 'B',  // Wajib cocok dengan nomor_anggota di sheet Anggota
        'jenis_pinjaman' => 'C', // UANG atau BARANG
        'nama_barang' => 'D',    // Diisi jika jenis_pinjaman = BARANG
        'pokok_pinjaman' => 'E', // Pokok pinjaman
        'persen_margin' => 'F',  // Kosong -> 0% untuk UANG, default tarif untuk BARANG
        'sisa_tagihan' => 'G',   // Sisa piutang yang dimigrasikan ke sistem web
        'sisa_tenor' => 'H',     // Jumlah sisa bulan cicilan yang belum lunas
        'tanggal_cair' => 'I',   // Tanggal pencairan / patokan jadwal (opsional)
    ];

    /**
     * Koordinat sel default untuk membaca Saldo Kas Awal di sheet Neraca 2025 / Dashboard.
     * Jika sel ini tidak berisi angka > 0, skrip akan mencari label "SALDO KAS" / "KAS" di kolom A -> nilai di kolom B.
     */
    public const string KAS_CELL_SALDO_AWAL = 'B2';

    /** Nomor transaksi tetap (idempoten) untuk baris SALDO_AWAL hasil impor */
    public const string NOMOR_TRANSAKSI_SALDO_AWAL = 'KAS-MIG-SALDO-AWAL';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koperasi:import-excel
        {file : Path menuju file Excel pembukuan koperasi (.xlsx)}
        {--periode-tutup-buku=202512 : Periode SW terakhir tutup buku Excel (YYYYMM)}
        {--tanggal-saldo-awal=2026-01-01 : Tanggal pencatatan SALDO_AWAL & fallback tanggal (YYYY-MM-DD)}
        {--password-awal=password : Kata sandi awal default untuk akun anggota baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengimpor data anggota, saldo SW awal, piutang berjalan (is_migrasi=true), dan saldo kas awal dari Excel koperasi secara atomik & idempoten.';

    public function __construct(
        private readonly KasKoperasiService $kasKoperasiService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fileArgument = (string) $this->argument('file');
        $filePath = $this->resolveFilePath($fileArgument);

        if (! is_file($filePath)) {
            $this->error("File Excel tidak ditemukan pada path: {$fileArgument}");

            return self::FAILURE;
        }

        $periodeTutupBuku = trim((string) $this->option('periode-tutup-buku'));
        if (preg_match('/^(\d{4})(0[1-9]|1[0-2])$/', $periodeTutupBuku) !== 1) {
            $this->error("Opsi --periode-tutup-buku tidak valid: '{$periodeTutupBuku}'. Gunakan format YYYYMM.");

            return self::INVALID;
        }

        $tanggalSaldoAwal = Carbon::parse((string) $this->option('tanggal-saldo-awal'));
        $passwordAwalHash = Hash::make((string) $this->option('password-awal'));

        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            $this->error('Gagal membaca file Excel: '.$e->getMessage());

            return self::FAILURE;
        }

        $sheetAnggota = $this->findSheet($spreadsheet, self::SHEET_ANGGOTA_CANDIDATES);
        $sheetPiutang = $this->findSheet($spreadsheet, self::SHEET_PIUTANG_CANDIDATES);
        $sheetKas = $this->findSheet($spreadsheet, self::SHEET_KAS_CANDIDATES);

        if ($sheetAnggota === null && $sheetPiutang === null && $sheetKas === null) {
            $this->error('Tidak ada tab sheet yang cocok (Anggota_sw / Data_Koperasi / Piutang / Neraca 2025 / Dashboard) pada file Excel.');

            return self::FAILURE;
        }

        $laporan = DB::transaction(function () use (
            $sheetAnggota,
            $sheetPiutang,
            $sheetKas,
            $periodeTutupBuku,
            $tanggalSaldoAwal,
            $passwordAwalHash,
        ): array {
            $admin = $this->ensureAdminExists($tanggalSaldoAwal, $passwordAwalHash);
            $pengaturan = $this->ensurePengaturanExists($admin, $tanggalSaldoAwal);

            $hasilAnggota = $sheetAnggota !== null
                ? $this->imporSheetAnggota($sheetAnggota, $admin, $pengaturan, $periodeTutupBuku, $tanggalSaldoAwal, $passwordAwalHash)
                : ['jumlah' => 0, 'total_sw_awal_cents' => 0, 'placeholders' => []];

            $hasilPiutang = $sheetPiutang !== null
                ? $this->imporSheetPiutang($sheetPiutang, $admin, $pengaturan, $tanggalSaldoAwal)
                : ['jumlah_pinjaman' => 0, 'jumlah_cicilan' => 0, 'total_piutang_cents' => 0, 'dilewati' => []];

            $saldoKasAwalCents = $sheetKas !== null
                ? $this->imporSaldoKasAwal($sheetKas, $admin, $tanggalSaldoAwal)
                : 0;

            return [
                'anggota' => $hasilAnggota,
                'piutang' => $hasilPiutang,
                'saldo_kas_awal_cents' => $saldoKasAwalCents,
            ];
        });

        $this->tampilkanLaporan($laporan);

        return self::SUCCESS;
    }

    /**
     * Impor sheet Anggota_sw / Data_Koperasi ke tabel anggota & simpanan_anggota (1-on-1).
     *
     * @return array{
     *     jumlah: int,
     *     total_sw_awal_cents: int,
     *     placeholders: list<array{nomor_anggota: string, nama_lengkap: string, no_hp_placeholder: string}>
     * }
     */
    private function imporSheetAnggota(
        Worksheet $sheet,
        User $admin,
        PengaturanKoperasi $pengaturan,
        string $periodeTutupBuku,
        Carbon $tanggalDefault,
        string $passwordAwalHash,
    ): array {
        $highestRow = $sheet->getHighestDataRow();
        $jumlahDiimpor = 0;
        $totalSwAwalCents = 0;
        $placeholders = [];
        $placeholderCounter = 1;

        for ($row = self::ANGGOTA_START_ROW; $row <= $highestRow; $row++) {
            $nomorAnggota = $this->readStringCell($sheet, self::ANGGOTA_COLUMNS['nomor_anggota'], $row);
            $namaLengkap = $this->readStringCell($sheet, self::ANGGOTA_COLUMNS['nama_lengkap'], $row);

            if ($nomorAnggota === '' || $namaLengkap === '') {
                continue;
            }

            $rawNoHp = $this->readStringCell($sheet, self::ANGGOTA_COLUMNS['no_hp'], $row);
            $statusRaw = strtoupper($this->readStringCell($sheet, self::ANGGOTA_COLUMNS['status'], $row));
            $status = in_array($statusRaw, [User::STATUS_AKTIF, User::STATUS_KELUAR], true)
                ? $statusRaw
                : User::STATUS_AKTIF;

            $tanggalGabung = $this->parseDateCell($sheet, self::ANGGOTA_COLUMNS['tanggal_gabung'], $row) ?? $tanggalDefault->copy();
            $tanggalKeluar = $status === User::STATUS_KELUAR
                ? ($this->parseDateCell($sheet, self::ANGGOTA_COLUMNS['tanggal_keluar'], $row) ?? $tanggalDefault->copy())
                : null;

            $existingUser = User::query()->where('nomor_anggota', $nomorAnggota)->first();

            $pakaiPlaceholder = false;
            $noHpFinal = $this->normalizePhoneNumber($rawNoHp);

            if ($noHpFinal === '') {
                if ($existingUser !== null) {
                    $noHpFinal = $existingUser->no_hp;
                    $pakaiPlaceholder = str_starts_with($noHpFinal, '08000');
                } else {
                    $noHpFinal = $this->generateUniquePlaceholderHp($nomorAnggota, $placeholderCounter);
                    $pakaiPlaceholder = true;
                }
            } else {
                $hpDipakaiLain = User::query()
                    ->where('no_hp', $noHpFinal)
                    ->when($existingUser !== null, fn ($q) => $q->where('id', '!=', $existingUser->id))
                    ->exists();

                if ($hpDipakaiLain) {
                    $noHpFinal = $this->generateUniquePlaceholderHp($nomorAnggota, $placeholderCounter);
                    $pakaiPlaceholder = true;
                }
            }

            if ($existingUser === null) {
                $anggota = User::query()->create([
                    'nomor_anggota' => $nomorAnggota,
                    'nama_lengkap' => $namaLengkap,
                    'no_hp' => $noHpFinal,
                    'password' => $passwordAwalHash,
                    'peran' => User::PERAN_ANGGOTA,
                    'status' => $status,
                    'wajib_simpanan' => true,
                    'tanggal_gabung' => $tanggalGabung->toDateString(),
                    'tanggal_keluar' => $tanggalKeluar?->toDateString(),
                ]);
            } else {
                $existingUser->update([
                    'nama_lengkap' => $namaLengkap,
                    'no_hp' => $noHpFinal,
                    'status' => $status,
                    'tanggal_gabung' => $tanggalGabung->toDateString(),
                    'tanggal_keluar' => $tanggalKeluar?->toDateString(),
                ]);
                $anggota = $existingUser;
            }

            if ($pakaiPlaceholder) {
                $placeholders[] = [
                    'nomor_anggota' => $anggota->nomor_anggota,
                    'nama_lengkap' => $anggota->nama_lengkap,
                    'no_hp_placeholder' => $anggota->no_hp,
                ];
            }

            if ($status === User::STATUS_KELUAR) {
                RiwayatStatusAnggota::query()->firstOrCreate(
                    [
                        'anggota_id' => $anggota->id,
                        'status_sebelumnya' => RiwayatStatusAnggota::STATUS_AKTIF,
                        'status_baru' => RiwayatStatusAnggota::STATUS_KELUAR,
                    ],
                    [
                        'alasan' => 'Status KELUAR hasil migrasi pembukuan Excel.',
                        'tanggal_pengajuan' => ($tanggalKeluar ?? $tanggalDefault)->copy()->setTime(9, 0, 0),
                        'disetujui_oleh' => $admin->id,
                        'tanggal_disetujui' => ($tanggalKeluar ?? $tanggalDefault)->copy()->setTime(14, 0, 0),
                    ],
                );
            }

            $spCellValue = $this->parseNumericCell($sheet, self::ANGGOTA_COLUMNS['nominal_simpanan_pokok'], $row);
            $nominalSpCents = $spCellValue > 0
                ? $this->kasKoperasiService->toCents($spCellValue)
                : $this->kasKoperasiService->toCents((string) $pengaturan->nominal_simpanan_pokok);

            $statusSpRaw = strtoupper($this->readStringCell($sheet, self::ANGGOTA_COLUMNS['status_simpanan_pokok'], $row));
            $statusSp = in_array($statusSpRaw, [SimpananAnggota::STATUS_POKOK_LUNAS, SimpananAnggota::STATUS_POKOK_BELUM_BAYAR], true)
                ? $statusSpRaw
                : SimpananAnggota::STATUS_POKOK_LUNAS;

            $saldoSwAwalCents = max(0, $this->kasKoperasiService->toCents(
                $this->parseNumericCell($sheet, self::ANGGOTA_COLUMNS['saldo_sw_awal'], $row),
            ));

            // Pertahankan pembayaran SW yang mungkin sudah tercatat di sistem web jika impor dijalankan ulang
            $terbayarSwWebCents = TagihanSimpananWajib::query()
                ->where('anggota_id', $anggota->id)
                ->get()
                ->sum(fn (TagihanSimpananWajib $t): int => $this->kasKoperasiService->toCents((string) $t->nominal_terbayar));

            $simpananLama = SimpananAnggota::query()->where('anggota_id', $anggota->id)->first();
            $periodeTerakhir = $simpananLama?->periode_sw_terakhir;
            if ($periodeTerakhir === null || $periodeTutupBuku > $periodeTerakhir) {
                $periodeTerakhir = $periodeTutupBuku;
            }

            SimpananAnggota::query()->updateOrCreate(
                ['anggota_id' => $anggota->id],
                [
                    'nominal_simpanan_pokok' => $this->kasKoperasiService->formatCents($nominalSpCents),
                    'status_simpanan_pokok' => $statusSp,
                    'tanggal_lunas_pokok' => $statusSp === SimpananAnggota::STATUS_POKOK_LUNAS
                        ? ($simpananLama?->tanggal_lunas_pokok ?? $tanggalGabung->copy()->setTime(10, 0, 0))
                        : null,
                    'saldo_sw_awal' => $this->kasKoperasiService->formatCents($saldoSwAwalCents),
                    'total_simpanan_wajib' => $this->kasKoperasiService->formatCents($saldoSwAwalCents + $terbayarSwWebCents),
                    'periode_sw_terakhir' => $periodeTerakhir,
                ],
            );

            $jumlahDiimpor++;
            $totalSwAwalCents += $saldoSwAwalCents;
        }

        return [
            'jumlah' => $jumlahDiimpor,
            'total_sw_awal_cents' => $totalSwAwalCents,
            'placeholders' => $placeholders,
        ];
    }

    /**
     * Impor sheet Piutang ke tabel pinjaman (is_migrasi = true) & cicilan_pinjaman.
     *
     * @return array{
     *     jumlah_pinjaman: int,
     *     jumlah_cicilan: int,
     *     total_piutang_cents: int,
     *     dilewati: list<string>
     * }
     */
    private function imporSheetPiutang(
        Worksheet $sheet,
        User $admin,
        PengaturanKoperasi $pengaturan,
        Carbon $tanggalDefault,
    ): array {
        $highestRow = $sheet->getHighestDataRow();
        $jumlahPinjaman = 0;
        $jumlahCicilan = 0;
        $totalPiutangCents = 0;
        $dilewati = [];

        for ($row = self::PIUTANG_START_ROW; $row <= $highestRow; $row++) {
            $nomorAnggota = $this->readStringCell($sheet, self::PIUTANG_COLUMNS['nomor_anggota'], $row);
            $sisaTagihanCents = max(0, $this->kasKoperasiService->toCents(
                $this->parseNumericCell($sheet, self::PIUTANG_COLUMNS['sisa_tagihan'], $row),
            ));

            if ($nomorAnggota === '' || $sisaTagihanCents <= 0) {
                continue;
            }

            $anggota = User::query()->where('nomor_anggota', $nomorAnggota)->first();
            if ($anggota === null) {
                $dilewati[] = "Baris {$row}: nomor anggota '{$nomorAnggota}' tidak ditemukan.";

                continue;
            }

            $nomorPinjaman = $this->readStringCell($sheet, self::PIUTANG_COLUMNS['nomor_pinjaman'], $row);
            if ($nomorPinjaman === '') {
                $kodeBersih = preg_replace('/[^A-Za-z0-9]/', '', $nomorAnggota) ?: 'AGT';
                $nomorPinjaman = 'PJM-MIG-'.strtoupper($kodeBersih).'-'.str_pad((string) $row, 3, '0', STR_PAD_LEFT);
            }

            $jenisRaw = strtoupper($this->readStringCell($sheet, self::PIUTANG_COLUMNS['jenis_pinjaman'], $row));
            $jenisPinjaman = $jenisRaw === Pinjaman::JENIS_BARANG ? Pinjaman::JENIS_BARANG : Pinjaman::JENIS_UANG;

            $namaBarang = $jenisPinjaman === Pinjaman::JENIS_BARANG
                ? ($this->readStringCell($sheet, self::PIUTANG_COLUMNS['nama_barang'], $row) ?: 'Pinjaman Barang Migrasi')
                : null;

            $pokokValue = $this->parseNumericCell($sheet, self::PIUTANG_COLUMNS['pokok_pinjaman'], $row);
            $pokokCents = $pokokValue > 0 ? $this->kasKoperasiService->toCents($pokokValue) : $sisaTagihanCents;

            $marginCellRaw = $this->readStringCell($sheet, self::PIUTANG_COLUMNS['persen_margin'], $row);
            if ($marginCellRaw !== '') {
                $persenMargin = max(0.0, $this->parseNumericCell($sheet, self::PIUTANG_COLUMNS['persen_margin'], $row));
            } else {
                $persenMargin = $jenisPinjaman === Pinjaman::JENIS_BARANG
                    ? (float) $pengaturan->persen_margin_barang
                    : 0.0;
            }

            $sisaTenor = max(1, (int) round($this->parseNumericCell($sheet, self::PIUTANG_COLUMNS['sisa_tenor'], $row)));
            $tanggalCair = $this->parseDateCell($sheet, self::PIUTANG_COLUMNS['tanggal_cair'], $row) ?? $tanggalDefault->copy();

            // Sisa piutang yang dimigrasikan menjadi total_tagihan & jadwal cicilan tersisa agar 100% sinkron dengan PembayaranService
            $cicilanDasarCents = intdiv($sisaTagihanCents, $sisaTenor);
            $cicilanTerakhirCents = $sisaTagihanCents - ($cicilanDasarCents * ($sisaTenor - 1));

            $pinjaman = Pinjaman::query()->firstOrCreate(
                ['nomor_pinjaman' => $nomorPinjaman],
                [
                    'anggota_id' => $anggota->id,
                    'jenis_pinjaman' => $jenisPinjaman,
                    'nama_barang' => $namaBarang,
                    'pokok_pinjaman' => $this->kasKoperasiService->formatCents($pokokCents),
                    'persen_margin' => number_format($persenMargin, 2, '.', ''),
                    'total_tagihan' => $this->kasKoperasiService->formatCents($sisaTagihanCents),
                    'sisa_tagihan' => $this->kasKoperasiService->formatCents($sisaTagihanCents),
                    'tenor_bulan' => $sisaTenor,
                    'cicilan_per_bulan' => $this->kasKoperasiService->formatCents($cicilanDasarCents),
                    'status' => Pinjaman::STATUS_BERJALAN,
                    'tanggal_cair' => $tanggalCair->toDateString(),
                    'is_migrasi' => true,
                    'dicairkan_oleh' => $admin->id,
                ],
            );

            for ($cicilanKe = 1; $cicilanKe <= $sisaTenor; $cicilanKe++) {
                $nominalCicilanCents = $cicilanKe === $sisaTenor ? $cicilanTerakhirCents : $cicilanDasarCents;

                $cicilan = CicilanPinjaman::query()->firstOrCreate(
                    [
                        'pinjaman_id' => $pinjaman->id,
                        'cicilan_ke' => $cicilanKe,
                    ],
                    [
                        'jatuh_tempo' => $tanggalDefault->copy()->addMonthsNoOverflow($cicilanKe - 1)->day(10)->toDateString(),
                        'nominal_tagihan' => $this->kasKoperasiService->formatCents($nominalCicilanCents),
                        'nominal_terbayar' => '0.00',
                        'status' => CicilanPinjaman::STATUS_BELUM_BAYAR,
                        'tanggal_bayar' => null,
                    ],
                );

                if ($cicilan->wasRecentlyCreated) {
                    $jumlahCicilan++;
                }
            }

            $jumlahPinjaman++;
            $totalPiutangCents += $sisaTagihanCents;
        }

        return [
            'jumlah_pinjaman' => $jumlahPinjaman,
            'jumlah_cicilan' => $jumlahCicilan,
            'total_piutang_cents' => $totalPiutangCents,
            'dilewati' => $dilewati,
        ];
    }

    /**
     * Membaca saldo kas awal dari sheet Neraca 2025 / Dashboard dan mencatat 1 baris SALDO_AWAL secara idempoten.
     */
    private function imporSaldoKasAwal(
        Worksheet $sheet,
        User $admin,
        Carbon $tanggalSaldoAwal,
    ): int {
        $nilaiKas = $this->parseNumericValue($sheet->getCell(self::KAS_CELL_SALDO_AWAL)->getCalculatedValue());

        // Fallback: cari baris berlabel "SALDO KAS" atau "KAS" di kolom A dan baca nilainya di kolom B
        if ($nilaiKas <= 0) {
            $highestRow = $sheet->getHighestDataRow();
            for ($row = 1; $row <= $highestRow; $row++) {
                $label = strtoupper($this->readStringCell($sheet, 'A', $row));
                if (str_contains($label, 'SALDO KAS') || $label === 'KAS' || str_contains($label, 'KAS KOPERASI')) {
                    $kandidat = $this->parseNumericCell($sheet, 'B', $row);
                    if ($kandidat > 0) {
                        $nilaiKas = $kandidat;

                        break;
                    }
                }
            }
        }

        $saldoKasCents = max(0, $this->kasKoperasiService->toCents($nilaiKas));

        if ($saldoKasCents <= 0) {
            return 0;
        }

        TransaksiKasUsaha::query()->updateOrCreate(
            ['nomor_transaksi' => self::NOMOR_TRANSAKSI_SALDO_AWAL],
            [
                'tanggal_transaksi' => $tanggalSaldoAwal->toDateString(),
                'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
                'kategori' => 'SALDO_AWAL',
                'keterangan' => 'Saldo awal kas koperasi hasil impor pembukuan Excel (sheet '.$sheet->getTitle().').',
                'nominal' => $this->kasKoperasiService->formatCents($saldoKasCents),
                'diinput_oleh' => $admin->id,
            ],
        );

        return $saldoKasCents;
    }

    /**
     * @param  array<string, mixed>  $laporan
     */
    private function tampilkanLaporan(array $laporan): void
    {
        $this->newLine();
        $this->info('=== LAPORAN IMPOR DATA EXCEL KOPERASI JASA BERKAH SACIBA ===');
        $this->line('Anggota diproses         : '.$laporan['anggota']['jumlah'].' orang');
        $this->line('Total Saldo SW Awal      : '.format_rupiah($laporan['anggota']['total_sw_awal_cents'] / 100));
        $this->line('Pinjaman Migrasi Diproses: '.$laporan['piutang']['jumlah_pinjaman'].' kontrak ('.$laporan['piutang']['jumlah_cicilan'].' jadwal cicilan baru)');
        $this->line('Total Piutang Migrasi    : '.format_rupiah($laporan['piutang']['total_piutang_cents'] / 100));
        $this->line('Saldo Kas Awal Tercatat  : '.format_rupiah($laporan['saldo_kas_awal_cents'] / 100));

        if ($laporan['piutang']['dilewati'] !== []) {
            $this->newLine();
            $this->warn('Baris piutang yang dilewati:');
            foreach ($laporan['piutang']['dilewati'] as $peringatan) {
                $this->line(' - '.$peringatan);
            }
        }

        if ($laporan['anggota']['placeholders'] !== []) {
            $this->newLine();
            $this->warn('Daftar anggota dengan Nomor HP Placeholder (segera lengkapi melalui menu Data Anggota):');
            $this->table(
                ['Nomor Anggota', 'Nama Lengkap', 'No. HP Placeholder'],
                $laporan['anggota']['placeholders'],
            );
        } else {
            $this->info('Seluruh anggota memiliki nomor HP valid (tidak ada nomor HP placeholder).');
        }
    }

    private function ensureAdminExists(Carbon $tanggalDefault, string $passwordAwalHash): User
    {
        $admin = User::query()->where('peran', User::PERAN_ADMIN)->first();

        if ($admin !== null) {
            return $admin;
        }

        return User::query()->create([
            'nomor_anggota' => 'K.00.001',
            'nama_lengkap' => 'Admin Koperasi',
            'no_hp' => '081200000001',
            'password' => $passwordAwalHash,
            'peran' => User::PERAN_ADMIN,
            'status' => User::STATUS_AKTIF,
            'wajib_simpanan' => false,
            'tanggal_gabung' => $tanggalDefault->toDateString(),
            'tanggal_keluar' => null,
        ]);
    }

    private function ensurePengaturanExists(User $admin, Carbon $tanggalDefault): PengaturanKoperasi
    {
        $pengaturan = PengaturanKoperasi::query()
            ->latest('diperbarui_pada')
            ->latest('created_at')
            ->first();

        if ($pengaturan !== null) {
            return $pengaturan;
        }

        return PengaturanKoperasi::query()->create([
            'nominal_simpanan_pokok' => '100000.00',
            'nominal_simpanan_wajib' => '50000.00',
            'persen_margin_barang' => '15.00',
            'diperbarui_oleh' => $admin->id,
            'diperbarui_pada' => $tanggalDefault->copy()->setTime(8, 0, 0),
        ]);
    }

    /**
     * @param  list<string>  $candidates
     */
    private function findSheet(Spreadsheet $spreadsheet, array $candidates): ?Worksheet
    {
        foreach ($candidates as $name) {
            $sheet = $spreadsheet->getSheetByName($name);
            if ($sheet !== null) {
                return $sheet;
            }
        }

        return null;
    }

    private function resolveFilePath(string $path): string
    {
        if (is_file($path)) {
            return $path;
        }

        $baseCandidate = base_path($path);
        if (is_file($baseCandidate)) {
            return $baseCandidate;
        }

        return $path;
    }

    private function readStringCell(Worksheet $sheet, string $column, int $row): string
    {
        $value = $sheet->getCell($column.$row)->getCalculatedValue();

        return trim((string) ($value ?? ''));
    }

    private function parseNumericCell(Worksheet $sheet, string $column, int $row): float
    {
        return $this->parseNumericValue($sheet->getCell($column.$row)->getCalculatedValue());
    }

    private function parseNumericValue(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $cleaned = preg_replace('/[^0-9,.\-]/', '', (string) $value) ?? '';
        if ($cleaned === '') {
            return 0.0;
        }

        // Tangani format Indonesia seperti "5.000.000,50" atau "5.000.000"
        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (substr_count($cleaned, '.') > 1) {
            $cleaned = str_replace('.', '', $cleaned);
        }

        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }

    private function parseDateCell(Worksheet $sheet, string $column, int $row): ?Carbon
    {
        $value = $sheet->getCell($column.$row)->getCalculatedValue();

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && (float) $value > 20000) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizePhoneNumber(string $noHp): string
    {
        $digits = preg_replace('/\D+/', '', $noHp) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '620')) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0'.$digits;
        }

        return $digits;
    }

    private function generateUniquePlaceholderHp(string $nomorAnggota, int &$counter): string
    {
        $angkaNomor = preg_replace('/\D+/', '', $nomorAnggota) ?? '';

        if ($angkaNomor !== '') {
            $kandidat = '08000'.str_pad(substr($angkaNomor, -7), 7, '0', STR_PAD_LEFT);
            if (! User::query()->where('no_hp', $kandidat)->exists()) {
                return $kandidat;
            }
        }

        do {
            $kandidat = '080009'.str_pad((string) $counter, 6, '0', STR_PAD_LEFT);
            $counter++;
        } while (User::query()->where('no_hp', $kandidat)->exists());

        return $kandidat;
    }
}
