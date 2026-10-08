@extends('layouts.app')

@section('title', 'Bank Register (Buku Kas & Bank) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
        <h2 style="margin:0">Bank Register</h2>
        <span class="account-badge" data-account="{{ $activeAccount }}" style="font-size:0.85rem; padding:4px 10px; border-radius:6px; font-weight:600">
          <x-dynamic-component :component="'lucide-' . $currentAccountInfo['icon']" /> [{{ $currentAccountInfo['code'] }}] {{ $currentAccountInfo['name'] }}
        </span>
      </div>
      <p style="margin:0; color:var(--text-secondary)">Buku register mutasi kas & bank dengan saldo berjalan (running balance) ala MYOB.</p>
    </div>
    <div class="flex gap-1" style="flex-wrap:wrap">
      <a href="{{ route('transactions.import-template') }}" class="btn btn-secondary" style="text-decoration:none">
        <x-lucide-file-down /> Unduh Template
      </a>
      <button class="btn btn-secondary" id="btn-import-tx">
        <x-lucide-upload /> Import CSV
      </button>
      <a href="{{ route('transactions.export-csv', request()->query()) }}" class="btn btn-secondary" style="text-decoration:none">
        <x-lucide-download /> Export CSV
      </a>
      <button class="btn btn-secondary" id="btn-transfer-header" style="background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.3); color:#4338ca; font-weight:600">
        <x-lucide-arrow-left-right /> Transfer Kas
      </button>
      <button class="btn btn-primary" id="btn-add-tx">
        <x-lucide-plus /> Transaksi Baru
      </button>
    </div>
  </div>

  <!-- MYOB Bank Register Account & Balance Card -->
  <div class="card mb-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(99, 102, 241, 0.2); padding: 18px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      
      <!-- Account Info & Switcher -->
      <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); display:flex; align-items:center; justify-content:center; color:#818cf8;">
          <x-dynamic-component :component="'lucide-' . $currentAccountInfo['icon']" style="width:24px; height:24px;" />
        </div>
        <div>
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:#94a3b8; font-weight:600">Buku Akun (Account Register)</div>
          <div style="font-size:1.15rem; font-weight:700; color:#f8fafc; display:flex; align-items:center; gap:8px;">
            <span>[{{ $currentAccountInfo['code'] }}] {{ $currentAccountInfo['name'] }}</span>
          </div>
          <div style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap;">
            <a href="{{ route('switch-account', 'petty_cash') }}" 
              class="account-pill-btn {{ $activeAccount === 'petty_cash' ? 'active-petty' : '' }}" 
              style="padding:4px 12px; font-size:0.78rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; border-radius:6px; font-weight:600; {{ $activeAccount === 'petty_cash' ? 'background:linear-gradient(135deg, #d97706, #b45309); color:#ffffff; border:1px solid #f59e0b; box-shadow:0 2px 10px rgba(245, 158, 11, 0.4);' : 'background:rgba(255,255,255,0.15); color:#ffffff; border:1px solid rgba(255,255,255,0.32);' }}">
              <x-lucide-wallet style="width:14px; height:14px; color: {{ $activeAccount === 'petty_cash' ? '#ffffff' : '#fbbf24' }};" /> 
              <span>Kas Kecil [1-1110]</span>
            </a>
            <a href="{{ route('switch-account', 'bank_mandiri_1') }}" 
              class="account-pill-btn {{ $activeAccount === 'bank_mandiri_1' ? 'active-mandiri1' : '' }}" 
              style="padding:4px 12px; font-size:0.78rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; border-radius:6px; font-weight:600; {{ $activeAccount === 'bank_mandiri_1' ? 'background:linear-gradient(135deg, #2563eb, #1d4ed8); color:#ffffff; border:1px solid #60a5fa; box-shadow:0 2px 10px rgba(37, 99, 235, 0.4);' : 'background:rgba(255,255,255,0.15); color:#ffffff; border:1px solid rgba(255,255,255,0.32);' }}">
              <x-lucide-landmark style="width:14px; height:14px; color: {{ $activeAccount === 'bank_mandiri_1' ? '#ffffff' : '#60a5fa' }};" /> 
              <span>Mandiri 1 [1-1121]</span>
            </a>
            <a href="{{ route('switch-account', 'bank_mandiri_2') }}" 
              class="account-pill-btn {{ $activeAccount === 'bank_mandiri_2' ? 'active-mandiri2' : '' }}" 
              style="padding:4px 12px; font-size:0.78rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; border-radius:6px; font-weight:600; {{ $activeAccount === 'bank_mandiri_2' ? 'background:linear-gradient(135deg, #7c3aed, #6d28d9); color:#ffffff; border:1px solid #a78bfa; box-shadow:0 2px 10px rgba(124, 58, 237, 0.4);' : 'background:rgba(255,255,255,0.15); color:#ffffff; border:1px solid rgba(255,255,255,0.32);' }}">
              <x-lucide-building-2 style="width:14px; height:14px; color: {{ $activeAccount === 'bank_mandiri_2' ? '#ffffff' : '#c084fc' }};" /> 
              <span>Mandiri 2 [1-1122]</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Balance Metrics ala MYOB -->
      <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="text-align:right; border-right:1px solid rgba(255,255,255,0.1); padding-right:16px;">
          <div style="font-size:0.75rem; color:#4ade80; font-weight:600;">Total Masuk (Filter)</div>
          <div style="font-size:1rem; font-weight:700; color:#4ade80;">+Rp {{ number_format($filteredIncome, 0, ',', '.') }}</div>
        </div>
        <div style="text-align:right; border-right:1px solid rgba(255,255,255,0.1); padding-right:16px;">
          <div style="font-size:0.75rem; color:#f87171; font-weight:600;">Total Keluar (Filter)</div>
          <div style="font-size:1rem; font-weight:700; color:#f87171;">-Rp {{ number_format($filteredExpense, 0, ',', '.') }}</div>
        </div>
        <div style="text-align:right; background:rgba(99,102,241,0.1); padding:8px 16px; border-radius:10px; border:1px solid rgba(99,102,241,0.25);">
          <div style="font-size:0.75rem; color:#93c5fd; font-weight:600; text-transform:uppercase;">Saldo Terkini (Current Balance)</div>
          <div style="font-size:1.35rem; font-weight:800; color:#38bdf8; font-family: monospace;">
            Rp {{ number_format($currentAccountBalance, 0, ',', '.') }}
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Filter Bar -->
  <form action="{{ route('transactions.index') }}" method="GET" id="filter-form">
    <div class="filter-bar">
      <div class="search-input-wrapper">
        <span class="search-icon"><x-lucide-search style="width:16px; height:16px; color:var(--text-muted);" /></span>
        <input type="text" class="form-input" name="search" id="tx-search" 
          placeholder="Cari transaksi..." value="{{ request('search') }}" />
      </div>
      <select class="form-select" name="month" id="tx-filter-month">
        <option value="all" {{ request('month', 'all') === 'all' ? 'selected' : '' }}>Semua Bulan</option>
        @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $mNum => $mName)
          <option value="{{ $mNum }}" {{ request('month') == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
        @endforeach
      </select>
      <select class="form-select" name="type" id="tx-filter-type">
        <option value="">Semua Tipe</option>
        <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
        <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
      </select>
      <select class="form-select" name="category_id" id="tx-filter-category">
        <option value="">Semua Kategori (COA)</option>
        @foreach($categories->sortBy('code') as $c)
          <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
            @if($c->code)[{{ $c->code }}] @endif{{ $c->name }}
          </option>
        @endforeach
      </select>
      <select class="form-select" name="job_id" id="tx-filter-job">
        <option value="">Semua Unit (Proyek)</option>
        @foreach($jobs as $j)
          <option value="{{ $j->id }}" {{ request('job_id') == $j->id ? 'selected' : '' }}>
            [{{ $j->code }}] {{ $j->name }}
          </option>
        @endforeach
      </select>
      <input type="date" class="form-input" name="start_date" id="tx-filter-from" value="{{ request('start_date') }}" 
        style="width:auto;min-width:140px" placeholder="Mulai" />
      <input type="date" class="form-input" name="end_date" id="tx-filter-to" value="{{ request('end_date') }}" 
        style="width:auto;min-width:140px" placeholder="Selesai" />
    </div>
  </form>


  <!-- Quick Stats (Filtered Totals) -->
  <div class="flex gap-2 mb-2" style="flex-wrap:wrap">
    <div class="badge badge-income" style="font-size:0.85rem; padding:8px 12px">
      Pemasukan (Filter): +Rp {{ number_format($filteredIncome, 0, ',', '.') }}
    </div>
    <div class="badge badge-expense" style="font-size:0.85rem; padding:8px 12px">
      Pengeluaran (Filter): -Rp {{ number_format($filteredExpense, 0, ',', '.') }}
    </div>
    <div class="badge" style="font-size:0.85rem; padding:8px 12px; background:rgba(255,255,255,0.05); color:#e2e8f0">
      Netto (Filter): <span class="{{ $filteredNet >= 0 ? 'text-income' : 'text-expense' }}" style="font-weight:700">
        {{ $filteredNet >= 0 ? '+' : '' }}Rp {{ number_format($filteredNet, 0, ',', '.') }}
      </span>
    </div>
  </div>

  <!-- Transactions Table -->
  <div class="card">
    <div class="table-container">
      <table class="data-table" id="tx-table">
        <thead>
          <tr>
            <th style="min-width:105px">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'direction' => ($sortColumn === 'date' && $sortDirection === 'desc') ? 'asc' : 'desc']) }}" style="color:inherit; text-decoration:none">
                Tanggal {!! $sortColumn === 'date' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' !!}
              </a>
            </th>
            <th style="min-width:120px">No. Bukti / ID#</th>
            <th style="min-width:140px">Akun / Kategori (COA)</th>
            <th style="min-width:110px">Proyek (Job)</th>
            <th>Uraian (Memo)</th>
            <th style="text-align:right; min-width:130px; color:#4ade80">Penerimaan (Deposit)</th>
            <th style="text-align:right; min-width:130px; color:#f87171">Pengeluaran (Withdrawal)</th>
            <th style="text-align:right; min-width:145px; color:#38bdf8">
              <a href="{{ request()->fullUrlWithQuery(['sort' => 'amount', 'direction' => ($sortColumn === 'amount' && $sortDirection === 'desc') ? 'asc' : 'desc']) }}" style="color:inherit; text-decoration:none">
                Saldo Berjalan {!! $sortColumn === 'amount' ? ($sortDirection === 'asc' ? '↑' : '↓') : '↕' !!}
              </a>
            </th>
            <th style="width:115px; text-align:center">Aksi</th>
          </tr>
        </thead>
        <tbody id="tx-tbody">
          @if($transactions->isEmpty())
            <tr>
              <td colspan="9" style="text-align:center; padding: 40px 0;">
                <div class="empty-state-title" style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 5px;">Belum ada data transaksi</div>
                <div class="empty-state-desc" style="font-size: 0.85rem; color: var(--text-muted);">Tidak ada transaksi yang cocok dengan filter aktif.</div>
              </td>
            </tr>
          @else
            @foreach($transactions as $tx)
              <tr>
                <td style="white-space:nowrap; font-size:0.85rem">{{ $tx->date->format('d M Y') }}</td>
                <td>
                  <span style="font-family: monospace; font-size: 0.85rem; color: #a5b4fc; font-weight:600;">{{ $tx->voucher_number ?? '—' }}</span>
                  @if($tx->related_transaction_id)
                    <span class="badge" style="font-size:0.65rem; background:rgba(99,102,241,0.2); color:#a5b4fc; border:1px solid rgba(99,102,241,0.3); padding:1px 5px; margin-left:4px;" title="Transfer Kas/Bank">⇄ Transfer</span>
                  @endif
                </td>
                <td>
                  @if(str_contains(strtolower($tx->description), 'saldo awal'))
                    <span style="color: var(--text-muted); font-size:0.85rem;">[1-1100] Saldo Awal</span>
                  @elseif($tx->category)
                    <div style="display:flex; align-items:center; gap:6px;">
                      @if($tx->category->code)
                        <span class="badge" style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #a5b4fc; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.28); padding: 1px 5px; border-radius: 4px;">
                          {{ $tx->category->code }}
                        </span>
                      @endif
                      <span style="font-size:0.85rem; color:var(--text-secondary)">{{ $tx->category->name }}</span>
                    </div>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>
                <td>
                  @if($tx->job)
                    <span style="font-weight:700; color:var(--accent-primary); font-size:0.8rem">[{{ $tx->job->code }}]</span>
                    <span style="font-size:0.75rem; color:var(--text-secondary)">{{ $tx->job->name }}</span>
                  @else
                    <span style="color:var(--text-muted)">—</span>
                  @endif
                </td>
                <td>
                  <div style="font-size:0.88rem; color:#f1f5f9;">{{ $tx->description ?? '—' }}</div>
                  @if($tx->ket || $tx->paraf)
                    <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">
                      @if($tx->paraf)<span>Paraf: {{ $tx->paraf }}</span>@endif
                      @if($tx->paraf && $tx->ket) • @endif
                      @if($tx->ket)<span>Ket: {{ $tx->ket }}</span>@endif
                    </div>
                  @endif
                </td>
                <!-- Penerimaan (Deposit) -->
                <td style="text-align:right">
                  @if($tx->type === 'income')
                    <span class="text-income font-bold" style="font-family:monospace; font-size:0.9rem">
                      +Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </span>
                  @else
                    <span style="color:rgba(255,255,255,0.2)">—</span>
                  @endif
                </td>
                <!-- Pengeluaran (Withdrawal) -->
                <td style="text-align:right">
                  @if($tx->type === 'expense')
                    <span class="text-expense font-bold" style="font-family:monospace; font-size:0.9rem">
                      -Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </span>
                  @else
                    <span style="color:rgba(255,255,255,0.2)">—</span>
                  @endif
                </td>
                <!-- Saldo Berjalan (Running Balance) -->
                <td style="text-align:right">
                  <span style="font-family: monospace; font-weight: 700; font-size:0.9rem; color: {{ $tx->running_balance < 0 ? '#ef4444' : '#38bdf8' }};">
                    Rp {{ number_format($tx->running_balance, 0, ',', '.') }}
                  </span>
                </td>
                <td style="text-align:center">
                  <div style="display:flex; gap:4px; justify-content:center">
                    <button class="btn btn-secondary btn-sm btn-print-voucher" 
                      data-id="{{ $tx->id }}"
                      data-voucher="{{ $tx->voucher_number }}"
                      data-amount="{{ (float) $tx->amount }}"
                      data-description="{{ $tx->description }}"
                      data-date="{{ $tx->date->format('Y-m-d') }}"
                      data-type="{{ $tx->type }}"
                      data-ket="{{ $tx->ket }}"
                      data-np="{{ $tx->category ? str_replace(['-', ' '], '', $tx->category->code) : '' }}"
                      data-prodi="{{ $tx->job ? $tx->job->name : 'Pusat' }}"
                      style="padding: 4px 6px"
                      title="Cetak Voucer Bukti">
                      <x-lucide-printer style="width:14px; height:14px;" />
                    </button>
                    @if($tx->attachment)
                      <a href="{{ Storage::url($tx->attachment) }}" target="_blank" class="btn btn-secondary btn-sm" style="padding: 4px 6px" title="Lihat Lampiran Dokumen">
                        <x-lucide-paperclip style="width:14px; height:14px;" />
                      </a>
                    @endif
                    <button class="btn btn-secondary btn-sm btn-edit-tx" 
                      data-id="{{ $tx->id }}"
                      data-account="{{ $tx->account }}"
                      data-type="{{ $tx->type }}"
                      data-category-id="{{ $tx->category_id }}"
                      data-job-id="{{ $tx->job_id }}"
                      data-date="{{ $tx->date->format('Y-m-d') }}"
                      data-amount="{{ (float) $tx->amount }}"
                      data-description="{{ $tx->description }}"
                      data-ket="{{ $tx->ket }}"
                      data-voucher="{{ $tx->voucher_number }}"
                      style="padding: 4px 6px"
                      title="Edit Transaksi">
                      <x-lucide-edit-3 style="width:14px; height:14px;" />
                    </button>
                    @if(str_starts_with($tx->description ?? '', '[PEMBATALAN]'))
                      <span class="badge" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 0.7rem; padding: 3px 6px;" title="Transaksi ini adalah jurnal pembalik koreksi">
                        Pembalik
                      </span>
                    @else
                      <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST" onsubmit="return confirm('Batalkan transaksi ini dengan Jurnal Pembalik? Transaksi asal tetap tersimpan untuk audit trail dan sistem akan mencatat transaksi pembalik otomatis.')" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="padding: 4px 6px; background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25);" title="Batalkan Transaksi (Jurnal Pembalik)">
                          <x-lucide-rotate-ccw style="width:14px; height:14px;" />
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>

    <!-- Custom Pagination -->
    @if($transactions->hasPages())
      <div class="pagination">
        @if ($transactions->onFirstPage())
          <span class="pagination-btn disabled"><x-lucide-chevron-left style="width:14px; height:14px;" /></span>
        @else
          <a href="{{ $transactions->previousPageUrl() }}" class="pagination-btn"><x-lucide-chevron-left style="width:14px; height:14px;" /></a>
        @endif

        @for ($i = 1; $i <= $transactions->lastPage(); $i++)
          @if ($i == $transactions->currentPage())
            <span class="pagination-btn active">{{ $i }}</span>
          @else
            <a href="{{ $transactions->url($i) }}" class="pagination-btn">{{ $i }}</a>
          @endif
        @endfor

        @if ($transactions->hasMorePages())
          <a href="{{ $transactions->nextPageUrl() }}" class="pagination-btn"><x-lucide-chevron-right style="width:14px; height:14px;" /></a>
        @else
          <span class="pagination-btn disabled"><x-lucide-chevron-right style="width:14px; height:14px;" /></span>
        @endif

        <span class="pagination-info">{{ $transactions->total() }} transaksi</span>
      </div>
    @endif
  </div>
