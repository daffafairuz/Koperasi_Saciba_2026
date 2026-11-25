# Panduan Deployment & Migrasi SQLite ke PostgreSQL — Koperasi Jasa Berkah Saciba

Dokumen ini adalah checklist langkah demi langkah untuk memindahkan aplikasi **Koperasi Jasa Berkah Saciba** dari lingkungan **Development (SQLite)** ke **Production (PostgreSQL)** secara aman dan terstruktur.

---

## 1. Prasyarat Server & Ekstensi PHP

Pastikan server produksi (VPS / Cloud / Dedicated Server) memenuhi spesifikasi berikut:

- **PHP:** `>= 8.3`
- **Database:** PostgreSQL `>= 15`
- **Composer:** `>= 2.x`
- **Node.js & NPM:** `>= 20.x` (untuk build aset Tailwind/Vite produksi)

### Ekstensi PHP yang Wajib Aktif
Verifikasi melalui `php -m` bahwa ekstensi berikut sudah terpasang dan aktif:

| Ekstensi | Fungsi |
| :--- | :--- |
| `pdo_pgsql` & `pgsql` | Driver koneksi Laravel ke PostgreSQL |
| `zip` | Membaca & mengekstrak file Excel `.xlsx` (`phpoffice/phpspreadsheet`) |
| `gd` | Pemrosesan gambar/spreadsheet pada `phpoffice/phpspreadsheet` |
| `xml`, `simplexml`, `dom`, `mbstring`, `iconv` | Parsing XML internal spreadsheet & manipulasi string UTF-8 |
| `bcmath` / `ctype`, `fileinfo`, `openssl`, `tokenizer` | Kebutuhan inti Laravel 13 |

Contoh instalasi ekstensi di Ubuntu/Debian (PHP 8.3):

```bash
sudo apt update
sudo apt install -y php8.3-pgsql php8.3-zip php8.3-gd php8.3-xml php8.3-mbstring php8.3-curl php8.3-bcmath
```

---

## 2. Persiapan Database PostgreSQL

Buat database dan user khusus untuk aplikasi (jangan gunakan superuser `postgres` di `.env` produksi):

```sql
CREATE USER saciba_app WITH PASSWORD 'GANTI_PASSWORD_KUAT_DI_SINI';
CREATE DATABASE koperasi_saciba OWNER saciba_app ENCODING 'UTF8';
GRANT ALL PRIVILEGES ON DATABASE koperasi_saciba TO saciba_app;
```

---

## 3. Konfigurasi `.env` Produksi

Salin `.env.example` menjadi `.env`, jalankan `php artisan key:generate` (jika `APP_KEY` belum ada), lalu sesuaikan variabel berikut:

```env
APP_NAME="Koperasi Jasa Berkah Saciba"
APP_ENV=production
APP_KEY=base64:ISI_DENGAN_HASIL_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://koperasi-saciba.example.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=daily
LOG_LEVEL=warning

# Koneksi Database PostgreSQL Production
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=koperasi_saciba
DB_USERNAME=saciba_app
DB_PASSWORD=GANTI_PASSWORD_KUAT_DI_SINI

# Session, Cache & Queue
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database
```

> **Catatan Kompatibilitas:** Seluruh file migration (`UUID`, `decimal(15, 2)`, `string` status/jenis tanpa `ALTER TYPE` enum vendor-spesifik) dan seluruh query builder (`LOWER(...) LIKE`, `CASE WHEN`, `Carbon`) sudah dirancang **100% portabel** dari SQLite ke PostgreSQL tanpa perlu mengubah kode aplikasi.

---

## 4. Urutan Eksekusi Perintah Deploy

Jalankan perintah berikut secara berurutan dari direktori root proyek di server produksi:

```bash
# 1. Instal dependensi PHP tanpa dev-dependencies & optimasi autoload
composer install --no-dev --optimize-autoloader

# 2. Build aset frontend produksi (Tailwind CSS + Vite)
npm ci && npm run build

# 3. Jalankan seluruh database migration di PostgreSQL
php artisan migrate --force

# 4. (Opsional) Seed akun Admin awal & Pengaturan Koperasi default jika belum ada
#    Catatan: ImportDataExcelKoperasiCommand juga otomatis menyiapkan PengaturanKoperasi & Admin default jika belum ada
php artisan db:seed --class=UserSeeder --force
php artisan db:seed --class=PengaturanKoperasiSeeder --force

# 5. Impor data pembukuan Excel koperasi ke PostgreSQL (letakkan file .xlsx di storage/app/)
#    Sebelum menjalankan, pastikan mapping nama sheet & kolom di ImportDataExcelKoperasiCommand.php sesuai file asli
php artisan koperasi:import-excel storage/app/buku_koperasi.xlsx

# 6. Optimasi cache konfigurasi, route, event, dan view untuk performa produksi
php artisan optimize
```

> **Penting:** Segera ubah kata sandi akun Admin bawaan setelah login pertama kali dan lengkapi nomor HP anggota yang masih berformat placeholder (`08000...`) melalui menu **Data Anggota**.

---

## 5. Pengaturan Cron Job Scheduler (Tagihan SW Tanggal 1)

Tambahkan tepat **1 baris Cron Job** pada server (`crontab -e`) agar Laravel Scheduler menjalankan `koperasi:generate-tagihan-sw` secara otomatis setiap **tanggal 1 pukul 00:05 WIB**:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Verifikasi bahwa jadwal sudah terbaca oleh Laravel:

```bash
php artisan schedule:list
```

Output yang diharapkan:

```text
5 0 1 * * php artisan koperasi:generate-tagihan-sw  Next Due: ...
```

*(Sebagai cadangan bila server/shared hosting tidak mendukung cron, Admin tetap dapat memicu penerbitan tagihan bulan berjalan secara manual melalui menu **Pengaturan Koperasi -> Terbitkan Tagihan SW Bulan Ini Secara Manual**.)*

---

## 6. Checklist Verifikasi Pasca-Deploy

- [ ] `APP_ENV=production` dan `APP_DEBUG=false` di `.env`.
- [ ] Koneksi PostgreSQL berhasil dan seluruh 12 tabel (`anggota`, `sessions`, `pengaturan_koperasi`, `riwayat_status_anggota`, `simpanan_anggota`, `tagihan_simpanan_wajib`, `pinjaman`, `cicilan_pinjaman`, `pembayaran_anggota`, `detail_pembayaran`, `transaksi_kas_usaha`, `migrations`) terbentuk lewat `php artisan migrate:status`.
- [ ] Hasil impor Excel terverifikasi: **Total Kas Koperasi**, **Total Dana Simpanan**, dan **Total Piutang Berjalan** di `/admin/dashboard` cocok dengan angka tutup buku Excel terakhir (`Neraca 2025` / `Dashboard`).
- [ ] Login akun `ADMIN` berhasil masuk `/admin/dashboard`, login akun `ANGGOTA` aktif berhasil masuk `/anggota/dashboard`, dan akun `KELUAR` ditolak login.
- [ ] Backup otomatis PostgreSQL (`pg_dump`) telah dijadwalkan harian.
