---
name: accounting_helper
description: Panduan dan aturan bagi agen AI dalam menganalisis kas, mengaudit transaksi keuangan, merancang skema akuntansi, dan mengintegrasikan Xero / Norman API.
---

# 📊 Customization Skill: Accounting & Cashflow Helper

Skill ini membantu agen AI dalam merancang, menganalisis, mengaudit, dan memperluas fungsionalitas akuntansi di dalam aplikasi **Cashflow Management**.

---

## 🏛️ 1. Standar Akuntansi & Pembukuan Kas

Saat membuat, mengedit, atau menganalisis transaksi kas di aplikasi ini, patuhi aturan berikut:
1.  **Dua Sumber Dana Terpisah**:
    *   `petty_cash` (Kas Kecil): Pengeluaran kecil operasional harian (di bawah Rp 5.000.000). Batas atas nominal saldo biasanya dibatasi.
    *   `bank` (Kas Bank): Transaksi nominal besar (pembayaran vendor, gaji, investasi, invoice penjualan besar).
2.  **Klasifikasi Tipe Kas**:
    *   `income` (Pemasukan / Cash In): Uang masuk ke sistem.
    *   `expense` (Pengeluaran / Cash Out): Uang keluar dari sistem.
3.  **Kategori & Emoji Bawaan**:
    *   **Pemasukan**: SPP Teknik Informatika (💻), SPP Teknik Sipil (🏗️), SPP Teknik Lingkungan (🌱), Pendaftaran Maba (📝), Hibah & Kerjasama (🤝), Pendapatan Lainnya (💵).
    *   **Pengeluaran**: Gaji Dosen & Staf (👥), Operasional Kampus (⚙️), Sewa & Utilitas Gedung (🏢), Beasiswa Mahasiswa (🎓), Kegiatan Mahasiswa (UKM) (🎯), Pajak & Retribusi (🏛️), Sarana & Prasarana (Sarpras) (🔧), Pengeluaran Lainnya (📋).

---

## 🛠️ 2. Panduan Integrasi API Akuntansi Eksternal (Referensi)

Untuk melakukan sinkronisasi dengan platform akuntansi luar seperti **Xero** atau **Norman Finance**, berikut adalah panduan pemetaan objek data (*data mapping*):

### A. Sinkronisasi dengan Xero (Bank Transaction API)
Ketika menarik data dari Xero ke aplikasi [database.sqlite](file:///d:/Cashflow/database/database.sqlite):
*   **Xero `BankTransaction`** -> **Laravel `Transaction`**:
    *   `BankTransactionID` -> Dipetakan sebagai referensi/metadata unik transaksi.
    *   `Type` (`RECEIVE` / `SPEND`) -> Dipetakan ke field `type` (`income` / `expense`).
    *   `TotalAmount` -> Dipetakan ke field `amount`.
    *   `Date` -> Dipetakan ke field `date`.
    *   `Reference` / `Description` -> Dipetakan ke field `description`.

### B. Integrasi Norman Finance (Pencatatan PPN & Faktur)
Ketika mengirimkan data untuk pelaporan PPN:
*   Kirim transaksi dari kategori `Penjualan` (Pemasukan) atau `Pembelian Bahan` (Pengeluaran) yang memiliki catatan pajak ke endpoint Norman API:
    ```json
    {
      "transaction_id": "laravel_id",
      "amount_gross": 1110000,
      "amount_net": 1000000,
      "tax_amount": 110000,
      "tax_rate": 0.11,
      "description": "Faktur Pajak - Pembelian Bahan Baku"
    }
    ```

---

## 🔍 3. Prosedur Audit & Analisis Kas (Garis Tren & Estimasi)

Saat user meminta analisis kas, ikuti metodologi perhitungan di bawah ini:
1.  **Rasio Burn Rate**:
    *   Dihitung dengan membagi rata-rata `expense` 3 bulan terakhir dengan saldo bersih saat ini untuk mengetahui ketahanan kas kecil.
2.  **Perhitungan Tren Linier (Slope)**:
    *   Gunakan linear regression slope $m = \frac{N\sum(xy) - \sum x \sum y}{N\sum(x^2) - (\sum x)^2}$ untuk memproyeksikan arus kas ke depan, dengan $x$ sebagai indeks bulan (0, 1, 2) dan $y$ sebagai nominal total transaksi bulan tersebut.
3.  **Persentase Skema Skenario Proyeksi**:
    *   **Optimis**: Pemasukan $+20\%$, Pengeluaran $-15\%$ dari skenario realistik.
    *   **Pesimis**: Pemasukan $-20\%$, Pengeluaran $+15\%$ dari skenario realistik.