</div>

@include('partials.transaction-modal')

<!-- Import Transactions Modal Overlay -->
<div class="modal-overlay" id="import-tx-modal-overlay">
  <div class="modal" style="max-width:500px">
    <form action="{{ route('transactions.import') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h3>Import Buku Kas Harian</h3>
        <button type="button" class="modal-close" id="btn-import-close-tx" aria-label="Tutup modal">
          <x-lucide-x />
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Pilih Berkas Excel/CSV</label>
          <input type="file" name="file" class="form-input" accept=".xlsx,.xls,.csv,.txt" required />
          <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; display: block;">
            Format yang didukung: .xlsx, .xls, .csv, .txt.
            Anda dapat mengunduh <a href="{{ route('transactions.import-template') }}" style="color: #818cf8; text-decoration: underline; font-weight: 500;">Template (CSV)</a> sebagai acuan.
          </span>
        </div>
        <div style="background: rgba(129, 140, 248, 0.05); padding: 12px; border-radius: 6px; border: 1px solid rgba(129, 140, 248, 0.1); margin-top: 12px;">
          <span style="font-weight: 600; display: inline-flex; align-items:center; gap:6px; margin-bottom: 8px; color: #818cf8; font-size: 0.85rem;">
            <x-lucide-info style="width:14px; height:14px;" /> Panduan Import:
          </span>
          <ul style="font-size: 0.8rem; color: var(--text-secondary); padding-left: 16px; list-style-type: disc; line-height: 1.4;">
            <li>Sistem mendeteksi kolom: <strong>Tanggal, Nomor Bukti, NP, Uraian, Debet, Kredit, Paraf, Ket</strong>.</li>
            <li>Jika baris NP berisi kode baru, kategori akan dibuat otomatis.</li>
            <li>Kolom Jumlah diambil dari nilai Debet (Pemasukan) atau Kredit (Pengeluaran).</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-import-cancel-tx">Batal</button>
        <button type="submit" class="btn btn-primary">Mulai Import</button>
      </div>
    </form>
  </div>
