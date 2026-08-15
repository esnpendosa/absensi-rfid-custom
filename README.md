# Sistem E-Absensi & Keuangan Sekolah
### SMK NURUL HIDAYAH

Sistem Informasi Manajemen Terpadu Sekolah Berbasis Laravel untuk Presensi RFID ESP32, Kasir Keuangan Sekolah, Laporan Kas Masuk, Persuratan Resmi, dan Direktori Alumni Tracer Study.

---

## 📑 Daftar Isi
1. [Fitur Utama Sistem (4 Pilar)](#-fitur-utama-sistem-4-pilar)
2. [Teknologi & Persyaratan Server](#-teknologi--persyaratan-server)
3. [Panduan Instalasi (Local & Hosting)](#-panduan-instalasi)
4. [Panduan Modul & Penggunaan](#-panduan-modul--penggunaan)
5. [Integrasi WhatsApp Gateway](#-integrasi-whatsapp-gateway)
6. [Integrasi Perangkat Mesin Absensi (ESP32)](#-integrasi-perangkat-mesin-absensi-esp32)
7. [Struktur Folder & Arsitektur](#-struktur-folder--arsitektur)
8. [Lisensi & Hak Cipta](#-lisensi)

---

## 🌟 Fitur Utama Sistem (4 Pilar)

### 1. Presensi & Monitoring Kehadiran (RFID / Barcode)
- **Multi-Mode Kehadiran:** Mendukung mode *Masuk Saja* (One-Tap) dan *Masuk + Pulang* (Two-Tap).
- **Aturan Jam Absensi:** Pengaturan fleksibel jam masuk, batas telat, jam mulai pulang, dan toleransi absen per hari.
- **Monitoring Real-time:** Dashboard pantauan presensi live per kelas dan rekap harian/bulanan/tahunan.
- **Kartu Siswa & Absensi:** Pembuatan ID card otomatis lengkap dengan Barcode / QR Code siap cetak.
- **Pengajuan Izin / Sakit:** Sistem verifikasi bukti surat dokter/izin orang tua secara digital.

### 2. Keuangan Sekolah (Kasir & Pos Dinamis)
- **Kategori Pos Keuangan Fleksibel:** Atur berbagai pos pembayaran seperti SPP Bulanan (12 Bulan), Uang Gedung/Pembangunan (Cicilan/Bebas), Ujian Semester, Seragam & Atribut.
- **Kasir Pembayaran Cepat:** Antarmuka kasir ringkas dengan filter kelas, status lunas/cicilan, dan modal input cepat.
- **Sinkronisasi Tagihan Otomatis:** Sistem secara otomatis mendistribusikan tagihan ke seluruh siswa terdaftar.
- **Nota Struk Kasir Thermal (POS Receipt):** Format nota struk mini kasir (58mm/80mm & A4) dengan barcode validasi.
- **Kirim Kuitansi ke WhatsApp Otomatis:** Setiap kali transaksi berhasil, sistem mengirimkan pesan rincian beserta tautan unduh nota PDF ke nomor WhatsApp siswa/orang tua.
- **Laporan Rekap Kas Masuk:** Filter rentang tanggal, kelas, pos pembayaran, dan metode bayar (Tunai, Transfer, QRIS) dengan format cetak dokumen resmi PDF lengkap tanda tangan Kepala Sekolah & Bendahara.

### 3. Persuratan & Arsip Resmi
- **Manajemen Surat Masuk & Surat Keluar:** Penomoran otomatis, klasifikasi surat, pengarsipan file digital PDF, dan pelacakan disposisi.
- **Daftar Arsip Digital:** Penyimpanan dokumen penting sekolah yang aman dan terorganisir.

### 4. Direktori Alumni & Tracer Study
- **Pelacakan Karir & Studi:** Mencatat riwayat alumni pasca kelulusan (*Bekerja, Kuliah di PTN/PTS, Wirausaha, Mencari Kerja*).
- **Input Manual & Kelulusan Massal:** Tambah data alumni langsung melalui form modal atau luluskan siswa kelas XII secara serentak dari modul Kenaikan Kelas.

---

## 🛠️ Teknologi & Persyaratan Server

### Stack Teknologi
- **Framework:** Laravel 12 (PHP 8.3+)
- **Database:** MySQL 8.0+ / MariaDB 10.4+
- **Frontend:** TailwindCSS, Vanilla JavaScript, FontAwesome Icons, SweetAlert2
- **Cetak Dokumen:** HTML5 Printable Templates (A4 & Thermal POS Receipt)

### Persyaratan Ekstensi PHP
- PHP >= 8.2 (Direkomendasikan **PHP 8.3**)
- `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PCRE`, `PDO`, `PDO_MySQL`, `Tokenizer`, `XML`

---

## 🚀 Panduan Instalasi

### A. Instalasi di Lingkungan Lokal (Laragon / XAMPP)

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/esnpendosa/absensi-rfid-custom.git
   cd absensi-rfid-custom
   ```

2. **Install Dependensi Composer & NPM:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Salin File Environment & Generate Key:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database di `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db_absensi_sekolah
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan Migrasi & Seeder:**
   ```bash
   php artisan migrate --seed
   ```

6. **Buat Symlink Storage & Bersihkan Cache:**
   ```bash
   php artisan storage:link
   php artisan optimize:clear
   ```

7. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Akses di browser: `http://127.0.0.1:8000`

---

### B. Panduan Deploy ke Hosting (Hostinger / cPanel)

1. Upload seluruh file proyek ke folder tujuan (misalnya `public_html` atau subfolder root).
2. Konfigurasikan `.env` dengan kredensial database hosting:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domainsekolah.sch.id

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=u123456_namadb
   DB_USERNAME=u123456_userdb
   DB_PASSWORD=password_db
   ```
3. Pastikan folder `storage` dan `bootstrap/cache` memiliki permission **775** atau **755** (dapat ditulis web server).
4. Jalankan perintah pembersihan cache melalui SSH Terminal Hosting:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

---

## 📖 Panduan Modul & Penggunaan

### 1. Modul Kasir Pembayaran (`/keuangan/pembayaran`)
- Membuka halaman kasir menampilkan 4 kartu ringkasan pemasukan kas, tagihan lunas, siswa terdaftar, dan sisa tunggakan.
- Klik tombol hijau **`+ Input Pembayaran`**:
  - Cari nama siswa/NISN.
  - Pilih pos tagihan (otomatis memuat nominal tagihan dan sisa tunggakan).
  - Masukkan nominal pembayaran & pilih metode (*Tunai, Transfer, QRIS*).
  - Klik **Simpan Pembayaran** -> Nota Kasir otomatis dicetak dan dikirim ke WhatsApp siswa.

### 2. Modul Laporan Keuangan (`/keuangan/laporan`)
- Menampilkan seluruh riwayat transaksi kas masuk.
- Atur filter rentang tanggal (*Dari Tanggal - Sampai Tanggal*), filter kelas, pos keuangan, atau metode pembayaran.
- Klik **Cetak Laporan Resmi** untuk mengunduh/mencetak dokumen PDF resmi ber-KOP sekolah.

### 3. Modul Kategori Pos Keuangan (`/keuangan/pos`)
- Tambah kategori baru dengan tipe:
  - `Bulanan`: Otomatis digenerate 12 bulan (Juli - Juni). Contoh: **SPP Bulanan**.
  - `Bebas / Angsuran`: Pembayaran total yang dapat dicicil berkala. Contoh: **Uang Gedung**.
  - `Sekali Bayar`: Tagihan tunggal per tahun ajaran. Contoh: **Ujian Semester, Seragam**.

### 4. Modul Alumni & Tracer Study (`/data-alumni`)
- Klik **`+ Tambah Alumni`** untuk menambahkan arsip lulusan langsung beserta status aktivitas saat ini (*Kuliah, Bekerja, Wirausaha, Mencari Kerja*).
- Atau gunakan fitur **Luluskan Siswa Kelas XII** di menu *Akademik -> Kenaikan Kelas*.

---

## 📲 Integrasi WhatsApp Gateway

Sistem dilengkapi `WaGatewayService` yang mendukung berbagai provider gateway WhatsApp (seperti Fonnte, Wablas, atau Gateway Mandiri):

1. Masuk ke menu **Pengaturan -> Pengaturan Notifikasi**.
2. Masukkan **Base URL Gateway** dan **API Token / API Key**.
3. Notifikasi otomatis aktif untuk:
   - ✅ Presensi kehadiran siswa (Jam Datang & Jam Pulang) ke wali murid.
   - ✅ Bukti pembayaran resmi kasir beserta tautan nota PDF digital ke nomor siswa/orang tua.

---

## 📡 Integrasi Perangkat Mesin Absensi (ESP32)

Mesin absensi berbasis microcontroller ESP32 / RFID RC522 berkomunikasi melalui REST API:

- **Endpoint:** `POST /api/absen/rfid` atau `POST /api/device/scan`
- **Headers:**
  ```http
  Content-Type: application/json
  X-Device-Token: your_registered_device_token
  ```
- **Payload Request:**
  ```json
  {
    "rfid_uid": "93A24B10",
    "timestamp": "2026-08-16 07:05:00"
  }
  ```
- **Response Sukses:**
  ```json
  {
    "status": "success",
    "message": "Presensi Berhasil: Masuk Tepat Waktu",
    "student_name": "Muhammad As'ad",
    "time": "07:05:00"
  }
  ```

---

## 📂 Struktur Folder & Arsitektur

```
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php         # Handler dashboard 4 pilar eksekutif
│   │   ├── KeuanganSekolahController.php   # Kasir, Pos, Laporan & Cetak Nota
│   │   ├── DataAlumniController.php        # Direktori Alumni & Tracer Study
│   │   ├── DataSiswaController.php         # Manajemen Siswa Aktif
│   │   └── PersuratanController.php        # Persuratan & Arsip Digital
│   ├── Models/
│   │   ├── Siswa.php                       # Data Siswa
│   │   ├── Alumni.php                      # Data Alumni & Tracer
│   │   ├── PosKeuangan.php                 # Pos/Kategori Pembayaran
│   │   ├── TagihanSiswa.php                # Rekening Tagihan Siswa
│   │   ├── TransaksiKeuangan.php           # Log Transaksi Kasir
│   │   └── Surat.php                       # Surat Masuk & Keluar
│   └── Services/
│       ├── StudentAttendanceService.php    # Logika Jam & Validasi Presensi
│       ├── WaGatewayService.php            # Service Notifikasi WhatsApp
│       └── Modules/AlumniRecordService.php # Service Pengelolaan Alumni
├── resources/
│   └── views/
│       ├── pages/                          # Halaman Tampilan Blade
│       │   ├── dashboard-admin.blade.php
│       │   ├── keuangan-pembayaran.blade.php
│       │   ├── keuangan-laporan.blade.php
│       │   ├── keuangan-pos.blade.php
│       │   └── data-alumni.blade.php
│       ├── partials/
│       │   └── sidebar.blade.php           # Navigasi Sidebar 4 Pilar
│       └── pdf/                            # Template Cetak Resmi
│           ├── kuitansi-keuangan.blade.php # Nota Kasir Thermal POS
│           └── laporan-keuangan.blade.php  # Laporan Kas Masuk A4
└── routes/
    ├── web.php                             # Routing Aplikasi Web
    └── api.php                             # Endpoint ESP32 & Webhook
```

---

## 📄 Lisensi

Dikembangkan untuk **SMK NURUL HIDAYAH**.
Hak Cipta dilindungi undang-undang.
