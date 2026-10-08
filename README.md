# Cashflow Management System

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%2B-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B%20%7C%208.0%2B-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![Node.js](https://img.shields.io/badge/Node.js-v18%2B-339933?logo=node.js&logoColor=white)](https://nodejs.org)
[![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer&logoColor=white)](https://getcomposer.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Aplikasi manajemen arus kas dan pembukuan akuntansi (*Cashflow & Accounting*) berbasis Laravel. Dilengkapi dengan pencatatan multi-item transaksi, mutasi/transfer kas bank, pencatatan jurnal umum, neraca saldo (*trial balance*), buku besar (*general ledger*), cetak bukti voucher (*print voucher*), serta laporan laba rugi & posisi keuangan.

---

## 🏷️ Requirements (Persyaratan Sistem)

Sebelum menginstal dan menjalankan aplikasi, pastikan perangkat Anda memenuhi spesifikasi kebutuhan berikut:

| Komponen | Spesifikasi Minimum | Keterangan / Rekomendasi |
| :--- | :--- | :--- |
| **PHP** | `v8.2` ke atas (disarankan `8.3`) | Ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` |
| **Composer** | `v2.x` | Package manager dependensi PHP |
| **Node.js & NPM** | Node.js `v18.x` atau `v20.x` LTS, NPM `v9+` | Untuk kompilasi aset frontend dan Vite |
| **Database** | MySQL `5.7+` / `8.0+` atau MariaDB `10.4+` | Bisa melalui **XAMPP**, **Laragon**, Docker, atau MySQL Standalone |
| **Web Server** | PHP Built-in Server / Apache / Nginx | Untuk server lokal: `php artisan serve` |
| **Git** | Versi terbaru | Untuk kloning repositori |

---

## 🚀 Panduan Instalasi (Cara Install)

Ikuti langkah-langkah instalasi berikut secara berurutan:

### 1. Clone Repositori
Buka terminal (Git Bash, Command Prompt, atau PowerShell), lalu clone proyek ini:
```bash
git clone https://github.com/titislina26/Cashflow.git
cd Cashflow
```

### 2. Install Dependensi Backend (Composer)
Jalankan instalasi pustaka PHP:
```bash
composer install
```

### 3. Install Dependensi Frontend (NPM)
Jalankan instalasi paket Node.js:
```bash
npm install
```

### 4. Konfigurasi Environment File (`.env`)
Salin file konfigurasi contoh `.env.example` menjadi `.env`:

- **Windows (PowerShell / CMD):**
  ```bash
  copy .env.example .env
  ```
- **Linux / macOS / Git Bash:**
  ```bash
  cp .env.example .env
  ```

Kemudian buat kunci aplikasi enkripsi (*Application Key*):
```bash
php artisan key:generate
```

---

## 🗄️ Pengaturan Database

Aplikasi menggunakan database MySQL dengan nama default **`cashflow_db`**.

### Langkah A: Buat Database MySQL
1. Pastikan service MySQL sedang aktif (klik **Start** MySQL di XAMPP / Laragon).
2. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`) atau aplikasi SQL client pilihan Anda (HeidiSQL, DBeaver, TablePlus).
3. Buat database baru dengan nama:
   ```sql
   CREATE DATABASE cashflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### Langkah B: Sesuaikan Konfigurasi di `.env`
Buka file `.env` pada root direktori proyek, lalu periksa konfigurasi koneksi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cashflow_db
DB_USERNAME=root
DB_PASSWORD=
```
> **Catatan:** Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` jika instalasi MySQL lokal Anda menggunakan password khusus.

### Langkah C: Jalankan Migrasi & Data Seeder Awal
Jalankan perintah berikut untuk membuat seluruh tabel dan mengimpor data awal (kategori akun/COA):
```bash
php artisan migrate --seed
```

---

## 📁 Setup Penyimpanan File (Storage Link)
Aplikasi memiliki fitur upload bukti transfer dan dokumen lampiran (*attachments*). Hubungkan direktori storage ke direktori publik:
```bash
php artisan storage:link
```

---

## 💻 Menjalankan Aplikasi di Lokal

Jalankan perintah berikut untuk menjalankan server aplikasi:

### Opsi 1: Menjalankan Development Server (Dua Terminal)

- **Terminal 1** (Server backend Laravel):
  ```bash
  php artisan serve
  ```
- **Terminal 2** (Compiler aset Vite frontend):
  ```bash
  npm run dev
  ```

### Opsi 2: Build Aset Sekali Saja (Satu Terminal)
Jika tidak ingin menjalankan server Vite secara terpisah:
```bash
npm run build
php artisan serve
```

Akses aplikasi melalui browser:
👉 **[http://localhost:8000](http://localhost:8000)** atau **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## ✨ Fitur-Fitur Utama

- 📊 **Dashboard Keuangan**: Ringkasan saldo kas, grafik arus kas, tren pemasukan & pengeluaran.
- 💳 **Multi-Account Register**: Pencatatan terpisah untuk *Petty Cash* (Kas Kecil), *Bank Mandiri Giro I*, dan *Bank Mandiri Giro II*.
- 📑 **Rincian Pos Transaksi (Multi-Item)**:
  - Mencatat lebih dari satu rincian pos transaksi dalam satu nomor bukti dokumen (*voucher*).
  - Berlaku untuk Pemasukan, Pengeluaran, maupun Mutasi/Transfer Kas (*Pencairan Bank TTN*).
- 🖨️ **Cetak Bukti Voucher (*Print Voucher*)**: Format cetak standar dokumen bukti kas keluar/masuk dengan nomor bukti, terbilang otomatis, dan kolom tanda tangan berjenjang.
- 🔄 **Transfer Kas & Bank**: Pencatatan mutasi internal kas & bank berpasangan (*double-entry*) tanpa mempengaruhi laporan surplus defisit.
- 📚 **Buku Besar (*General Ledger*)**: Monitoring mutasi akun secara komprehensif.
- ⚖️ **Neraca Saldo (*Trial Balance*)**: Validasi keseimbangan debet dan kredit akun.
- 📈 **Laporan Laba Rugi & Neraca**: Laporan surplus/defisit dan posisi keuangan standar.
- 👥 **Daftar Pengaju / Paraf**: Otomatisasi data penerima/pengaju dari daftar personil.

---

## 🛠️ Perintah Berguna Tambahan

| Perintah | Deskripsi |
| :--- | :--- |
| `php artisan test` | Menjalankan seluruh pengujian unit & fitur aplikasi |
| `php artisan optimize:clear` | Menghapus seluruh cache (config, route, view) saat update kode |
| `php artisan migrate:fresh --seed` | Mengosongkan ulang database dan mengisi data seeder dari awal |
