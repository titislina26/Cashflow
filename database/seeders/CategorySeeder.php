<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sumber: STT PEKERJAAN UMUM JAKARTA - Accounts List [Summary] (165 Akun)
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Category::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $categories = [
            // ========================================================
            // 1. AKTIVA / ASSETS (Awalan 1)
            // ========================================================
            ['code' => '1-1110', 'name' => 'Kas', 'type' => 'asset', 'icon' => 'wallet', 'color' => '#0ea5e9'],
            ['code' => '1-1120', 'name' => 'Bank Mandiri', 'type' => 'asset', 'icon' => 'building', 'color' => '#0284c7'],
            ['code' => '1-1121', 'name' => 'Bank Mandiri Giro I', 'type' => 'asset', 'icon' => 'landmark', 'color' => '#0369a1'],
            ['code' => '1-1122', 'name' => 'Bank Mandiri Giro II', 'type' => 'asset', 'icon' => 'landmark', 'color' => '#075985'],
            ['code' => '1-1191', 'name' => 'Deposito', 'type' => 'asset', 'icon' => 'coins', 'color' => '#06b6d4'],
            ['code' => '1-1200', 'name' => 'Piutang Usaha', 'type' => 'asset', 'icon' => 'receipt', 'color' => '#38bdf8'],
            ['code' => '1-1220', 'name' => 'Piutang Mahasiswa', 'type' => 'asset', 'icon' => 'graduation-cap', 'color' => '#60a5fa'],
            ['code' => '1-1230', 'name' => 'Piutang Kerjasama', 'type' => 'asset', 'icon' => 'handshake', 'color' => '#3b82f6'],
            ['code' => '1-1290', 'name' => 'Piutang Usaha Lainnya', 'type' => 'asset', 'icon' => 'file-text', 'color' => '#64748b'],
            ['code' => '1-1300', 'name' => 'Piutang Lain-lain', 'type' => 'asset', 'icon' => 'file-question', 'color' => '#64748b'],
            ['code' => '1-1310', 'name' => 'Piutang Karyawan', 'type' => 'asset', 'icon' => 'users', 'color' => '#6366f1'],
            ['code' => '1-1330', 'name' => 'Piutang Dosen', 'type' => 'asset', 'icon' => 'user-check', 'color' => '#818cf8'],
            ['code' => '1-1510', 'name' => 'Pajak Dibayar Dimuka', 'type' => 'asset', 'icon' => 'landmark', 'color' => '#eab308'],
            ['code' => '1-1599', 'name' => 'Biaya dibayar dimuka Lainnya', 'type' => 'asset', 'icon' => 'clock', 'color' => '#f59e0b'],
            ['code' => '1-1900', 'name' => 'Piutang Afiliasi', 'type' => 'asset', 'icon' => 'network', 'color' => '#a855f7'],
            ['code' => '1-1990', 'name' => 'Piutang YPP Pusat', 'type' => 'asset', 'icon' => 'building-2', 'color' => '#8b5cf6'],
            ['code' => '1-1995', 'name' => 'Piutang antar Unit Usaha', 'type' => 'asset', 'icon' => 'git-fork', 'color' => '#7c3aed'],
            ['code' => '1-2000', 'name' => 'Aktiva Tetap', 'type' => 'asset', 'icon' => 'boxes', 'color' => '#10b981'],
            ['code' => '1-2100', 'name' => 'Inventaris', 'type' => 'asset', 'icon' => 'package', 'color' => '#10b981'],
            ['code' => '1-2110', 'name' => 'Inventaris Kelas', 'type' => 'asset', 'icon' => 'armchair', 'color' => '#14b8a6'],
            ['code' => '1-2111', 'name' => 'Akumulasi Penystn Inv. Kelas', 'type' => 'asset', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '1-2120', 'name' => 'Inventaris Kantor', 'type' => 'asset', 'icon' => 'briefcase', 'color' => '#0d9488'],
            ['code' => '1-2121', 'name' => 'Akumulasi Penystn Inv. Kantor', 'type' => 'asset', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '1-2130', 'name' => 'Peralatan Laboratorium', 'type' => 'asset', 'icon' => 'flask-conical', 'color' => '#06b6d4'],
            ['code' => '1-2131', 'name' => 'Akumulasi Penystn Perltn Labor', 'type' => 'asset', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '1-2300', 'name' => 'Gedung', 'type' => 'asset', 'icon' => 'building', 'color' => '#059669'],
            ['code' => '1-2310', 'name' => 'Gedung', 'type' => 'asset', 'icon' => 'building', 'color' => '#059669'],
            ['code' => '1-2311', 'name' => 'Akumulasi Penyusutan Gedung', 'type' => 'asset', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '1-2400', 'name' => 'Tanah', 'type' => 'asset', 'icon' => 'map-pin', 'color' => '#15803d'],
            ['code' => '1-3000', 'name' => 'Harta / Aktiva Lain-lain', 'type' => 'asset', 'icon' => 'layers', 'color' => '#64748b'],
            ['code' => '1-3100', 'name' => 'Penyertaan Modal', 'type' => 'asset', 'icon' => 'pie-chart', 'color' => '#6366f1'],
            ['code' => '1-3110', 'name' => 'Penyertaan Modal Lemptek', 'type' => 'asset', 'icon' => 'pie-chart', 'color' => '#8b5cf6'],

            // ========================================================
            // 2. KEWAJIBAN / LIABILITIES (Awalan 2)
            // ========================================================
            ['code' => '2-1000', 'name' => 'Hutang Lancar', 'type' => 'liability', 'icon' => 'clock', 'color' => '#f97316'],
            ['code' => '2-1100', 'name' => 'Hutang Usaha', 'type' => 'liability', 'icon' => 'credit-card', 'color' => '#f97316'],
            ['code' => '2-1199', 'name' => 'Hutang Lain-lainnya', 'type' => 'liability', 'icon' => 'file-text', 'color' => '#fb923c'],
            ['code' => '2-1399', 'name' => 'Biaya YMHD lainnya', 'type' => 'liability', 'icon' => 'hourglass', 'color' => '#ea580c'],
            ['code' => '2-1400', 'name' => 'Hutang Pinjaman', 'type' => 'liability', 'icon' => 'banknote', 'color' => '#c2410c'],
            ['code' => '2-1410', 'name' => 'Hutang Bank', 'type' => 'liability', 'icon' => 'landmark', 'color' => '#b45309'],
            ['code' => '2-2110', 'name' => 'Hutang Bank Jangka Panjang', 'type' => 'liability', 'icon' => 'calendar-clock', 'color' => '#9a3412'],
            ['code' => '2-2210', 'name' => 'Hutang YPP Pusat', 'type' => 'liability', 'icon' => 'building-2', 'color' => '#d97706'],
            ['code' => '2-2220', 'name' => 'Hutang Unit Usaha', 'type' => 'liability', 'icon' => 'git-fork', 'color' => '#b45309'],
            ['code' => '2-2230', 'name' => 'Hutang Lemtek', 'type' => 'liability', 'icon' => 'layers', 'color' => '#92400e'],

            // ========================================================
            // 3. EKUITAS / EQUITY (Awalan 3)
            // ========================================================
            ['code' => '3-1110', 'name' => 'Modal disetor', 'type' => 'equity', 'icon' => 'scale', 'color' => '#8b5cf6'],
            ['code' => '3-1210', 'name' => 'Modal Donasi', 'type' => 'equity', 'icon' => 'gift', 'color' => '#a855f7'],
            ['code' => '3-3000', 'name' => 'Surplus (Defisit)', 'type' => 'equity', 'icon' => 'line-chart', 'color' => '#7c3aed'],
            ['code' => '3-3110', 'name' => 'Surplus (Defisit) Th Lalu', 'type' => 'equity', 'icon' => 'history', 'color' => '#6d28d9'],
            ['code' => '3-3120', 'name' => 'Surplus (Defisit) Th Berjalan', 'type' => 'equity', 'icon' => 'activity', 'color' => '#5b21b6'],
            ['code' => '3-9999', 'name' => 'Historical Balancing Account', 'type' => 'equity', 'icon' => 'sliders', 'color' => '#64748b'],

            // ========================================================
            // 4. PENDAPATAN OPERASIONAL / INCOME (Awalan 4)
            // ========================================================
            ['code' => '4-1100', 'name' => 'Pendapatan Uang Kuliah', 'type' => 'income', 'icon' => 'graduation-cap', 'color' => '#10b981'],
            ['code' => '4-1110', 'name' => 'SPP Mahasiswa Reguler', 'type' => 'income', 'icon' => 'credit-card', 'color' => '#10b981'],
            ['code' => '4-1115', 'name' => 'SPP Karyasiswa', 'type' => 'income', 'icon' => 'user-check', 'color' => '#059669'],
            ['code' => '4-1120', 'name' => 'Uang Ujian', 'type' => 'income', 'icon' => 'file-check', 'color' => '#10b981'],
            ['code' => '4-1130', 'name' => 'Uang Tugas Akhir/Skripsi', 'type' => 'income', 'icon' => 'book-open', 'color' => '#047857'],
            ['code' => '4-1140', 'name' => 'Uang Praktikum', 'type' => 'income', 'icon' => 'flask-conical', 'color' => '#059669'],
            ['code' => '4-1150', 'name' => 'Uang Wisuda', 'type' => 'income', 'icon' => 'award', 'color' => '#10b981'],
            ['code' => '4-1165', 'name' => 'Uang SKS', 'type' => 'income', 'icon' => 'calendar', 'color' => '#047857'],
            ['code' => '4-1170', 'name' => 'Uang Kegiatan Mahasiswa', 'type' => 'income', 'icon' => 'users', 'color' => '#10b981'],
            ['code' => '4-1175', 'name' => 'Uang Ijazah', 'type' => 'income', 'icon' => 'scroll', 'color' => '#059669'],
            ['code' => '4-1390', 'name' => 'Uang Formulir', 'type' => 'income', 'icon' => 'file-text', 'color' => '#10b981'],
            ['code' => '4-1430', 'name' => 'Bantuan Peningkatan Pendidikan', 'type' => 'income', 'icon' => 'heart-handshake', 'color' => '#047857'],
            ['code' => '4-2210', 'name' => 'Penerimaan Seragam/Jaket', 'type' => 'income', 'icon' => 'shirt', 'color' => '#10b981'],
            ['code' => '4-3210', 'name' => 'Bantuan Tidak Terikat', 'type' => 'income', 'icon' => 'sparkles', 'color' => '#059669'],
            ['code' => '4-3220', 'name' => 'Bantuan Terikat', 'type' => 'income', 'icon' => 'anchor', 'color' => '#047857'],
            ['code' => '4-9110', 'name' => 'Sewa Ruang', 'type' => 'income', 'icon' => 'home', 'color' => '#10b981'],
            ['code' => '4-9120', 'name' => 'Sewa Alat', 'type' => 'income', 'icon' => 'wrench', 'color' => '#059669'],
            ['code' => '4-9199', 'name' => 'Sewa lain-lain', 'type' => 'income', 'icon' => 'grid', 'color' => '#047857'],
            ['code' => '4-9210', 'name' => 'Jasa Laboratorium', 'type' => 'income', 'icon' => 'microscope', 'color' => '#10b981'],
            ['code' => '4-9300', 'name' => 'Pendapatan Kerjasama', 'type' => 'income', 'icon' => 'handshake', 'color' => '#059669'],
            ['code' => '4-9310', 'name' => 'Kontribusi Listrik', 'type' => 'income', 'icon' => 'zap', 'color' => '#047857'],

            // ========================================================
            // 5. BEBAN POKOK PENDIDIKAN / EXPENSE (Awalan 5)
            // ========================================================
            ['code' => '5-1100', 'name' => 'Beban Gaji dan Dosen', 'type' => 'expense', 'icon' => 'users', 'color' => '#ef4444'],
            ['code' => '5-1110', 'name' => 'Beban Gaji Dosen Tetap', 'type' => 'expense', 'icon' => 'user-check', 'color' => '#dc2626'],
            ['code' => '5-1130', 'name' => 'Honor Dosen & Asistn Tdk Tetap', 'type' => 'expense', 'icon' => 'user-plus', 'color' => '#ef4444'],
            ['code' => '5-1150', 'name' => 'Honor Sidang Skripsi/TA/PKL', 'type' => 'expense', 'icon' => 'award', 'color' => '#dc2626'],
            ['code' => '5-1170', 'name' => 'Beban Honor Ujian dan Koreksi', 'type' => 'expense', 'icon' => 'file-edit', 'color' => '#ef4444'],
            ['code' => '5-1270', 'name' => 'Beban Jamsostek', 'type' => 'expense', 'icon' => 'shield', 'color' => '#b91c1c'],
            ['code' => '5-1299', 'name' => 'Beban Tunjangan lainnya', 'type' => 'expense', 'icon' => 'gift', 'color' => '#dc2626'],
            ['code' => '5-1400', 'name' => 'Beban Pengmbangn Kepeg & Dosen', 'type' => 'expense', 'icon' => 'trending-up', 'color' => '#ef4444'],
            ['code' => '5-1410', 'name' => 'Beban Pendidikan', 'type' => 'expense', 'icon' => 'book', 'color' => '#dc2626'],
            ['code' => '5-1420', 'name' => 'Beban Pelatihan', 'type' => 'expense', 'icon' => 'presentation', 'color' => '#ef4444'],
            ['code' => '5-1430', 'name' => 'Beban Pengembangan Institusi', 'type' => 'expense', 'icon' => 'landmark', 'color' => '#b91c1c'],
            ['code' => '5-1440', 'name' => 'Beban Pengembangn Perpustakaan', 'type' => 'expense', 'icon' => 'library', 'color' => '#dc2626'],
            ['code' => '5-1510', 'name' => 'Honor Tatap Muka', 'type' => 'expense', 'icon' => 'message-square', 'color' => '#ef4444'],
            ['code' => '5-1520', 'name' => 'Beban Ujian', 'type' => 'expense', 'icon' => 'clipboard-list', 'color' => '#dc2626'],
            ['code' => '5-1530', 'name' => 'Beban Praktikum', 'type' => 'expense', 'icon' => 'flask-round', 'color' => '#ef4444'],
            ['code' => '5-1540', 'name' => 'Beban Transport Dosen/Asisten', 'type' => 'expense', 'icon' => 'car', 'color' => '#dc2626'],
            ['code' => '5-1550', 'name' => 'Beban Bimbingan Akademik', 'type' => 'expense', 'icon' => 'help-circle', 'color' => '#ef4444'],
            ['code' => '5-1560', 'name' => 'Beban Skripsi/Tugas Akhir', 'type' => 'expense', 'icon' => 'file-text', 'color' => '#b91c1c'],
            ['code' => '5-2110', 'name' => 'Kegiatan Management :', 'type' => 'expense', 'icon' => 'briefcase', 'color' => '#dc2626'],
            ['code' => '5-2115', 'name' => 'Akreditasi', 'type' => 'expense', 'icon' => 'check-circle-2', 'color' => '#ef4444'],
            ['code' => '5-2120', 'name' => 'Perpanjangan Ijin Operasional', 'type' => 'expense', 'icon' => 'file-signature', 'color' => '#dc2626'],
            ['code' => '5-2125', 'name' => 'Beban Hibah', 'type' => 'expense', 'icon' => 'hand-heart', 'color' => '#b91c1c'],
            ['code' => '5-2130', 'name' => 'Kurikulum, SAP, GBPP, Silabus', 'type' => 'expense', 'icon' => 'file-code', 'color' => '#dc2626'],
            ['code' => '5-2140', 'name' => 'Beban Perjalanan Dinas Dosen', 'type' => 'expense', 'icon' => 'plane', 'color' => '#ef4444'],
            ['code' => '5-2150', 'name' => 'Penelitian', 'type' => 'expense', 'icon' => 'search', 'color' => '#dc2626'],
            ['code' => '5-2155', 'name' => 'Pengabdian Kepada Masyarakat', 'type' => 'expense', 'icon' => 'heart', 'color' => '#b91c1c'],
            ['code' => '5-2160', 'name' => 'Seminar, Kuliah Umum ,Workshop', 'type' => 'expense', 'icon' => 'mic', 'color' => '#ef4444'],
            ['code' => '5-2165', 'name' => 'Rapat Dosen', 'type' => 'expense', 'icon' => 'users', 'color' => '#dc2626'],
            ['code' => '5-2170', 'name' => 'Kunj Lap / Studi Banding', 'type' => 'expense', 'icon' => 'map', 'color' => '#ef4444'],
            ['code' => '5-3210', 'name' => 'Beban Bea Siswa Umum', 'type' => 'expense', 'icon' => 'graduation-cap', 'color' => '#b91c1c'],
            ['code' => '5-4200', 'name' => 'Beban Laboratorium', 'type' => 'expense', 'icon' => 'flask-conical', 'color' => '#dc2626'],
            ['code' => '5-4210', 'name' => 'Bahan Praktek Laboratorium', 'type' => 'expense', 'icon' => 'test-tube', 'color' => '#ef4444'],
            ['code' => '5-4230', 'name' => 'Praktikum diluar Kampus', 'type' => 'expense', 'icon' => 'compass', 'color' => '#dc2626'],
            ['code' => '5-4299', 'name' => 'Beban Praktikum Lain-lain', 'type' => 'expense', 'icon' => 'wrench', 'color' => '#b91c1c'],
            ['code' => '5-4300', 'name' => 'Beban Mahasiswa', 'type' => 'expense', 'icon' => 'user', 'color' => '#dc2626'],
            ['code' => '5-4310', 'name' => 'Kegiatan Mahasiswa', 'type' => 'expense', 'icon' => 'target', 'color' => '#ef4444'],
            ['code' => '5-4320', 'name' => 'Kunjngn Lpngn, Studi Bndng Mhs', 'type' => 'expense', 'icon' => 'bus', 'color' => '#dc2626'],
            ['code' => '5-4330', 'name' => 'Bantuan / Bea Siswa', 'type' => 'expense', 'icon' => 'handshake', 'color' => '#b91c1c'],
            ['code' => '5-4340', 'name' => 'Wisuda', 'type' => 'expense', 'icon' => 'award', 'color' => '#dc2626'],
            ['code' => '5-4350', 'name' => 'Beban Mahasiswa Lain-lain', 'type' => 'expense', 'icon' => 'users', 'color' => '#ef4444'],
            ['code' => '5-4400', 'name' => 'Beban Seragam dan Buku Paket', 'type' => 'expense', 'icon' => 'shirt', 'color' => '#dc2626'],
            ['code' => '5-4410', 'name' => 'Beban Jaket Mahasiswa', 'type' => 'expense', 'icon' => 'shirt', 'color' => '#b91c1c'],

            // ========================================================
            // 6. BEBAN OPERASIONAL & UMUM / EXPENSE (Awalan 6)
            // ========================================================
            ['code' => '6-1100', 'name' => 'Beban Gaji Karyawan', 'type' => 'expense', 'icon' => 'users', 'color' => '#f43f5e'],
            ['code' => '6-1110', 'name' => 'Beban Gaji Karyawan Tetap', 'type' => 'expense', 'icon' => 'user-check', 'color' => '#e11d48'],
            ['code' => '6-1115', 'name' => 'Gaji Karyawan Tidak Tetap', 'type' => 'expense', 'icon' => 'user-plus', 'color' => '#be123c'],
            ['code' => '6-1120', 'name' => 'Beban Honorarium Pengurus', 'type' => 'expense', 'icon' => 'award', 'color' => '#e11d48'],
            ['code' => '6-1199', 'name' => 'Beban Gaji/Honor Lainnya', 'type' => 'expense', 'icon' => 'wallet', 'color' => '#f43f5e'],
            ['code' => '6-1210', 'name' => 'Beban THR Karyawan', 'type' => 'expense', 'icon' => 'gift', 'color' => '#e11d48'],
            ['code' => '6-1215', 'name' => 'Beban THR lainnya', 'type' => 'expense', 'icon' => 'gift', 'color' => '#be123c'],
            ['code' => '6-1220', 'name' => 'Beban Bonus Karywan', 'type' => 'expense', 'icon' => 'badge-percent', 'color' => '#e11d48'],
            ['code' => '6-1225', 'name' => 'Beban Bonus lainnya', 'type' => 'expense', 'icon' => 'badge-percent', 'color' => '#f43f5e'],
            ['code' => '6-1230', 'name' => 'Tunjangan Kesehatan Karyawan', 'type' => 'expense', 'icon' => 'heart-pulse', 'color' => '#e11d48'],
            ['code' => '6-1260', 'name' => 'Beban Jamsostek Karyawan', 'type' => 'expense', 'icon' => 'shield', 'color' => '#be123c'],
            ['code' => '6-1301', 'name' => 'Beban Listrik', 'type' => 'expense', 'icon' => 'zap', 'color' => '#f59e0b'],
            ['code' => '6-1302', 'name' => 'Beban Air', 'type' => 'expense', 'icon' => 'droplet', 'color' => '#0ea5e9'],
            ['code' => '6-1303', 'name' => 'Beban Telephone', 'type' => 'expense', 'icon' => 'phone', 'color' => '#6366f1'],
            ['code' => '6-1304', 'name' => 'Beban Photocopy', 'type' => 'expense', 'icon' => 'copy', 'color' => '#8b5cf6'],
            ['code' => '6-1305', 'name' => 'Beban Alat tulis Kantor', 'type' => 'expense', 'icon' => 'pen-tool', 'color' => '#ec4899'],
            ['code' => '6-1306', 'name' => 'Beban Percetakan', 'type' => 'expense', 'icon' => 'printer', 'color' => '#f43f5e'],
            ['code' => '6-1310', 'name' => 'Beban Sumbangan Sosial', 'type' => 'expense', 'icon' => 'heart', 'color' => '#e11d48'],
            ['code' => '6-1311', 'name' => 'Beban Cleaning Service', 'type' => 'expense', 'icon' => 'sparkles', 'color' => '#10b981'],
            ['code' => '6-1312', 'name' => 'Beban Transportasi Dinas', 'type' => 'expense', 'icon' => 'car', 'color' => '#06b6d4'],
            ['code' => '6-1313', 'name' => 'Beban Transportasi Lokal', 'type' => 'expense', 'icon' => 'navigation', 'color' => '#0284c7'],
            ['code' => '6-1314', 'name' => 'Beban Pemeliharaan Gedung', 'type' => 'expense', 'icon' => 'wrench', 'color' => '#ea580c'],
            ['code' => '6-1315', 'name' => 'Beban Pemeliharaan Inventaris', 'type' => 'expense', 'icon' => 'settings', 'color' => '#d97706'],
            ['code' => '6-1318', 'name' => 'Beban Rumah Tangga', 'type' => 'expense', 'icon' => 'home', 'color' => '#84cc16'],
            ['code' => '6-1319', 'name' => 'Beban Rapat', 'type' => 'expense', 'icon' => 'messages-square', 'color' => '#6366f1'],
            ['code' => '6-1320', 'name' => 'Beban Seminar', 'type' => 'expense', 'icon' => 'mic', 'color' => '#8b5cf6'],
            ['code' => '6-1410', 'name' => 'Beban Penyusutan Inv Kelas', 'type' => 'expense', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '6-1420', 'name' => 'Beban Penyusutan Inv Kantor', 'type' => 'expense', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '6-1430', 'name' => 'Beban Penyusutan Gedung', 'type' => 'expense', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '6-1440', 'name' => 'Beban Penyusutan Alat Labor', 'type' => 'expense', 'icon' => 'trending-down', 'color' => '#94a3b8'],
            ['code' => '6-1510', 'name' => 'Jamuan', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#f97316'],
            ['code' => '6-1520', 'name' => 'Jasa Pelayanan, Pemasaran dll', 'type' => 'expense', 'icon' => 'badge-dollar-sign', 'color' => '#ec4899'],
            ['code' => '6-1530', 'name' => 'Promosi, Iklan', 'type' => 'expense', 'icon' => 'megaphone', 'color' => '#f43f5e'],
            ['code' => '6-1610', 'name' => 'Seleksi Penerimaan Mhs Baru', 'type' => 'expense', 'icon' => 'user-check', 'color' => '#06b6d4'],
            ['code' => '6-1620', 'name' => 'Beban Panitia', 'type' => 'expense', 'icon' => 'users', 'color' => '#3b82f6'],
            ['code' => '6-1630', 'name' => 'Beban Promosi', 'type' => 'expense', 'icon' => 'megaphone', 'color' => '#f43f5e'],
            ['code' => '6-1640', 'name' => 'Beban Psikotes dan Tes Urine', 'type' => 'expense', 'icon' => 'clipboard-check', 'color' => '#8b5cf6'],
            ['code' => '6-1699', 'name' => 'Biaya Penerimaan Mhs Lainnya', 'type' => 'expense', 'icon' => 'folder-plus', 'color' => '#64748b'],
            ['code' => '6-1710', 'name' => 'Biaya Kursus Bahasa Inggris', 'type' => 'expense', 'icon' => 'languages', 'color' => '#10b981'],
            ['code' => '6-1720', 'name' => 'Biaya Kursus Komputer', 'type' => 'expense', 'icon' => 'monitor', 'color' => '#0ea5e9'],
            ['code' => '6-1730', 'name' => 'Biaya Sewa Ruang', 'type' => 'expense', 'icon' => 'building', 'color' => '#f59e0b'],

            // ========================================================
            // 8. PENDAPATAN LAIN-LAIN / OTHER INCOME (Awalan 8)
            // ========================================================
            ['code' => '8-1100', 'name' => 'Pendapatan Bunga Bank', 'type' => 'income', 'icon' => 'badge-percent', 'color' => '#10b981'],
            ['code' => '8-1110', 'name' => 'Pendapatan Jasa Giro', 'type' => 'income', 'icon' => 'landmark', 'color' => '#059669'],
            ['code' => '8-1120', 'name' => 'Pendapatan Bunga Tabungan', 'type' => 'income', 'icon' => 'piggy-bank', 'color' => '#047857'],
            ['code' => '8-1130', 'name' => 'Bunga Deposito', 'type' => 'income', 'icon' => 'coins', 'color' => '#10b981'],
            ['code' => '8-5000', 'name' => 'Pendapatan Lainnya', 'type' => 'income', 'icon' => 'banknote', 'color' => '#059669'],
            ['code' => '8-5110', 'name' => 'Laba Selisih Kurs', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#047857'],
            ['code' => '8-9999', 'name' => 'Pendapatan lain-lain', 'type' => 'income', 'icon' => 'wallet', 'color' => '#10b981'],

            // ========================================================
            // 9. BEBAN LAIN-LAIN / OTHER EXPENSE (Awalan 9)
            // ========================================================
            ['code' => '9-1110', 'name' => 'Beban Administrasi Bank', 'type' => 'expense', 'icon' => 'landmark', 'color' => '#ef4444'],
            ['code' => '9-1120', 'name' => 'Beban Pajak bunga Bank', 'type' => 'expense', 'icon' => 'receipt', 'color' => '#dc2626'],
            ['code' => '9-5530', 'name' => 'Sosial / Sumbangan', 'type' => 'expense', 'icon' => 'heart', 'color' => '#b91c1c'],
            ['code' => '9-5540', 'name' => 'Peghapusan Piutang Macet', 'type' => 'expense', 'icon' => 'file-x', 'color' => '#ef4444'],
            ['code' => '9-5550', 'name' => 'Rugi selisih Kurs', 'type' => 'expense', 'icon' => 'trending-down', 'color' => '#dc2626'],
            ['code' => '9-5599', 'name' => 'Beban Lain-lainnya', 'type' => 'expense', 'icon' => 'more-horizontal', 'color' => '#b91c1c'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color']
                ]
            );
        }
    }
}
