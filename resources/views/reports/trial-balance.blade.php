@extends('layouts.app')

@section('title', 'Neraca Saldo (Trial Balance) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
        <h2 style="margin:0">Neraca Saldo</h2>
        <span class="badge" style="font-size:0.8rem; background:rgba(99,102,241,0.15); color:#a5b4fc; border:1px solid rgba(99,102,241,0.3); padding:3px 8px; border-radius:6px; font-weight:600">
          MYOB Trial Balance
        </span>
      </div>
      <p style="margin:0; color:var(--text-secondary)">Daftar seluruh saldo akun buku besar (COA) untuk memverifikasi keseimbangan debet dan kredit kampus.</p>
    </div>
    <div class="flex gap-1" style="flex-wrap:wrap">
      <a href="{{ route('reports.trial-balance.export-csv', request()->query()) }}" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
        <x-lucide-download style="width:16px; height:16px;" /> Export CSV
      </a>
      <a href="{{ route('reports.trial-balance.print', request()->query()) }}" target="_blank" class="btn btn-primary" style="text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <x-lucide-printer style="width:16px; height:16px;" /> Cetak Neraca Saldo (A4)
      </a>
    </div>
  </div>

  <!-- Balancing & Period Banner ala MYOB -->
  <div class="card mb-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(99, 102, 241, 0.2); padding: 16px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); display:flex; align-items:center; justify-content:center;">
          <x-lucide-scale style="width:24px; height:24px; color:#818cf8;" />
        </div>
        <div>
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); font-weight:600">Verifikasi Neraca Saldo</div>
          <div style="font-size:1.15rem; font-weight:700; color:#f8fafc;">
            Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
          </div>
          <div style="font-size:0.8rem; color:#94a3b8; margin-top:2px;">
            Menampilkan {{ count($items) }} akun aktif dengan mutasi atau saldo berjalan.
          </div>
        </div>
      </div>

      <!-- Real-time Balances -->
      <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="text-align:right; border-right:1px solid rgba(255,255,255,0.1); padding-right:16px;">
          <div style="font-size:0.75rem; color:#f87171; font-weight:600;">Total Debet</div>
          <div style="font-size:1.15rem; font-weight:800; color:#f87171;" class="font-mono-num tabular-nums">
            Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
          </div>
        </div>
        <div style="text-align:right; border-right:1px solid rgba(255,255,255,0.1); padding-right:16px;">
          <div style="font-size:0.75rem; color:#4ade80; font-weight:600;">Total Kredit</div>
          <div style="font-size:1.15rem; font-weight:800; color:#4ade80;" class="font-mono-num tabular-nums">
            Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}
          </div>
        </div>
        <div style="text-align:right; background:rgba(99,102,241,0.1); padding:8px 16px; border-radius:10px; border:1px solid rgba(99,102,241,0.25);">
          <div style="font-size:0.75rem; color:#93c5fd; font-weight:600; text-transform:uppercase;">Status Keseimbangan</div>
          <div style="font-size:1.05rem; font-weight:800; font-family: monospace;">
            @if($outOfBalance < 0.01)
              <span style="display:inline-flex; align-items:center; gap:4px; color:#4ade80;">
                <x-lucide-check style="width:14px; height:14px;" /> SEIMBANG (0)
              </span>
            @else
              <span style="display:inline-flex; align-items:center; gap:4px; color:#f87171;">
                <x-lucide-alert-triangle style="width:14px; height:14px;" /> SELISIH: Rp {{ number_format($outOfBalance, 0, ',', '.') }}
              </span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Bar -->
  <form action="{{ route('reports.trial-balance') }}" method="GET" id="tb-filter-form">
    <div class="filter-bar">
      <div style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:0.85rem; color:var(--text-secondary); font-weight:600">Rentang Periode:</span>
        <input type="date" class="form-input" name="start_date" id="tb-filter-from" value="{{ $startDate }}" style="width:auto;min-width:140px" />
        <span style="color:var(--text-muted)">s/d</span>
        <input type="date" class="form-input" name="end_date" id="tb-filter-to" value="{{ $endDate }}" style="width:auto;min-width:140px" />
        <button type="submit" class="btn btn-secondary btn-sm" style="padding:7px 14px; display:inline-flex; align-items:center; gap:4px;">
          <x-lucide-filter style="width:14px; height:14px;" /> Terapkan Filter
        </button>
      </div>
    </div>
  </form>

  <!-- Trial Balance Table -->
  <div class="card">
    <div class="table-container">
      <table class="data-table" id="tb-table">
        <thead>
          <tr>
            <th style="min-width:110px">Kode Akun</th>
            <th style="min-width:220px">Nama Akun (Chart of Accounts)</th>
            <th style="min-width:110px">Tipe Normal</th>
            <th style="text-align:right; min-width:130px; color:#cbd5e1">Mutasi Debet</th>
            <th style="text-align:right; min-width:130px; color:#cbd5e1">Mutasi Kredit</th>
            <th style="text-align:right; min-width:145px; color:#f87171; background:rgba(239,68,68,0.04)">Saldo Debet (Rp)</th>
            <th style="text-align:right; min-width:145px; color:#4ade80; background:rgba(34,197,94,0.04)">Saldo Kredit (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @if(empty($items))
            <tr>
              <td colspan="7" style="text-align:center; padding: 40px 0;">
                <div style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 5px;">Belum ada data transaksi pada periode ini</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Sesuaikan rentang tanggal filter untuk melihat saldo akun.</div>
              </td>
            </tr>
          @else
            @foreach($items as $row)
              <tr>
                <td>
                  <span class="badge font-mono-num" style="font-size: 0.8rem; font-weight: 700; color: #a5b4fc; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.28); padding: 2px 7px; border-radius: 4px;">
                    {{ $row['category']->code }}
                  </span>
                </td>
                <td>
                  <div style="display:flex; align-items:center; gap:8px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $row['category']->color ?? '#818cf8' }}; flex-shrink:0;"></span>
                    <a href="{{ route('reports.general-ledger', ['category_id' => $row['category']->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" style="color:#f1f5f9; text-decoration:none; font-weight:600" title="Buka Buku Besar Akun Ini">
                      {{ $row['category']->name }}
                    </a>
                  </div>
                </td>
                <td>
                  <span style="font-size:0.75rem; color: {{ $row['is_debit_normal'] ? '#fca5a5' : '#86efac' }}; font-weight:600">
                    {{ $row['is_debit_normal'] ? 'Debet' : 'Kredit' }}
                  </span>
                </td>
                <td style="text-align:right; font-size:0.85rem; color:#94a3b8" class="font-mono-num tabular-nums">
                  {{ $row['debit_mutation'] > 0 ? number_format($row['debit_mutation'], 0, ',', '.') : '—' }}
                </td>
                <td style="text-align:right; font-size:0.85rem; color:#94a3b8" class="font-mono-num tabular-nums">
                  {{ $row['credit_mutation'] > 0 ? number_format($row['credit_mutation'], 0, ',', '.') : '—' }}
                </td>
                <!-- Saldo Debet -->
                <td style="text-align:right; font-weight:700; font-size:0.9rem; color:#f87171; background:rgba(239,68,68,0.03)" class="font-mono-num tabular-nums">
                  {{ $row['ending_debit'] > 0 ? number_format($row['ending_debit'], 0, ',', '.') : '—' }}
                </td>
                <!-- Saldo Kredit -->
                <td style="text-align:right; font-weight:700; font-size:0.9rem; color:#4ade80; background:rgba(34,197,94,0.03)" class="font-mono-num tabular-nums">
                  {{ $row['ending_credit'] > 0 ? number_format($row['ending_credit'], 0, ',', '.') : '—' }}
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
        @if(!empty($items))
          <tfoot>
            <tr style="border-top:2px solid rgba(255,255,255,0.2); background:rgba(15,23,42,0.6); font-weight:800;">
              <td colspan="5" style="text-align:right; font-size:0.95rem; color:#f8fafc; padding:12px 16px;">
                TOTAL NERACA SALDO:
              </td>
              <td style="text-align:right; font-size:1.05rem; color:#f87171; padding:12px 16px;" class="font-mono-num tabular-nums">
                Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
              </td>
              <td style="text-align:right; font-size:1.05rem; color:#4ade80; padding:12px 16px;" class="font-mono-num tabular-nums">
                Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}
              </td>
            </tr>
            <tr style="background:rgba(15,23,42,0.8); font-weight:700;">
              <td colspan="5" style="text-align:right; font-size:0.85rem; color:#94a3b8; padding:8px 16px;">
                OUT OF BALANCE (SELISIH):
              </td>
              <td colspan="2" style="text-align:center; font-size:0.95rem; color: {{ $outOfBalance < 0.01 ? '#4ade80' : '#ef4444' }}; padding:8px 16px;" class="font-mono-num tabular-nums">
                {{ $outOfBalance < 0.01 ? 'Rp 0 (SEIMBANG / BALANCED)' : 'Rp ' . number_format($outOfBalance, 0, ',', '.') }}
              </td>
            </tr>
          </tfoot>
        @endif
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) { lucide.createIcons(); }
  });
</script>
@endsection
