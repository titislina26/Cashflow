# Standar Tampilan & Layout UI (Anti-Scroll Horizontal)

> **Prinsip Utama:** Setiap fitur atau tampilan yang tidak muat dalam 1 layar dan harus di-scroll ke kanan/kiri (horizontal scroll) **WAJIB langsung dialihkan menggunakan pola Master-Detail Drill-Down (Popup Modal ala SIAKAD)**.

---

## 1. Larangan Mutlak: No Horizontal Scrollbar
- **DILARANG** memaksakan tabel dengan 8–12 kolom berjejer ke samping di layar utama hingga memicu `overflow-x: scroll` atau `overflow-x: auto` di desktop/laptop.
- **DILARANG** membuat tampilan perantara yang bertumpuk-tumpuk (*too much*) atau tab berlebih saat satu alur drill-down sudah cukup.

---

## 2. Pola Baku: Master-Detail Drill-Down (SIAKAD Standard)
Jika sebuah data memiliki rincian banyak kolom, sub-transaksi, atau rincian per-pemegang/per-akun:

### A. Tampilan Utama (Master / Layar 1) — Muat 1 Layar Penuh
- Sajikan dalam bentuk **Ringkasan Kartu (Summary Cards)** atau **Tabel Ringkas (4–5 kolom utama saja)**.
- Informasi yang ditampilkan hanya yang krusial:
  - Identitas (Kode / Nama Akun / Kategori / Sumber)
  - Jumlah Entitas (misal: *1 Orang*, *5 Transaksi*)
  - Total Saldo / Nominal
  - Aksi / Indikator Interaksi (*"Lihat Rincian"* / chevron icon)
- Pastikan 100% muat dalam 1 layar tanpa scroll samping.

### B. Tampilan Rincian (Detail Popup Modal) — Dibuka Saat Diklik
Ketika pengguna mengklik kartu atau baris pada tampilan utama:
1. **Buka Modal Dialog Terfokus** (`max-width: 780px` – `850px`, `width: 95%`).
2. **Kotak Info Metadata di Atas (SIAKAD Header Info Box)**:
   - Background lembut (`rgba(0,0,0,0.03)` atau soft grey-blue) dengan border tegas.
   - Format 2 kolom: Periode/Status, Nama Akun/Sumber, Kode Akun, Total Akun, Jumlah Pemegang/Entitas.
3. **Tabel Rincian Terfokus di Bawah**:
   - Kolom yang relevan saja dengan lebar minimum yang proporsional.
   - **WAJIB dibungkus pembungkus `.table-scroll-x` dengan `overflow-x: auto !important;`**.
   - **DILARANG MENGGUNAKAN `overflow: hidden;`** pada pembungkus tabel yang memotong kolom.
   - Jika layar atau modal lebih sempit daripada total lebar kolom tabel, tabel **HARUS BISA di-scroll ke samping (kanan/kiri)** dengan scrollbar yang responsif dan jelas agar tidak ada kolom yang terpotong.
   - Nomor urut di tengah (`text-align: center`).
   - Nominal rata kanan dengan font monospace (`font-mono-num font-bold`).
   - Warna aksen konsisten (hijau untuk masuk, merah untuk keluar, oranye/amber untuk panjar).
4. **Scroll Vertikal & Horizontal Terkendali**:
   - Batasi tinggi body modal dengan `max-height: 520px; overflow-y: auto;`.
   - Scroll horizontal aktif secara mulus pada tabel (`.table-scroll-x`) jika lebar layar terbatas.
5. **Navigasi Jelas**:
   - Tombol close `[X]` di kanan atas header.
   - Tombol kembali sekunder di footer modal (*"Kembali ke Pilihan"*).

---

## 3. Eksekusi Otomatis ("Terapkan Langsung Seperti Itu")
- Setiap kali merancang fitur baru atau merapikan tampilan yang padat data, **langsung terapkan arsitektur ini**.
- Jangan meminta konfirmasi berulang kali untuk hal layout jika data sudah jelas melebihi lebar layar nyaman (1366x768 / 1080p).
