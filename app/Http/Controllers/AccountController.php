<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;

class AccountController extends Controller
{
    public function switchAccount($account)
    {
        if (in_array($account, ['petty_cash', 'bank_mandiri_1', 'bank_mandiri_2'])) {
            session(['active_account' => $account]);
        }
        return redirect()->back();
    }

    public function loadSampleData()
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Transaction::truncate();
        \App\Models\AccountingJob::truncate();

        // Re-seed categories to use real MYOB accounts list
        Category::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        $seeder = new \Database\Seeders\CategorySeeder();
        $seeder->run();
        $categories = Category::all();

        // Seed the exact jobs from the report (Ordered: TS -> TL -> TI -> Pusat)
        $jobs = [
            ['code' => '002', 'name' => 'S1 TS', 'description' => 'Program Studi S1 Teknik Sipil', 'status' => 'active'],
            ['code' => '004', 'name' => 'S1 TL', 'description' => 'Program Studi S1 Teknik Lingkungan', 'status' => 'active'],
            ['code' => '010', 'name' => 'S1 TI', 'description' => 'Program Studi S1 Teknik Informatika', 'status' => 'active'],
            ['code' => '009', 'name' => 'Pusat', 'description' => 'Bagian Administrasi & Operasional Pusat', 'status' => 'active'],
        ];

        foreach ($jobs as $jobData) {
            \App\Models\AccountingJob::create($jobData);
        }

        $allJobs = \App\Models\AccountingJob::all();

        // Seed cash assets with initial balances to match the Balance Sheet as of April 2026
        // Bank balance: Rp 1.368.715.505,92 (Mandiri I + II)
        // April bank net change: Rp 331.399.247,96
        // Bank initial balance = 1.368.715.505,92 - 331.399.247,96 = 1.037.316.257,96
        // Petty cash balance: Rp 9.086.188,03 (constant)

        $bantuanCategory = $categories->where('name', 'Bantuan Tidak Terikat')->first();
        $giroCategory = $categories->where('name', 'Pendapatan Jasa Giro')->first();

        // Initial Balance Petty Cash
        Transaction::create([
            'account' => 'petty_cash',
            'type' => 'income',
            'category_id' => $bantuanCategory->id,
            'amount' => 9086188.03,
            'description' => 'Saldo Awal Kas (Petty Cash) - STT Pekerjaan Umum',
            'date' => '2026-03-31',
        ]);

        // Initial Balance Bank
        Transaction::create([
            'account' => 'bank_mandiri_1',
            'type' => 'income',
            'category_id' => $bantuanCategory->id,
            'amount' => 1037316257.96,
            'description' => 'Saldo Awal Bank Mandiri Giro (I & II) - STT Pekerjaan Umum',
            'date' => '2026-03-31',
        ]);

