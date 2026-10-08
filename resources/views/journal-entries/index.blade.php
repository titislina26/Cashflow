@extends('layouts.app')

@section('title', 'Jurnal Umum (Record Journal Entry) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
        <h2 style="margin:0">Jurnal Umum</h2>
        <span class="badge" style="font-size:0.8rem; background:rgba(99,102,241,0.15); color:#a5b4fc; border:1px solid rgba(99,102,241,0.3); padding:3px 8px; border-radius:6px; font-weight:600">
          MYOB General Journal
        </span>
      </div>
      <p style="margin:0; color:var(--text-secondary)">Pencatatan transaksi non-kas (penyusutan gedung & inventaris, penyesuaian akrual, alokasi beban) berbasis double-entry balancing.</p>
    </div>
    <div class="flex gap-1" style="flex-wrap:wrap">
      <a href="{{ route('journal-entries.export-csv', request()->query()) }}" class="btn btn-secondary" style="text-decoration:none">
        <x-lucide-download /> Export CSV
      </a>
      <button class="btn btn-primary" id="btn-open-record-journal" style="font-weight:600">
        <x-lucide-plus /> Record Journal Entry
      </button>
    </div>
  </div>

  <!-- Summary Card ala MYOB -->
  <div class="card mb-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(99, 102, 241, 0.2); padding: 16px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:46px; height:46px; border-radius:12px; background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); display:flex; align-items:center; justify-content:center; color:#818cf8;">
          <x-lucide-scale style="width:24px; height:24px;" />
        </div>
        <div>
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); font-weight:600">Buku Jurnal Umum</div>
          <div style="font-size:1.1rem; font-weight:700; color:#f8fafc;">
            Standar Double-Entry (Debet = Kredit)
          </div>
          <div style="font-size:0.8rem; color:#94a3b8; margin-top:2px;">
            Semua penyesuaian non-kas otomatis terhubung ke Neraca & Laporan Surplus Defisit (LSD).
          </div>
        </div>
      </div>

      <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="text-align:right; border-right:1px solid rgba(255,255,255,0.1); padding-right:16px;">
          <div style="font-size:0.75rem; color:#94a3b8; font-weight:600;">Total Jurnal Terdata</div>
          <div style="font-size:1.15rem; font-weight:700; color:#f8fafc;" class="font-mono-num">{{ $totalEntries }} Transaksi</div>
        </div>
        <div style="text-align:right; background:rgba(99,102,241,0.1); padding:8px 16px; border-radius:10px; border:1px solid rgba(99,102,241,0.25);">
          <div style="font-size:0.75rem; color:#93c5fd; font-weight:600; text-transform:uppercase;">Total Nilai Mutasi Jurnal</div>
          <div style="font-size:1.3rem; font-weight:800; color:#38bdf8;" class="font-mono-num">
            Rp {{ number_format($totalVolume, 0, ',', '.') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Bar -->
  <form action="{{ route('journal-entries.index') }}" method="GET" id="journal-filter-form">
    <div class="filter-bar">
      <div class="search-input-wrapper">
        <span class="search-icon"><x-lucide-search style="width:16px; height:16px; color:var(--text-muted);" /></span>
        <input type="text" class="form-input" name="search" id="journal-search" 
          placeholder="Cari no. jurnal atau memo..." value="{{ request('search') }}" />
      </div>
      <select class="form-select" name="month" id="journal-filter-month">
        <option value="all" {{ request('month', 'all') === 'all' ? 'selected' : '' }}>Semua Bulan</option>
        @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $mNum => $mName)
          <option value="{{ $mNum }}" {{ request('month') == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
        @endforeach
      </select>
      <input type="date" class="form-input" name="start_date" id="journal-filter-from" value="{{ request('start_date') }}" 
        style="width:auto;min-width:140px" placeholder="Mulai" />
      <input type="date" class="form-input" name="end_date" id="journal-filter-to" value="{{ request('end_date') }}" 
        style="width:auto;min-width:140px" placeholder="Selesai" />
    </div>
  </form>

  <!-- Table List of Journal Entries -->
  <div class="card">
    <div class="table-container">
      <table class="data-table" id="journal-table">
        <thead>
          <tr>
            <th style="min-width:110px">Tanggal</th>
            <th style="min-width:130px">No. Bukti Jurnal</th>
            <th style="min-width:180px">Memo (Keterangan)</th>
            <th style="min-width:280px">Rincian Akun (Debet / Kredit)</th>
            <th style="text-align:right; min-width:140px">Total Nilai</th>
            <th style="text-align:center; min-width:100px">Status</th>
            <th style="text-align:center; width:110px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @if($journalEntries->isEmpty())
            <tr>
              <td colspan="7" style="text-align:center; padding: 40px 0;">
                <div style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 5px;">Belum ada Jurnal Umum yang dicatat</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">Gunakan tombol "Record Journal Entry" untuk mencatat penyusutan, akrual, atau penyesuaian non-kas.</div>
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('btn-open-record-journal').click()">+ Buat Jurnal Pertama</button>
              </td>
            </tr>
          @else
            @foreach($journalEntries as $entry)
              <tr>
                <td style="white-space:nowrap; font-size:0.85rem; vertical-align:top; padding-top:14px">
                  {{ $entry->date->format('d M Y') }}
                </td>
                <td style="vertical-align:top; padding-top:14px">
                  <span style="font-family: monospace; font-size: 0.85rem; color: #a5b4fc; font-weight:700;">
                    {{ $entry->journal_number }}
                  </span>
                </td>
                <td style="vertical-align:top; padding-top:14px">
                  <div style="font-weight:600; color:#f1f5f9; font-size:0.9rem">{{ $entry->memo }}</div>
                </td>
                <td>
                  <div style="display:flex; flex-direction:column; gap:6px; font-size:0.82rem;">
                    @foreach($entry->transactions as $tx)
                      @php
                        $isDebit = ($tx->account !== 'general_journal') ? ($tx->type === 'income') : ($tx->type === 'expense');
                      @endphp
                      <div style="display:flex; justify-content:space-between; align-items:center; padding: 3px 6px; border-radius:4px; background: {{ $isDebit ? 'rgba(239, 68, 68, 0.06)' : 'rgba(34, 197, 94, 0.06)' }}; {{ !$isDebit ? 'margin-left: 20px;' : '' }}">
                        <div style="display:flex; align-items:center; gap:6px;">
                          @if(!$isDebit)
                            <span style="color:#22c55e; font-size:0.75rem">↳</span>
                          @endif
                          <span style="font-family:monospace; font-weight:700; color: {{ $isDebit ? '#fca5a5' : '#86efac' }}">
                            [{{ $tx->category->code ?? '—' }}]
                          </span>
                          <span style="color:var(--text-primary)">
                            {{ $tx->category->name ?? '—' }}
                          </span>
                          @if($tx->job)
                            <span style="font-size:0.75rem; color:#818cf8; font-weight:600">
                              ({{ $tx->job->code }})
                            </span>
                          @endif
                        </div>
                        <div style="font-family:monospace; font-weight:700; color: {{ $isDebit ? '#f87171' : '#4ade80' }}">
                          {{ $isDebit ? '(D)' : '(K)' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </div>
                      </div>
                    @endforeach
                  </div>
                </td>
                <td style="text-align:right; vertical-align:top; padding-top:14px">
                  <span style="font-family:monospace; font-weight:800; font-size:0.95rem; color:#38bdf8">
                    Rp {{ number_format($entry->total_amount, 0, ',', '.') }}
                  </span>
                </td>
                <td style="text-align:center; vertical-align:top; padding-top:14px">
                  <span class="badge" style="display:inline-flex; align-items:center; gap:4px; font-size:0.75rem; background:rgba(34,197,94,0.15); color:#15803d; border:1px solid rgba(34,197,94,0.3); padding:2px 8px; border-radius:4px;">
                    <x-lucide-check style="width:12px; height:12px;" /> Seimbang
                  </span>
                </td>
                <td style="text-align:center; vertical-align:top; padding-top:14px">
                  <div style="display:flex; gap:6px; justify-content:center">
                    <button class="btn btn-secondary btn-sm btn-view-journal" 
                      data-id="{{ $entry->id }}"
                      style="padding: 4px 7px"
                      title="Lihat Detail Jurnal">
                      <x-lucide-eye style="width:14px; height:14px;" />
                    </button>
                    <form action="{{ route('journal-entries.destroy', $entry->id) }}" method="POST" onsubmit="return confirm('Hapus Jurnal Umum {{ $entry->journal_number }} beserta seluruh baris mutasinya?')" style="display:inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 7px" title="Hapus Jurnal">
                        <x-lucide-trash-2 style="width:14px; height:14px;" />
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($journalEntries->hasPages())
      <div class="pagination">
        @if ($journalEntries->onFirstPage())
          <span class="pagination-btn disabled"><x-lucide-chevron-left style="width:14px; height:14px;" /></span>
        @else
          <a href="{{ $journalEntries->previousPageUrl() }}" class="pagination-btn"><x-lucide-chevron-left style="width:14px; height:14px;" /></a>
        @endif

        @for ($i = 1; $i <= $journalEntries->lastPage(); $i++)
          @if ($i == $journalEntries->currentPage())
            <span class="pagination-btn active">{{ $i }}</span>
          @else
            <a href="{{ $journalEntries->url($i) }}" class="pagination-btn">{{ $i }}</a>
          @endif
        @endfor

        @if ($journalEntries->hasMorePages())
          <a href="{{ $journalEntries->nextPageUrl() }}" class="pagination-btn"><x-lucide-chevron-right style="width:14px; height:14px;" /></a>
        @else
          <span class="pagination-btn disabled"><x-lucide-chevron-right style="width:14px; height:14px;" /></span>
        @endif

        <span class="pagination-info">{{ $journalEntries->total() }} jurnal</span>
      </div>
    @endif
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL: RECORD JOURNAL ENTRY (ALA MYOB)     -->
<!-- ========================================== -->
<div class="modal-overlay" id="record-journal-overlay">
  <div class="modal" style="max-width: 960px; width: 95%;">
    <form action="{{ route('journal-entries.store') }}" method="POST" id="form-record-journal">
      @csrf
      <div class="modal-header">
        <div style="display:flex; align-items:center; gap:8px;">
          <div style="width:32px; height:32px; border-radius:8px; background:rgba(99,102,241,0.15); color:#818cf8; display:flex; align-items:center; justify-content:center;">
            <x-lucide-scale style="width:18px; height:18px;" />
          </div>
          <div>
            <h3 style="margin:0; font-size:1.15rem">Record Journal Entry</h3>
            <span style="font-size:0.75rem; color:var(--text-muted)">Jurnal Umum Non-Kas — MYOB General Journal</span>
          </div>
        </div>
        <button type="button" class="modal-close" id="btn-close-journal-modal" aria-label="Tutup modal">
          <x-lucide-x />
        </button>
      </div>

      <div class="modal-body" style="padding: 16px 20px;">
        <!-- Header Row -->
        <div style="display:grid; grid-template-columns: 1fr 1fr 2fr; gap:14px; margin-bottom:16px;">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" style="font-size:0.8rem">General Journal No. (ID#)</label>
            <input type="text" class="form-input" name="journal_number" id="journal-number" value="{{ $suggestedJournalNumber }}" required style="font-family:monospace; font-weight:700; color:#a5b4fc;" />
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" style="font-size:0.8rem">Tanggal Transaksi</label>
            <input type="date" class="form-input" name="date" id="journal-date" value="{{ date('Y-m-d') }}" required />
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" style="font-size:0.8rem">Memo / Keterangan Transaksi</label>
            <input type="text" class="form-input" name="memo" id="journal-memo" placeholder="Contoh: Penyusutan inventaris bulan September 2026..." required />
          </div>
        </div>

        <!-- Journal Lines Grid Header -->
        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:12px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div style="font-weight:700; font-size:0.85rem; color:#f1f5f9; display:flex; align-items:center; gap:6px;">
              <span>Rincian Baris Akun (Double-Entry Matrix)</span>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" id="btn-add-line" style="font-size:0.8rem; padding:3px 10px; display:inline-flex; align-items:center; gap:4px;">
              <x-lucide-plus style="width:13px; height:13px;" /> Tambah Baris
            </button>
          </div>

          <div style="display:grid; grid-template-columns: 2.2fr 1.1fr 1.6fr 1.2fr 1.2fr 36px; gap:8px; font-size:0.75rem; font-weight:700; color:var(--text-secondary); margin-bottom:6px; padding:0 4px;">
            <div>Akun (COA) <span class="text-danger">*</span></div>
            <div>Unit (Job)</div>
            <div>Memo Baris</div>
            <div style="text-align:right; color:#f87171">Debet (Rp)</div>
            <div style="text-align:right; color:#4ade80">Kredit (Rp)</div>
            <div></div>
          </div>

          <!-- Dynamic Container for rows -->
          <div id="journal-lines-container" style="display:flex; flex-direction:column; gap:8px;">
            <!-- Rows injected by JavaScript -->
          </div>
        </div>

        <!-- Real-time Balancing Box (The iconic MYOB balancing display) -->
        <div style="margin-top:14px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(99, 102, 241, 0.25); border-radius:8px; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
          <div style="display:flex; gap:20px; align-items:center;">
            <div>
              <div style="font-size:0.75rem; color:#f87171; font-weight:600">Total Debet</div>
              <div id="display-total-debit" style="font-size:1.1rem; font-weight:800; font-family:monospace; color:#f87171">Rp 0</div>
            </div>
            <div>
              <div style="font-size:0.75rem; color:#4ade80; font-weight:600">Total Kredit</div>
              <div id="display-total-credit" style="font-size:1.1rem; font-weight:800; font-family:monospace; color:#4ade80">Rp 0</div>
            </div>
          </div>

          <div style="display:flex; align-items:center; gap:12px;">
            <div style="text-align:right">
              <div style="font-size:0.75rem; color:#94a3b8; font-weight:600">Out of Balance (Selisih)</div>
              <div id="display-out-of-balance" style="font-size:1.15rem; font-weight:800; font-family:monospace; color:#38bdf8">Rp 0</div>
            </div>
            <div id="badge-balance-status" class="badge" style="font-size:0.8rem; padding:6px 12px; border-radius:6px; font-weight:700; background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); display:inline-flex; align-items:center; gap:5px;">
              <x-lucide-alert-triangle style="width:13px; height:13px;" /> Belum Seimbang
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer" style="padding: 12px 20px;">
        <button type="button" class="btn btn-secondary" id="btn-cancel-journal-modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btn-submit-journal" disabled style="min-width:140px; font-weight:700;">
          Simpan Jurnal (Record)
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL: DETAIL BUKTI JURNAL (VOUCHER VIEW)  -->
<!-- ========================================== -->
<div class="modal-overlay" id="view-journal-overlay">
  <div class="modal" style="max-width: 700px; width:95%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:8px;">
        <div style="width:30px; height:30px; border-radius:6px; background:rgba(99,102,241,0.15); color:#818cf8; display:flex; align-items:center; justify-content:center;">
          <x-lucide-file-text style="width:16px; height:16px;" />
        </div>
        <h3 id="view-journal-title" style="margin:0; font-size:1.1rem">Detail Bukti Jurnal Umum</h3>
      </div>
      <button type="button" class="modal-close" id="btn-close-view-modal" aria-label="Tutup modal">
        <x-lucide-x />
      </button>
    </div>
    <div class="modal-body" style="padding: 18px 22px;">
      <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:14px;">
        <div>
          <div style="font-size:0.75rem; color:var(--text-muted)">Nomor Jurnal</div>
          <div id="view-journal-number" style="font-weight:700; font-family:monospace; color:#a5b4fc; font-size:1.1rem">—</div>
        </div>
        <div>
          <div style="font-size:0.75rem; color:var(--text-muted)">Tanggal</div>
          <div id="view-journal-date" style="font-weight:600; color:#f8fafc">—</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:0.75rem; color:var(--text-muted)">Total Nilai</div>
          <div id="view-journal-total" style="font-weight:800; font-family:monospace; color:#38bdf8; font-size:1.1rem">—</div>
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:2px">Memo / Keterangan</div>
        <div id="view-journal-memo" style="font-size:0.95rem; font-weight:600; color:#f1f5f9; background:rgba(255,255,255,0.03); padding:8px 12px; border-radius:6px; border:1px solid rgba(255,255,255,0.06)">—</div>
      </div>

      <div style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); margin-bottom:6px">Rincian Pos Debet & Kredit</div>
      <table class="data-table" style="font-size:0.82rem; margin-bottom:12px">
        <thead>
          <tr>
            <th>Kode & Nama Akun</th>
            <th>Job</th>
            <th style="text-align:right; color:#f87171">Debet (Rp)</th>
            <th style="text-align:right; color:#4ade80">Kredit (Rp)</th>
          </tr>
        </thead>
        <tbody id="view-journal-tbody">
          <!-- Populated dynamically -->
        </tbody>
      </table>
    </div>
    <div class="modal-footer" style="padding: 12px 20px;">
      <button type="button" class="btn btn-secondary" id="btn-close-view-modal-footer">Tutup</button>
      <button type="button" class="btn btn-primary" onclick="window.print()" style="display:inline-flex; align-items:center; gap:6px;">
        <x-lucide-printer /> Cetak / Print
      </button>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Categories & Jobs master data passed from controller
  const categories = @json($categories);
  const jobs = @json($jobs);

  // --- Auto-submit filter form ---
  const filterForm = document.getElementById('journal-filter-form');
  document.querySelectorAll('#journal-filter-month, #journal-filter-from, #journal-filter-to').forEach(el => {
    el.addEventListener('change', () => filterForm.submit());
  });

  let searchTimeout;
  document.getElementById('journal-search').addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => filterForm.submit(), 500);
  });

  // --- Modal Logic ---
  const recordOverlay = document.getElementById('record-journal-overlay');
  const btnOpenRecord = document.getElementById('btn-open-record-journal');
  const btnCloseModal = document.getElementById('btn-close-journal-modal');
  const btnCancelModal = document.getElementById('btn-cancel-journal-modal');
  const linesContainer = document.getElementById('journal-lines-container');
  const btnAddLine = document.getElementById('btn-add-line');
  
  const displayTotalDebit = document.getElementById('display-total-debit');
  const displayTotalCredit = document.getElementById('display-total-credit');
  const displayOutOfBalance = document.getElementById('display-out-of-balance');
  const badgeBalanceStatus = document.getElementById('badge-balance-status');
  const btnSubmitJournal = document.getElementById('btn-submit-journal');

  let rowCounter = 0;

  function buildCategoryOptions(selectedId = null) {
    let html = '<option value="">-- Pilih Akun (COA) --</option>';
    categories.forEach(c => {
      const isSel = selectedId && selectedId == c.id ? 'selected' : '';
      const codeStr = c.code ? `[${c.code}] ` : '';
      html += `<option value="${c.id}" ${isSel}>${codeStr}${c.name}</option>`;
    });
    return html;
  }

  function buildJobOptions(selectedId = null) {
    let html = '<option value="">(Pusat / Kosong)</option>';
    jobs.forEach(j => {
      const isSel = selectedId && selectedId == j.id ? 'selected' : '';
      html += `<option value="${j.id}" ${isSel}>[${j.code}] ${j.name}</option>`;
    });
    return html;
  }

  function addJournalRow(catId = '', jobId = '', desc = '', debit = '', credit = '') {
    rowCounter++;
    const rowId = `journal-line-${rowCounter}`;
    const div = document.createElement('div');
    div.id = rowId;
    div.className = 'journal-line-row';
    div.style.display = 'grid';
    div.style.gridTemplateColumns = '2.2fr 1.1fr 1.6fr 1.2fr 1.2fr 36px';
    div.style.gap = '8px';
    div.style.alignItems = 'center';

    div.innerHTML = `
      <select class="form-select line-category" name="lines[${rowCounter}][category_id]" required style="font-size:0.8rem; padding:6px 8px">
        ${buildCategoryOptions(catId)}
      </select>
      <select class="form-select line-job" name="lines[${rowCounter}][job_id]" style="font-size:0.8rem; padding:6px 8px">
        ${buildJobOptions(jobId)}
      </select>
      <input type="text" class="form-input line-desc" name="lines[${rowCounter}][description]" value="${desc}" placeholder="Memo baris (opsional)..." style="font-size:0.8rem; padding:6px 8px" />
      <input type="number" step="any" min="0" class="form-input line-debit" name="lines[${rowCounter}][debit]" value="${debit}" placeholder="0" style="text-align:right; font-family:monospace; font-size:0.85rem; padding:6px 8px; font-weight:600; color:#f87171" />
      <input type="number" step="any" min="0" class="form-input line-credit" name="lines[${rowCounter}][credit]" value="${credit}" placeholder="0" style="text-align:right; font-family:monospace; font-size:0.85rem; padding:6px 8px; font-weight:600; color:#4ade80" />
      <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="padding:5px 8px; font-size:0.75rem; border-radius:4px; display:inline-flex; align-items:center; justify-content:center;" title="Hapus Baris"><i data-lucide="trash-2" style="width:13px; height:13px;"></i></button>
    `;

    linesContainer.appendChild(div);
    if (window.lucide) { lucide.createIcons(); }

    const debitInput = div.querySelector('.line-debit');
    const creditInput = div.querySelector('.line-credit');
    const removeBtn = div.querySelector('.btn-remove-row');

    // Mutual exclusivity per row: if debit is typed, clear credit, and vice versa
    debitInput.addEventListener('input', () => {
      if (parseFloat(debitInput.value) > 0) {
        creditInput.value = '';
      }
      calculateBalance();
    });

    creditInput.addEventListener('input', () => {
      if (parseFloat(creditInput.value) > 0) {
        debitInput.value = '';
      }
      calculateBalance();
    });

    removeBtn.addEventListener('click', () => {
      if (document.querySelectorAll('.journal-line-row').length > 2) {
        div.remove();
        calculateBalance();
      } else {
        alert('Minimal harus ada 2 baris transaksi dalam jurnal!');
      }
    });

    calculateBalance();
  }

  function calculateBalance() {
    let totalDebit = 0;
    let totalCredit = 0;

    document.querySelectorAll('.line-debit').forEach(inp => {
      totalDebit += parseFloat(inp.value) || 0;
    });

    document.querySelectorAll('.line-credit').forEach(inp => {
      totalCredit += parseFloat(inp.value) || 0;
    });

    const diff = Math.abs(totalDebit - totalCredit);
    const isBalanced = (diff < 0.01) && (totalDebit > 0);

    displayTotalDebit.textContent = 'Rp ' + totalDebit.toLocaleString('id-ID');
    displayTotalCredit.textContent = 'Rp ' + totalCredit.toLocaleString('id-ID');
    displayOutOfBalance.textContent = 'Rp ' + diff.toLocaleString('id-ID');

    if (isBalanced) {
      displayOutOfBalance.style.color = '#4ade80';
      badgeBalanceStatus.className = 'badge';
      badgeBalanceStatus.style.background = 'rgba(34, 197, 94, 0.15)';
      badgeBalanceStatus.style.color = '#4ade80';
      badgeBalanceStatus.style.border = '1px solid rgba(34, 197, 94, 0.3)';
      badgeBalanceStatus.innerHTML = '<span style="display:inline-flex; align-items:center; gap:4px;"><i data-lucide="check" style="width:12px; height:12px;"></i> Seimbang (Balanced)</span>';
      if (window.lucide) { lucide.createIcons(); }
      btnSubmitJournal.disabled = false;
      btnSubmitJournal.style.opacity = '1';
      btnSubmitJournal.style.cursor = 'pointer';
    } else {
      displayOutOfBalance.style.color = '#f87171';
      badgeBalanceStatus.className = 'badge';
      badgeBalanceStatus.style.background = 'rgba(239, 68, 68, 0.15)';
      badgeBalanceStatus.style.color = '#f87171';
      badgeBalanceStatus.style.border = '1px solid rgba(239, 68, 68, 0.3)';
      const msg = totalDebit === 0 && totalCredit === 0 ? 'Belum Ada Nilai' : `Selisih: Rp ${diff.toLocaleString('id-ID')}`;
      badgeBalanceStatus.innerHTML = `<span style="display:inline-flex; align-items:center; gap:4px;"><i data-lucide="alert-triangle" style="width:12px; height:12px;"></i> ${msg}</span>`;
      if (window.lucide) { lucide.createIcons(); }
      btnSubmitJournal.disabled = true;
      btnSubmitJournal.style.opacity = '0.5';
      btnSubmitJournal.style.cursor = 'not-allowed';
    }
  }

  // Open Modal
  if (btnOpenRecord) {
    btnOpenRecord.addEventListener('click', () => {
      linesContainer.innerHTML = '';
      rowCounter = 0;
      // Start with 2 clean empty rows
      addJournalRow();
      addJournalRow();
      recordOverlay.classList.add('active');
    });
  }

  function closeRecordModal() {
    recordOverlay.classList.remove('active');
  }

  if (btnCloseModal) btnCloseModal.addEventListener('click', closeRecordModal);
  if (btnCancelModal) btnCancelModal.addEventListener('click', closeRecordModal);
  recordOverlay.addEventListener('click', (e) => {
    if (e.target === recordOverlay) closeRecordModal();
  });

  if (btnAddLine) {
    btnAddLine.addEventListener('click', () => {
      addJournalRow();
    });
  }

  // --- View Voucher Detail Modal ---
  const viewOverlay = document.getElementById('view-journal-overlay');
  const btnCloseView = document.getElementById('btn-close-view-modal');
  const btnCloseViewFooter = document.getElementById('btn-close-view-modal-footer');

  function closeViewModal() {
    viewOverlay.classList.remove('active');
  }

  if (btnCloseView) btnCloseView.addEventListener('click', closeViewModal);
  if (btnCloseViewFooter) btnCloseViewFooter.addEventListener('click', closeViewModal);
  viewOverlay.addEventListener('click', (e) => {
    if (e.target === viewOverlay) closeViewModal();
  });

  document.querySelectorAll('.btn-view-journal').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      try {
        const res = await fetch(`{{ url('/journal-entries') }}/${id}`);
        const data = await res.json();

        document.getElementById('view-journal-number').textContent = data.journal_number;
        const dt = new Date(data.date);
        document.getElementById('view-journal-date').textContent = dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        document.getElementById('view-journal-total').textContent = 'Rp ' + parseFloat(data.total_amount).toLocaleString('id-ID');
        document.getElementById('view-journal-memo').textContent = data.memo;

        const tbody = document.getElementById('view-journal-tbody');
        tbody.innerHTML = '';

        data.transactions.forEach(tx => {
          const isDebit = tx.account !== 'general_journal' ? tx.type === 'income' : tx.type === 'expense';
          const tr = document.createElement('tr');
          const code = tx.category ? `[${tx.category.code}] ` : '';
          const name = tx.category ? tx.category.name : '—';
          const jobName = tx.job ? `[${tx.job.code}] ${tx.job.name}` : '—';
          const amountStr = 'Rp ' + parseFloat(tx.amount).toLocaleString('id-ID');

          tr.innerHTML = `
            <td style="${!isDebit ? 'padding-left: 28px;' : ''}">
              ${!isDebit ? '<span style="color:#22c55e; margin-right:4px">↳</span>' : ''}
              <span style="font-family:monospace; font-weight:700; color:${isDebit ? '#fca5a5' : '#86efac'}">${code}</span>
              <span style="color:#f1f5f9">${name}</span>
            </td>
            <td><span style="font-size:0.8rem; color:#94a3b8">${jobName}</span></td>
            <td style="text-align:right; font-family:monospace; font-weight:700; color:#f87171">${isDebit ? amountStr : '—'}</td>
            <td style="text-align:right; font-family:monospace; font-weight:700; color:#4ade80">${!isDebit ? amountStr : '—'}</td>
          `;
          tbody.appendChild(tr);
        });

        viewOverlay.classList.add('active');
      } catch (err) {
        console.error(err);
        alert('Gagal memuat rincian jurnal.');
      }
    });
  });
});
</script>
@endsection
