<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $activeAccount = session('active_account', 'petty_cash');

        // Fetch categories with transaction count and amount sum for the active account
        $query = Category::withCount(['transactions' => function ($query) use ($activeAccount) {
            $query->where('account', $activeAccount);
        }])->withSum(['transactions' => function ($query) use ($activeAccount) {
            $query->where('account', $activeAccount);
        }], 'amount');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $categories = $query->orderBy('type', 'asc')
            ->orderByRaw("CASE WHEN code IS NULL OR code = '' THEN 1 ELSE 0 END, code asc")
            ->orderBy('name', 'asc')
            ->get();

        return view('categories', compact('activeAccount', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:categories,code',
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:7', // Hex code
        ], [
            'code.required' => 'Kode kategori kampus wajib diisi.',
            'code.unique' => 'Kode kategori kampus ":input" sudah terdaftar. Kode harus unik.',
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'type.required' => 'Tipe kategori wajib dipilih.',
        ]);

        Category::create([
            'name' => trim($request->name),
            'code' => trim($request->code),
            'type' => $request->type,
            'icon' => $request->icon,
            'color' => $request->color,
        ]);

        return redirect()->back()->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:categories,code,' . $id,
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'type' => 'required|in:asset,liability,equity,income,expense',
            'icon' => 'required|string|max:50',
            'color' => 'required|string|max:7',
        ], [
            'code.required' => 'Kode kategori kampus wajib diisi.',
            'code.unique' => 'Kode kategori kampus ":input" sudah digunakan oleh kategori lain.',
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'type.required' => 'Tipe kategori wajib dipilih.',
        ]);

        $category->update([
            'name' => trim($request->name),
            'code' => trim($request->code),
            'type' => $request->type,
            'icon' => $request->icon,
            'color' => $request->color,
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Count how many transactions are using this category globally
        $transactionCount = $category->transactions()->count();
        
        if ($transactionCount > 0) {
            return redirect()->back()->with('error', 'Kategori ini tidak dapat dihapus karena sedang digunakan oleh ' . $transactionCount . ' transaksi.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];
        if ($extension === 'csv' || $extension === 'txt') {
            if (($handle = fopen($path, 'r')) !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($data) === 1 && str_contains($data[0], ';')) {
                        $data = explode(';', $data[0]);
                    }
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            try {
                $rows = \App\Helpers\XlsxParser::parse($path);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
            }
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', 'File kosong atau tidak dapat dibaca.');
        }

        // Detect column indices (find NP and Account)
        $headerRow = $rows[0];
        $npIndex = 0;
        $accountIndex = 1;
        $hasHeader = false;

        foreach ($headerRow as $index => $colVal) {
            $colValClean = strtolower(trim((string)$colVal));
            if ($colValClean === 'np' || str_contains($colValClean, 'nama') || str_contains($colValClean, 'perkiraan') || str_contains($colValClean, 'kategori')) {
                $npIndex = $index;
                $hasHeader = true;
            } elseif ($colValClean === 'account' || str_contains($colValClean, 'tipe') || str_contains($colValClean, 'type') || str_contains($colValClean, 'akun')) {
                $accountIndex = $index;
                $hasHeader = true;
            }
        }

        $startIdx = $hasHeader ? 1 : 0;
        $successCount = 0;

        $colors = ['#10b981', '#6366f1', '#8b5cf6', '#f43f5e', '#ef4444', '#f97316', '#ec4899', '#e11d48', '#14b8a6', '#06b6d4', '#3b82f6', '#d946ef', '#f59e0b', '#dc2626', '#84cc16'];

        $emojiMatches = [
            'spp' => '💻',
            'dosen' => '👥',
            'gaji' => '👥',
            'staf' => '👥',
            'maba' => '📝',
            'daftar' => '📝',
            'sewa' => '🏢',
            'gedung' => '🏢',
            'beasiswa' => '🎓',
            'mahasiswa' => '🎓',
            'kegiatan' => '🎯',
            'ukm' => '🎯',
            'pajak' => '🏛️',
            'retribusi' => '🏛️',
            'sarpras' => '🔧',
            'prasarana' => '🔧',
            'hibah' => '🤝',
            'kerja' => '🤝',
            'operasional' => '⚙️',
            'alat' => '⚙️',
            'kas' => '💵',
            'bank' => '🏦',
            'sampingan' => '💰',
        ];

        for ($i = $startIdx; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            $col1 = isset($row[$npIndex]) ? trim((string)$row[$npIndex]) : '';
            $col2 = isset($row[$accountIndex]) ? trim((string)$row[$accountIndex]) : '';

            if (empty($col1)) {
                continue;
            }

            $col2Lower = strtolower($col2);
            $typeKeywords = [
                'asset' => ['asset', 'aset', 'aktiva', 'harta'],
                'liability' => ['liability', 'kewajiban', 'hutang'],
                'equity' => ['equity', 'ekuitas', 'modal'],
                'income' => ['income', 'pemasukan', 'pendapatan'],
                'expense' => ['expense', 'pengeluaran', 'beban', 'biaya'],
            ];

            $matchedExplicitType = null;
            foreach ($typeKeywords as $tKey => $synonyms) {
                if (in_array($col2Lower, $synonyms)) {
                    $matchedExplicitType = $tKey;
                    break;
                }
            }
            
            if ($matchedExplicitType) {
                // If col2 is explicitly a type, col1 is the Name.
                $code = null;
                $categoryName = $col1;
                $type = $matchedExplicitType;
            } else {
                // If not, col1 is the NP (Code), col2 is the Account (Name).
                $code = $col1;
                $categoryName = empty($col2) ? $col1 : $col2;
                
                // Determine type based on first digit of NP (Code)
                $cleanCode = preg_replace('/[^0-9]/', '', $code);
                $firstDigit = (strlen($cleanCode) > 0) ? $cleanCode[0] : '';
                
                if ($firstDigit === '1') {
                    $type = 'asset';
                } elseif ($firstDigit === '2') {
                    $type = 'liability';
                } elseif ($firstDigit === '3') {
                    $type = 'equity';
                } elseif ($firstDigit === '4' || $firstDigit === '7' || $firstDigit === '8') {
                    $type = 'income';
                } elseif ($firstDigit === '5' || $firstDigit === '6' || $firstDigit === '9') {
                    $type = 'expense';
                } else {
                    // Fallback check on name for common keywords
                    $nameLower = strtolower($categoryName);
                    if (str_contains($nameLower, 'kas') || str_contains($nameLower, 'bank') || str_contains($nameLower, 'piutang') || str_contains($nameLower, 'aktiva') || str_contains($nameLower, 'gedung')) {
                        $type = 'asset';
                    } elseif (str_contains($nameLower, 'hutang') || str_contains($nameLower, 'kewajiban')) {
                        $type = 'liability';
                    } elseif (str_contains($nameLower, 'modal') || str_contains($nameLower, 'surplus') || str_contains($nameLower, 'ekuitas')) {
                        $type = 'equity';
                    } elseif (str_contains($nameLower, 'income') || str_contains($nameLower, 'pemasukan') || str_contains($nameLower, 'pendapatan') || str_contains($nameLower, 'spp') || str_contains($nameLower, 'bunga')) {
                        $type = 'income';
                    } else {
                        $type = 'expense';
                    }
                }
            }

            $nameLower = strtolower($categoryName);
            $icon = match($type) {
                'asset' => 'wallet',
                'liability' => 'credit-card',
                'equity' => 'scale',
                'income' => 'dollar-sign',
                default => 'file-text',
            };
            foreach ($emojiMatches as $keyword => $emoji) {
                if (str_contains($nameLower, $keyword)) {
                    $icon = $emoji;
                    break;
                }
            }

            $color = $colors[array_rand($colors)];

            if ($code) {
                Category::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $categoryName,
                        'type' => $type,
                        'icon' => $icon,
                        'color' => $color
                    ]
                );
            } else {
                Category::updateOrCreate(
                    ['name' => $categoryName],
                    [
                        'code' => null,
                        'type' => $type,
                        'icon' => $icon,
                        'color' => $color
                    ]
                );
            }

            $successCount++;
        }

        return redirect()->back()->with('success', "$successCount kategori berhasil di-import!");
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_kategori.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM agar Excel mengenali UTF-8 dengan benar
            fputs($file, "\xEF\xBB\xBF");
            
            // Header
            fputcsv($file, ['NP', 'Account'], ';');
            
            // Ambil daftar akun resmi dari database
            $cats = Category::orderBy('code', 'asc')->get(['code', 'name']);
            foreach ($cats as $c) {
                fputcsv($file, [$c->code, $c->name], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