        // Exact transactional data for April 2026 (Selected Period in MYOB report)
        $realData = [
            '002' => [ // S1 TS
                'income' => [
                    'SPP Mahasiswa Reguler' => 163034000.00,
                    'Uang Tugas Akhir/Skripsi' => 3710000.00,
                    'Uang Praktikum' => 27050000.00,
                    'Uang Wisuda' => 1200000.00,
                    'Uang Kegiatan Mahasiswa' => 900000.00,
                    'Uang Ijazah' => 3200000.00,
                    'Bantuan Peningkatan Pendidikan' => 1372000.00,
                ],
                'expense' => [
                    'Beban Gaji Dosen Tetap' => 32625000.00,
                    'Honor Dosen & Asistn Tdk Tetap' => 8875000.00,
                    'Beban Honor Ujian dan Koreksi' => 4055500.00,
                    'Beban Telephone' => 900000.00,
                ]
            ],
            '004' => [ // S1 TL
                'income' => [
                    'SPP Mahasiswa Reguler' => 408172000.00,
                    'Uang Formulir' => 250000.00,
                    'Bantuan Peningkatan Pendidikan' => 363000.00,
                ],
                'expense' => [
                    'Beban Gaji Dosen Tetap' => 39033750.00,
                    'Honor Dosen & Asistn Tdk Tetap' => 4187000.00,
                    'Beban Honor Ujian dan Koreksi' => 4789500.00,
                    'Beban Transport Dosen/Asisten' => 6250000.00,
                    'Beban Telephone' => 900000.00,
                ]
            ],
            '009' => [ // Pusat
                'income' => [
                    'Kontribusi Listrik' => 250000.00,
                    'Pendapatan Jasa Giro' => 556488.70,
                ],
                'expense' => [
                    'Beban Pelatihan' => 1237400.00,
                    'Pengabdian Kepada Masyarakat' => 668500.00,
                    'Beban Gaji Karyawan Tetap' => 125156250.00,
                    'Beban Jamsostek Karyawan' => 5472774.00,
                    'Beban Listrik' => 10703832.00,
                    'Beban Air' => 364195.00,
                    'Beban Telephone' => 8708742.00,
                    'Beban Alat tulis Kantor' => 236000.00,
                    'Beban Transportasi Lokal' => 136800.00,
                    'Beban Pemeliharaan Gedung' => 1100000.00,
                    'Beban Pemeliharaan Inventaris' => 200000.00,
                    'Beban Rumah Tangga' => 4855150.00,
                    'Beban Rapat' => 111500.00,
                    'Seleksi Penerimaan Mhs Baru' => 675000.00,
                    'Beban Administrasi Bank' => 226297.74,
                    'Beban Lain-lainnya' => 465850.00,
                ]
            ],
            '010' => [ // S1 TI
                'income' => [
                    'SPP Mahasiswa Reguler' => 15529000.00,
                    'Uang Kegiatan Mahasiswa' => 100000.00,
                    'Uang Formulir' => 250000.00,
                    'Bantuan Peningkatan Pendidikan' => 184800.00,
                ],
                'expense' => [
                    'Beban Gaji Dosen Tetap' => 25885000.00,
                    'Honor Dosen & Asistn Tdk Tetap' => 4550000.00,
                    'Beban Honor Ujian dan Koreksi' => 1678000.00,
                    'Beban Telephone' => 675000.00,
                ]
            ],
        ];

        foreach ($realData as $jobCode => $dataTypes) {
            $job = $allJobs->where('code', $jobCode)->first();
            if (!$job) continue;

            foreach ($dataTypes as $type => $catAmounts) {
                foreach ($catAmounts as $catName => $amount) {
                    $cat = $categories->where('name', $catName)->where('type', $type)->first();
                    if (!$cat) continue;

                    // Generate a realistic description
                    $desc = "";
                    if ($type === 'income') {
                        $desc = "Penerimaan " . $catName . " - " . $job->name;
                    } else {
                        $desc = "Pembayaran " . $catName . " - " . $job->name;
                    }

                    // Spread transactions across April 2026
                    $day = rand(1, 28);
                    $date = "2026-04-" . str_pad($day, 2, '0', STR_PAD_LEFT);

                    Transaction::create([
                        'account' => 'bank_mandiri_1',
                        'type' => $type,
                        'category_id' => $cat->id,
                        'job_id' => $job->id,
                        'amount' => $amount,
                        'description' => $desc,
                        'date' => $date,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Data contoh asli STT Pekerjaan Umum berhasil dimuat!');
    }

    public function resetData()
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Transaction::truncate();
        \App\Models\JournalEntry::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Hapus file attachment jika ada
        $files = \Illuminate\Support\Facades\Storage::disk('public')->allFiles('attachments');
        if (!empty($files)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($files);
        }

        return redirect()->back()->with('success', 'Semua data transaksi dan jurnal berhasil dibersihkan! Master Akun COA & Prodi tetap siap digunakan.');
    }
}
