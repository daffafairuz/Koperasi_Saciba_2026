# Saciba Neo-Fintech Design System

Panduan desain visual resmi untuk aplikasi web **Koperasi Jasa Berkah Saciba**. Estetika **Modern Neo-Fintech** ini terinspirasi dari kejelasan **Wise**, presisi geometris **Linear**, dan kewibawaan finansial **Stripe**—dirancang tajam, minimalis, kontras tinggi, dan mudah dipindai untuk data keuangan.

---

## 1. Tipografi

| Penggunaan | Font Family | Kelas Tailwind | Catatan |
| :--- | :--- | :--- | :--- |
| **UI, Judul & Teks Utama** | `Plus Jakarta Sans` (Google Fonts) | `font-sans` | Tracking ketat pada heading (`tracking-tight`), bobot `600`–`800` untuk hierarki tegas. |
| **Nominal Rupiah, Kode & Periode** | `JetBrains Mono` (Google Fonts) | `font-mono tabular-nums` | Wajib menggunakan `tabular-nums` pada seluruh nominal `format_rupiah()`, nomor anggota (`K.10.101`), nomor transaksi (`TRX-...`, `PJM-...`, `KAS-...`), dan tanggal agar kolom angka sejajar vertikal. |

### Skala Tipografi Inti
- **Hero Balance Display:** `text-3xl sm:text-4xl font-bold tracking-tight font-mono tabular-nums`
- **Page Title:** `text-xl sm:text-2xl font-bold tracking-tight text-zinc-950`
- **Card / Section Title:** `text-sm sm:text-base font-semibold tracking-tight text-zinc-900`
- **KPI Label / Table Header:** `text-[11px] font-semibold uppercase tracking-wider text-zinc-500`
- **Body / Table Cell:** `text-sm text-zinc-700`

---

## 2. Palet Warna (*Obsidian + Vibrant Emerald + Crisp Zinc*)

### Permukaan & Kanvas (*Surfaces*)
- **Main Canvas:** `bg-zinc-50` (`#FAFAFA`) — bersih, terang, bebas warna abu kusam (`gray-100`) atau krem.
- **Primary Card / Container:** `bg-white border border-zinc-200/80 rounded-2xl shadow-xs`
- **Subtle Inset Surface:** `bg-zinc-50/80 border border-zinc-200/60 rounded-xl`
- **Sticky Glass Header:** `backdrop-blur-md bg-white/80 border-b border-zinc-200/80`
- **Floating Glass Bottom Bar (Mobile):** `backdrop-blur-lg bg-white/90 border-t border-zinc-200`

### Hero Card (Saldo Utama / Spotlight Finansial)
- **Container:** `bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 rounded-2xl`
- **Nominal Sorotan:** `text-emerald-400 font-mono tabular-nums`
- **Sub-label:** `text-zinc-400` / `text-emerald-200/80`

### Tombol & Aksi (*Interactive Controls*)
- **Standard Primary Action (Obsidian):** `bg-zinc-900 hover:bg-zinc-800 active:bg-zinc-950 text-white rounded-xl font-semibold transition`
- **Financial / High-Impact CTA (Vibrant Emerald):** `bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-xl font-semibold shadow-xs transition`
- **Secondary / Outline Button:** `bg-white hover:bg-zinc-50 text-zinc-700 border border-zinc-200/90 rounded-xl font-medium transition`
- **Destructive / Soft-Exit Action:** `bg-rose-600 hover:bg-rose-500 text-white rounded-xl font-semibold transition`
- **Focus Ring Standar:** `focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500`

---

## 3. Badge Status (*Micro-Pills*)

Gunakan gaya *translucent tint* + *hairline border* agar bersih dan tidak mencolok berlebihan:

| Status / Kategori | Kelas Tailwind |
| :--- | :--- |
| **`LUNAS` / `AKTIF` / `MASUK`** | `inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-semibold` |
| **`SEBAGIAN` / `BERJALAN`** | `inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 text-amber-700 border border-amber-500/20 px-2.5 py-0.5 text-[11px] font-semibold` |
| **`BELUM_BAYAR` / `KELUAR`** | `inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 text-rose-700 border border-rose-500/20 px-2.5 py-0.5 text-[11px] font-semibold` |
| **Netral / Metadata (`UANG`, `TUNAI`, `TRANSFER`, `Migrasi`)** | `inline-flex items-center rounded-md bg-zinc-100 text-zinc-700 border border-zinc-200/80 px-2 py-0.5 text-[11px] font-medium` |
| **Aksen Pembiayaan Barang (`BARANG`)** | `inline-flex items-center rounded-full bg-zinc-900/5 text-zinc-900 border border-zinc-900/15 px-2.5 py-0.5 text-[11px] font-semibold` |

---

## 4. Tata Letak (*Bento Grid*) & Kerangka Layout

1. **Bento Grid:**
   - Gunakan grid responsif (`grid-cols-1 md:grid-cols-12 gap-4` atau `gap-5`) dengan radius konsisten `rounded-2xl` dan jarak visual terukur.
   - Kartu utama (Saldo Kas / Total Simpanan Akhir) menempati proporsi lebih menonjol dengan tema gelap Obsidian–Emerald, didampingi kartu metrik putih bersih (`bg-white border-zinc-200/80`).
2. **Halaman Autentikasi (`auth/login.blade.php`):**
   - *Split-screen* futuristik: sisi kiri panel Obsidian `bg-zinc-950` dengan *radial emerald glow* dan ringkasan pilar transparansi; sisi kanan area form bersih dengan fokus ring `focus:ring-emerald-500`.
3. **Panel Admin (`layouts/admin.blade.php`):**
   - Sidebar putih minimalis (`bg-white border-r border-zinc-200/80`) dengan indikator item aktif berupa pil **Obsidian (`bg-zinc-900 text-white`) + titik Emerald (`bg-emerald-400`)**.
   - Header lengket (*sticky*) dengan `backdrop-blur-md bg-white/80`.
   - Elemen navigasi diberi `print:hidden` agar struk pembayaran tercetak bersih.
4. **Portal Anggota (`layouts/anggota.blade.php`):**
   - Nuansa aplikasi fintech mobile modern: header *frosted glass*, navigasi tersegmentasi di desktop, dan *floating glass bottom bar* (`backdrop-blur-lg bg-white/90 border-t border-zinc-200`) di layar mobile.

---

## 5. Ikonografi & Aturan Ketat

- **Wajib SVG Garis Tipis:** Seluruh ikon menggunakan inline SVG bergaya **Lucide Icons** dengan `fill="none"`, `stroke="currentColor"`, `stroke-width="1.75"`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Dilarang Emoji:** Tidak diperbolehkan menggunakan emoji Unicode pada antarmuka, tombol, badge, maupun tabel.
- **Anti-Flash Alpine.js:** Selalu sertakan `[x-cloak] { display: none !important; }` pada setiap layout untuk mencegah kedipan (*flicker*) modal/accordion sebelum Alpine.js siap.