</div>

<!-- Print Voucher Modal Overlay -->
<div class="modal-overlay" id="voucher-modal-overlay">
  <div class="modal" style="max-width: 720px">
    <form action="{{ route('transactions.index') }}" method="GET" id="voucher-form" target="_blank">
      <input type="hidden" name="print_voucher" value="1" />
      <input type="hidden" name="transaction_id" id="voucher-transaction-id" value="" />
      <input type="hidden" name="transaction_type" id="voucher-transaction-type" value="expense" />
      
      <div class="modal-header">
        <h3 id="voucher-modal-title">Cetak Bukti Pengeluaran</h3>
        <button type="button" class="modal-close" id="btn-voucher-close" aria-label="Tutup modal">
          <x-lucide-x />
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
          <div class="form-group">
            <label class="form-label">Nomor Bukti</label>
            <input type="text" class="form-input" name="voucher_number" id="voucher-number" placeholder="Contoh: KT.26.08 018" required />
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal</label>
            <input type="date" class="form-input" name="date" id="voucher-date" required />
          </div>
          <div class="form-group">
            <label class="form-label">Prodi / Unit Kerja</label>
            <input type="text" class="form-input" name="prodi" id="voucher-prodi" value="Pusat" placeholder="Contoh: Pusat / S1 TS" required />
          </div>
        </div>

        <!-- Multi-Item Inputs -->
        <div class="form-group">
          <div class="flex-between mb-1" style="align-items: center">
            <label class="form-label" style="margin-bottom:0">Rincian Pos Transaksi</label>
            <button type="button" class="btn btn-secondary btn-sm" id="btn-add-voucher-item" style="padding: 3px 10px; font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
              <x-lucide-plus style="width:13px; height:13px;" /> Tambah Baris
            </button>
          </div>
          <div id="voucher-items-container" style="display:flex; flex-direction:column; gap:8px">
            <!-- Dynamic rows will be inserted here -->
          </div>
        </div>

        <!-- Calculated Summary -->
        <div style="background: rgba(255,255,255,0.02); padding: 12px; border-radius: 6px; margin-top:12px; border:1px solid var(--border-primary)">
          <div class="flex-between mb-1" style="font-weight:700">
            <span>Total Jumlah:</span>
            <span id="voucher-total-display" style="color:var(--color-expense)" class="font-mono-num">Rp 0,00</span>
          </div>
          <div style="font-size:0.8rem; color:var(--text-secondary); line-height: 1.4">
            <strong>Terbilang:</strong> <span id="voucher-terbilang-display" style="font-style:italic"># Nol Rupiah #</span>
          </div>
        </div>

        <!-- Signature Section -->
        <div style="margin-top: 16px;">
          <h4 style="font-size: 0.9rem; margin-bottom: 8px; font-weight:600; color: var(--text-primary);">Matriks Tanda Tangan</h4>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
            <div class="form-group">
              <label class="form-label" style="font-size:0.8rem">Menyetujui</label>
              <input type="text" class="form-input" name="approver" value="Ir. Rina Agustin Indriani, MURP" style="font-size:0.85rem" />
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:0.8rem">Memeriksa</label>
              <input type="text" class="form-input" name="verifier" value="Noor'aini Kartikarini, S.M" style="font-size:0.85rem" />
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:0.8rem">Bendahara</label>
              <input type="text" class="form-input" name="payer" value="Irma Yaniarti" style="font-size:0.85rem" />
            </div>
            <div class="form-group">
              <label class="form-label" style="font-size:0.8rem">Yang Menerima</label>
              <input type="text" class="form-input" name="recipient" id="voucher-recipient" value="Titis Marsela" style="font-size:0.85rem" required />
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-voucher-cancel">Batal</button>
        <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-printer /> Buka Layout Cetak (A4)
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // --- Auto-Submit Filters ---
    const filterForm = document.getElementById('filter-form');
    document.querySelectorAll('#tx-filter-month, #tx-filter-type, #tx-filter-category, #tx-filter-job, #tx-filter-from, #tx-filter-to').forEach(el => {
      el.addEventListener('change', () => {
        filterForm.submit();
      });
    });

    // Debounce search input
    let searchTimeout;
    document.getElementById('tx-search').addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        filterForm.submit();
      }, 500);
    });


    // --- Add/Edit Modal Logic ---
    const overlay = document.getElementById('tx-modal-overlay');
    const form = document.getElementById('tx-modal-form');
    const title = document.getElementById('tx-modal-title');
    const methodInput = document.getElementById('tx-form-method');
    
    const typeInput = document.getElementById('modal-type');
    const btnIncome = document.getElementById('btn-select-income');
    const btnExpense = document.getElementById('btn-select-expense');
    const btnTransfer = document.getElementById('btn-select-transfer');
    
    const dateInput = document.getElementById('modal-date');
    const amountInput = document.getElementById('modal-amount');
    const descInput = document.getElementById('modal-desc');
    const saveBtn = document.getElementById('btn-modal-save');

    const modalAccount = document.getElementById('modal-account');
    const modalAccountTip = document.getElementById('modal-account-tip');
    const modalAccountWarning = document.getElementById('modal-account-warning');

    function updateAccountUi() {
      const selected = modalAccount.value;
      const amount = parseFloat(amountInput.value) || 0;
      
      if (selected === 'petty_cash') {
        modalAccountTip.textContent = "Dikhususkan untuk pendanaan operasional ringan dan rutin harian (di bawah Rp 5.000.000).";
        if (amount >= 5000000) {
          modalAccountWarning.style.display = 'block';
        } else {
          modalAccountWarning.style.display = 'none';
        }
      } else {
        modalAccountTip.textContent = "Dikhususkan untuk perputaran dana berskala besar (pencairan cek, pembayaran vendor, gaji).";
        modalAccountWarning.style.display = 'none';
      }
    }

    modalAccount.addEventListener('change', updateAccountUi);
    amountInput.addEventListener('input', updateAccountUi);

    function setFormType(type) {
      typeInput.value = type;
      btnIncome.classList.toggle('active', type === 'income');
      btnExpense.classList.toggle('active', type === 'expense');
      if (btnTransfer) btnTransfer.classList.toggle('active', type === 'transfer');

      if (window.onTransactionTypeChange) {
        window.onTransactionTypeChange(type);
      }
    }

    btnIncome.addEventListener('click', () => setFormType('income'));
    btnExpense.addEventListener('click', () => setFormType('expense'));
    if (btnTransfer) btnTransfer.addEventListener('click', () => setFormType('transfer'));

    // Open Modal for Create
    document.getElementById('btn-add-tx').addEventListener('click', () => {
      form.action = "{{ route('transactions.store') }}";
      methodInput.value = "POST";
      title.innerHTML = `Tambah Transaksi Baru`;
      
      modalAccount.value = "{{ $activeAccount }}";
      amountInput.value = '';
      descInput.value = '';
      document.getElementById('modal-ket').value = '';
      dateInput.value = "{{ date('Y-m-d') }}";
      document.getElementById('modal-job').value = '';
      document.getElementById('modal-voucher').value = '';
      
      setFormType('expense');
      if (window.selectModalCategoryDefault) {
        window.selectModalCategoryDefault('expense');
      }
      if (window.resetExtraModalItems) {
        window.resetExtraModalItems();
      }
      if (window.setMultiItemHeaderVisible) {
        window.setMultiItemHeaderVisible(true);
      }
      updateAccountUi();
      saveBtn.textContent = 'Tambah Transaksi';
      overlay.classList.add('active');
    });

    const btnTransferHeader = document.getElementById('btn-transfer-header');
    if (btnTransferHeader) {
      btnTransferHeader.addEventListener('click', () => {
        document.getElementById('btn-add-tx').click();
        if (btnTransfer) {
          btnTransfer.click();
        }
      });
    }

    // Open Modal for Edit
    document.querySelectorAll('.btn-edit-tx').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const account = btn.dataset.account;
        const type = btn.dataset.type;
        const catId = btn.dataset.categoryId;
        const jobId = btn.dataset.jobId;
        const date = btn.dataset.date;
        const amount = btn.dataset.amount;
        const desc = btn.dataset.description;
        const voucher = btn.dataset.voucher;
        const ket = btn.dataset.ket;

        form.action = "{{ url('/transactions') }}/" + id;
        methodInput.value = "PUT";
        title.innerHTML = `Edit Transaksi`;
        
        modalAccount.value = account;
        amountInput.value = amount;
        descInput.value = desc;
        document.getElementById('modal-ket').value = ket || '';
        dateInput.value = date;
        document.getElementById('modal-job').value = jobId || '';
        document.getElementById('modal-voucher').value = voucher || '';
        
        setFormType(type);
        updateAccountUi();
        
        if (window.selectModalCategoryById) {
          window.selectModalCategoryById(catId);
        }
        if (window.resetExtraModalItems) {
          window.resetExtraModalItems();
        }
        if (window.setMultiItemHeaderVisible) {
          window.setMultiItemHeaderVisible(false);
        }

        saveBtn.textContent = 'Simpan Perubahan';
        overlay.classList.add('active');
      });
    });

    function closeModal() {
      overlay.classList.remove('active');
      if (window.closeCategoryDropdown) {
        window.closeCategoryDropdown();
      }
      if (window.resetExtraModalItems) {
        window.resetExtraModalItems();
      }
    }

    document.getElementById('btn-modal-close').addEventListener('click', closeModal);
    document.getElementById('btn-modal-cancel').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal();
    });

    // --- Import Modal Logic ---
    const importOverlay = document.getElementById('import-tx-modal-overlay');
    const btnImport = document.getElementById('btn-import-tx');
    const btnImportClose = document.getElementById('btn-import-close-tx');
    const btnImportCancel = document.getElementById('btn-import-cancel-tx');

    if (btnImport) {
      btnImport.addEventListener('click', () => {
        importOverlay.classList.add('active');
      });
    }

    function closeImportModal() {
      importOverlay.classList.remove('active');
    }

    if (btnImportClose) btnImportClose.addEventListener('click', closeImportModal);
    if (btnImportCancel) btnImportCancel.addEventListener('click', closeImportModal);
    importOverlay.addEventListener('click', (e) => {
      if (e.target === importOverlay) closeImportModal();
    });

    // --- Terbilang Helper (JS) ---
    function terbilang(angka) {
      angka = Math.floor(Math.abs(angka));
      const words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
      let temp = "";
      if (angka < 12) {
        temp = " " + words[angka];
      } else if (angka < 20) {
        temp = terbilang(angka - 10) + " Belas";
      } else if (angka < 100) {
        temp = terbilang(Math.floor(angka / 10)) + " Puluh" + terbilang(angka % 10);
      } else if (angka < 200) {
        temp = " Seratus" + terbilang(angka - 100);
      } else if (angka < 1000) {
        temp = terbilang(Math.floor(angka / 100)) + " Ratus" + terbilang(angka % 100);
      } else if (angka < 2000) {
        temp = " Seribu" + terbilang(angka - 1000);
      } else if (angka < 1000000) {
        temp = terbilang(Math.floor(angka / 1000)) + " Ribu" + terbilang(angka % 1000);
      } else if (angka < 1000000000) {
        temp = terbilang(Math.floor(angka / 1000000)) + " Juta" + terbilang(angka % 1000000);
      } else if (angka < 1000000000000) {
        temp = terbilang(Math.floor(angka / 1000000000)) + " Miliar" + terbilang(angka % 1000000000);
      } else if (angka < 1000000000000000) {
        temp = terbilang(Math.floor(angka / 1000000000000)) + " Triliun" + terbilang(angka % 1000000000000);
      }
      return temp;
    }

    function getSpelledRupiah(angka) {
      if (angka === 0) return "Nol Rupiah";
      return (terbilang(angka).trim() + " Rupiah").replace(/\s+/g, ' ');
    }

    // --- Print Voucher Logic ---
    const voucherOverlay = document.getElementById('voucher-modal-overlay');
    const voucherForm = document.getElementById('voucher-form');
    const btnVoucherClose = document.getElementById('btn-voucher-close');
    const btnVoucherCancel = document.getElementById('btn-voucher-cancel');
    const itemsContainer = document.getElementById('voucher-items-container');

    function calculateVoucherTotal() {
      let total = 0;
      document.querySelectorAll('.item-amount-input').forEach(input => {
        total += parseFloat(input.value) || 0;
      });
      document.getElementById('voucher-total-display').textContent = 'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      document.getElementById('voucher-terbilang-display').textContent = '# ' + getSpelledRupiah(total) + ' #';
    }

    function addVoucherRow(desc = '', np = '', amount = '', ket = '') {
      const div = document.createElement('div');
      div.className = 'voucher-item-row';
      div.style.display = 'flex';
      div.style.gap = '8px';
      div.style.alignItems = 'center';
      div.innerHTML = `
        <input type="text" class="form-input item-np-input" name="item_np[]" value="${np}" placeholder="NP (51540)" style="width:100px; font-size:0.85rem" />
        <input type="text" class="form-input item-desc-input" name="item_desc[]" value="${desc}" placeholder="Rincian pos uraian..." style="flex:2; font-size:0.85rem" required />
        <input type="number" class="form-input item-amount-input" name="item_amount[]" value="${amount}" placeholder="Jumlah (Rp)..." style="flex:1; font-size:0.85rem" min="0" step="any" required />
        <input type="text" class="form-input item-ket-input" name="item_ket[]" value="${ket}" placeholder="Keterangan..." style="flex:1; font-size:0.85rem" />
        <button type="button" class="btn btn-danger btn-remove-item" style="padding: 6px 10px; cursor:pointer; flex-shrink:0;">✕</button>
      `;
      itemsContainer.appendChild(div);

      div.querySelector('.item-amount-input').addEventListener('input', calculateVoucherTotal);
      div.querySelector('.btn-remove-item').addEventListener('click', () => {
        div.remove();
        calculateVoucherTotal();
      });
      calculateVoucherTotal();
    }

    document.getElementById('btn-add-voucher-item').addEventListener('click', () => {
      addVoucherRow('', '', '', '');
    });

    document.querySelectorAll('.btn-print-voucher').forEach(btn => {
      btn.addEventListener('click', () => {
        const txId = btn.dataset.id;
        const txVoucher = btn.dataset.voucher;
        const txAmount = parseFloat(btn.dataset.amount) || 0;
        const txDesc = btn.dataset.description;
        const txDate = btn.dataset.date;
        const txType = btn.dataset.type;
        const txKet = btn.dataset.ket || '';
        const txNp = btn.dataset.np || '';
        const txProdi = btn.dataset.prodi || 'Pusat';

        document.getElementById('voucher-transaction-id').value = txId;
        document.getElementById('voucher-transaction-type').value = txType;
        document.getElementById('voucher-date').value = txDate;
        document.getElementById('voucher-prodi').value = txProdi;
        
        const modalTitle = document.getElementById('voucher-modal-title');
        if (modalTitle) {
          modalTitle.textContent = txType === 'income' ? 'Cetak Bukti Pemasukan' : 'Cetak Bukti Pengeluaran';
        }

        // Generate voucher number sequence KT.YY.MM 001 if none exists
        if (txVoucher && txVoucher !== '' && txVoucher !== '—') {
          document.getElementById('voucher-number').value = txVoucher;
        } else {
          const dateObj = new Date(txDate);
          const m = String(dateObj.getMonth() + 1).padStart(2, '0');
          const y = String(dateObj.getFullYear()).substring(2);
          const paddedId = String(txId).padStart(3, '0');
          document.getElementById('voucher-number').value = `KT.${y}.${m} ${paddedId}`;
        }

        // Reset items container and populate all items sharing this voucher number
        itemsContainer.innerHTML = '';
        const siblingBtns = (txVoucher && txVoucher !== '' && txVoucher !== '—')
          ? Array.from(document.querySelectorAll(`.btn-print-voucher[data-voucher="${CSS.escape(txVoucher)}"]`))
          : [];

        if (siblingBtns.length > 1) {
          siblingBtns.forEach(sBtn => {
            addVoucherRow(
              sBtn.dataset.description,
              sBtn.dataset.np || '',
              parseFloat(sBtn.dataset.amount) || 0,
              sBtn.dataset.ket || ''
            );
          });
        } else {
          addVoucherRow(txDesc, txNp, txAmount, txKet);
        }

        voucherOverlay.classList.add('active');
      });
    });

    function closeVoucherModal() {
      voucherOverlay.classList.remove('active');
    }

    if (btnVoucherClose) btnVoucherClose.addEventListener('click', closeVoucherModal);
    if (btnVoucherCancel) btnVoucherCancel.addEventListener('click', closeVoucherModal);
    voucherOverlay.addEventListener('click', (e) => {
      if (e.target === voucherOverlay) closeVoucherModal();
    });

    // --- Bulk Action Logic ---
    const selectAll = document.getElementById('tx-select-all');
    const rowCheckboxes = document.querySelectorAll('.tx-checkbox');
    const bulkBar = document.getElementById('tx-bulk-bar');
    const selectedCountText = document.getElementById('tx-selected-count');
    const bulkIdsContainer = document.getElementById('bulk-delete-ids-container');

    function updateBulkSelection() {
      const checked = document.querySelectorAll('.tx-checkbox:checked');
      if (checked.length > 0) {
        bulkBar.style.display = 'flex';
        selectedCountText.textContent = `${checked.length} dipilih`;
        
        bulkIdsContainer.innerHTML = '';
        checked.forEach(cb => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'ids[]';
          input.value = cb.dataset.id;
          bulkIdsContainer.appendChild(input);
        });
      } else {
        bulkBar.style.display = 'none';
        bulkIdsContainer.innerHTML = '';
      }
    }

    if (selectAll) {
      selectAll.addEventListener('change', (e) => {
        rowCheckboxes.forEach(cb => {
          cb.checked = e.target.checked;
        });
        updateBulkSelection();
      });
    }

    rowCheckboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        if (!cb.checked) selectAll.checked = false;
        const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
        if (allChecked) selectAll.checked = true;

        updateBulkSelection();
      });
    });
  });
</script>
@endsection
