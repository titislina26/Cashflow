# Cashflow Management System

Aplikasi manajemen arus kas dan pembukuan keuangan (Cashflow & Accounting) berbasis Laravel. Dilengkapi dengan pencatatan transaksi masuk/keluar, pencatatan jurnal umum, neraca saldo (*trial balance*), buku besar, serta laporan keuangan.

---

## 📋 Persyaratan Sistem (Prerequisites)

Sebelum menjalankan aplikasi, pastikan perangkat Anda sudah terinstal:
- **PHP** >= 8.2
- **Composer** (Package Manager PHP)
- **Node.js** (v18+) & **NPM**
- **MySQL / MariaDB** (bisa menggunakan **XAMPP**, **Laragon**, atau MySQL standalone)
- **Git**

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal:

### 1. Clone Repositori
```bash
git clone https://github.com/titislina26/Cashflow.git
cd Cashflow
```

### 2. Install Dependensi (Composer & NPM)
```bash
composer install
npm install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env` lalu generate application key:

```bash
# Windows PowerShell / CMD:
copy .env.example .env

# Atau di Git Bash / Linux / macOS:
cp .env.example .env
```

Generate App Key:
```bash
php artisan key:generate
```

---

## 🗄️ Pengaturan Database (Penting!)

Aplikasi ini menggunakan database MySQL dengan nama bawaan **`cashflow_db`**.

### Langkah A: Siapkan Database MySQL
1. Nyalakan service MySQL Anda (contoh: klik tombol **Start** pada MySQL di control panel **XAMPP** atau **Laragon**).
2. Buka browser dan akses **phpMyAdmin** (biasanya di `http://localhost/phpmyadmin`).
3. Buat database baru dengan nama: **`cashflow_db`** (Collation: `utf8mb4_unicode_ci` atau default).

### Langkah B: Periksa Konfigurasi di file `.env`
Buka file `.env` dan pastikan bagian konfigurasi database sudah sesuai:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cashflow_db
DB_USERNAME=root
DB_PASSWORD=
```
*(Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` jika MySQL Anda menggunakan password khusus).*

### Langkah C: Jalankan Migrasi & Data Awal (Seeder)
Jalankan perintah ini di terminal proyek:

```bash
php artisan migrate --seed
```

> **Catatan:** Perintah di atas akan secara otomatis membuat seluruh struktur tabel (`users`, `categories`, `transactions`, `jobs`, `journal_entries`, dll.) serta mengisi data master kategori awal (`CategorySeeder`).

---

## 💻 Menjalankan Server Aplikasi

Buka dua jendela terminal untuk menjalankan backend dan frontend:

**Terminal 1 (Server Laravel):**
```bash
php artisan serve
```

**Terminal 2 (Compiler Asset Frontend):**
```bash
npm run dev
```

Buka browser Anda dan akses:
👉 **`http://localhost:8000`**

---

## ✨ Fitur Utama
- **Dashboard Overview**: Ringkasan saldo kas, pemasukan, dan pengeluaran.
- **Transaksi Kas**: Catat kas masuk dan kas keluar dengan upload lampiran/bukti transfer.
- **Kategori & Chart of Accounts (COA)**: Manajemen akun/kategori, import template CSV/Excel.
- **Record Journal Entry**: Pencatatan jurnal umum debet-kredit.
- **Laporan Keuangan**:
  - Laporan Arus Kas & Ringkasan Transaksi
  - Buku Besar (*General Ledger*)
  - Neraca Saldo (*Trial Balance*)
  - Laporan Laba Rugi (*Profit and Loss*) & Neraca (*Balance Sheet*)
- **Jobs / Multi-Project**: Pencatatan transaksi berdasarkan sub-pekerjaan/proyek tertentu.
