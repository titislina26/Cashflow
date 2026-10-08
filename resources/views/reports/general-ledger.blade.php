@extends('layouts.app')

@section('title', 'Buku Besar (General Ledger) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
        <h2 style="margin:0">Buku Besar</h2>
        <span class="badge" style="font-size:0.8rem; background:rgba(99,102,241,0.15); color:#a5b4fc; border:1px solid rgba(99,102,241,0.3); padding:3px 8px; border-radius:6px; font-weight:600">
          MYOB General Ledger
        </span>
      </div>
      <p style="margin:0; color:var(--text-secondary)">Mutasi kronologis detail, rincian debet/kredit, dan saldo berjalan per akun buku besar.</p>
    </div>
    <div class="flex gap-1" style="flex-wrap:wrap">
      <a href="{{ route('reports.general-ledger.export-csv', request()->query()) }}" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
        <x-lucide-download style="width:16px; height:16px;" /> Export CSV
      </a>
      <a href="{{ route('reports.general-ledger.print', request()->query()) }}" target="_blank" class="btn btn-primary" style="text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <x-lucide-printer style="width:16px; height:16px;" /> Cetak Buku Besar (A4)
      </a>
    </div>
  </div>

  <!-- Summary Card -->
  <div class="card mb-3" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(99, 102, 241, 0.2); padding: 16px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:12px; background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); display:flex; align-items:center; justify-content:center;">
          <x-lucide-book-open style="width:24px; height:24px; color:#818cf8;" />
        </div>
        <div>
          <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); font-weight:600">Ringkasan Buku Besar</div>
          <div style="font-size:1.15rem; font-weight:700; color:#f8fafc;">
            Periode: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
          </div>
          <div style="font-size:0.8rem; color:#94a3b8; margin-top:2px;">
            Menampilkan {{ count($ledgerData) }} buku akun yang memiliki transaksi atau saldo berjalan.
          </div>
        </div>
      </div>

      <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
        <div style="text-align:right; border-right:1px solid rgba(255,255,255,0.1); padding-right:16px;">
          <div style="font-size:0.75rem; color:#f87171; font-weight:600;">Total Mutasi Debet</div>
          <div style="font-size:1.15rem; font-weight:800; color:#f87171;" class="font-mono-num tabular-nums">
            Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
          </div>
        </div>
        <div style="text-align:right; background:rgba(99,102,241,0.1); padding:8px 16px; border-radius:10px; border:1px solid rgba(99,102,241,0.25);">
          <div style="font-size:0.75rem; color:#4ade80; font-weight:600; text-transform:uppercase;">Total Mutasi Kredit</div>
          <div style="font-size:1.25rem; font-weight:800; color:#4ade80;" class="font-mono-num tabular-nums">
            Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Bar -->
  <form action="{{ route('reports.general-ledger') }}" method="GET" id="gl-filter-form">
    <div class="filter-bar">
      <!-- Select Account -->
      <select class="form-select" name="category_id" id="gl-filter-category" style="min-width:260px">
        <option value="all" {{ $categoryId === 'all' ? 'selected' : '' }}>Semua Akun Buku Besar</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>
            [{{ $c->code }}] {{ $c->name }}
          </option>
        @endforeach
      </select>

      <!-- Select Job -->
      <select class="form-select" name="job_id" id="gl-filter-job" style="min-width:180px">
        <option value="all" {{ $jobId === 'all' ? 'selected' : '' }}>Semua Proyek (Jobs)</option>
        @foreach($jobs as $j)
          <option value="{{ $j->id }}" {{ $jobId == $j->id ? 'selected' : '' }}>
            [{{ $j->code }}] {{ $j->name }}
          </option>
        @endforeach
      </select>

      <div style="display:flex; align-items:center; gap:6px;">
        <input type="date" class="form-input" name="start_date" id="gl-filter-from" value="{{ $startDate }}" style="width:auto;min-width:130px" />
        <span style="color:var(--text-muted)">s/d</span>
        <input type="date" class="form-input" name="end_date" id="gl-filter-to" value="{{ $endDate }}" style="width:auto;min-width:130px" />
        <button type="submit" class="btn btn-secondary btn-sm" style="padding:7px 14px; display:inline-flex; align-items:center; gap:4px;">
          <x-lucide-filter style="width:14px; height:14px;" /> Terapkan
        </button>
      </div>
    </div>
  </form>

  <!-- Ledger Accounts -->
  @if(empty($ledgerData))
    <div class="card" style="text-align:center; padding: 40px 0;">
      <div style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 5px;">Tidak ada transaksi buku besar yang ditemukan</div>
      <div style="font-size: 0.85rem; color: var(--text-muted);">Pilih akun lain atau ubah rentang tanggal filter untuk melihat buku besar.</div>
    </div>
  @else
    @foreach($ledgerData as $account)
      <div class="card mb-3" style="border: 1px solid rgba(255,255,255,0.08);">
        <!-- Account Header Strip -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding: 12px 18px; background: rgba(30, 41, 59, 0.4); border-bottom: 1px solid rgba(255,255,255,0.06); flex-wrap:wrap; gap:10px;">
          <div style="display:flex; align-items:center; gap:10px;">
            <span class="badge" style="font-family: monospace; font-size: 0.85rem; font-weight: 800; color: #a5b4fc; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); padding: 3px 8px; border-radius: 6px;">
              {{ $account['category']->code }}
            </span>
            <span style="font-size:1.05rem; font-weight:700; color:#f8fafc">
              {{ $account['category']->name }}
            </span>
            <span style="font-size:0.75rem; color: {{ $account['is_debit_normal'] ? '#fca5a5' : '#86efac' }}; background:rgba(255,255,255,0.04); padding:2px 6px; border-radius:4px; font-weight:600">
              Saldo Normal: {{ $account['is_debit_normal'] ? 'Debet' : 'Kredit' }}
            </span>
          </div>

          <div style="display:flex; align-items:center; gap:16px;">
            <div style="font-size:0.8rem; color:var(--text-secondary)">
              Saldo Awal: <strong style="font-family:monospace; color:#f1f5f9">Rp {{ number_format($account['beginning_balance'], 0, ',', '.') }}</strong>
            </div>
            <div style="font-size:0.8rem; color:var(--text-secondary)">
              Saldo Akhir: <strong style="font-family:monospace; color:#38bdf8">Rp {{ number_format($account['ending_balance'], 0, ',', '.') }}</strong>
            </div>
          </div>
        </div>

        <!-- Account Transactions Table -->
        <div class="table-container">
          <table class="data-table" style="font-size:0.85rem;">
            <thead>
              <tr style="background:rgba(15,23,42,0.4)">
                <th style="min-width:95px">Tanggal</th>
                <th style="min-width:120px">No. Bukti / Ref</th>
                <th>Memo / Deskripsi Transaksi</th>
                <th style="min-width:100px">Job</th>
                <th style="text-align:right; min-width:120px; color:#f87171">Debet (Rp)</th>
                <th style="text-align:right; min-width:120px; color:#4ade80">Kredit (Rp)</th>
                <th style="text-align:right; min-width:140px; color:#38bdf8">Saldo Berjalan (Rp)</th>
              </tr>
            </thead>
            <tbody>
              <!-- Row Saldo Awal -->
              <tr style="background:rgba(255,255,255,0.015); font-style:italic">
                <td style="color:var(--text-muted)">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                <td style="color:var(--text-muted)">—</td>
                <td style="color:var(--text-muted); font-weight:600">Saldo Awal Periode</td>
                <td style="color:var(--text-muted)">—</td>
                <td style="text-align:right; color:var(--text-muted)">—</td>
                <td style="text-align:right; color:var(--text-muted)">—</td>
                <td style="text-align:right; font-family:monospace; font-weight:700; color:#cbd5e1">
                  Rp {{ number_format($account['beginning_balance'], 0, ',', '.') }}
                </td>
              </tr>

              @if(empty($account['transactions']))
                <tr>
                  <td colspan="7" style="text-align:center; padding: 14px 0; color:var(--text-muted); font-size:0.8rem">
                    Tidak ada transaksi mutasi pada periode ini.
                  </td>
                </tr>
              @else
                @foreach($account['transactions'] as $row)
                  <tr>
                    <td style="white-space:nowrap">{{ $row['transaction']->date->format('d M Y') }}</td>
                    <td>
                      <span style="font-family:monospace; font-size:0.8rem; color:#a5b4fc; font-weight:600">
                        {{ $row['transaction']->voucher_number ?? '—' }}
                      </span>
                    </td>
                    <td>
                      <span style="color:#f1f5f9">{{ $row['transaction']->description }}</span>
                    </td>
                    <td>
                      @if($row['transaction']->job)
                        <span style="font-weight:700; color:var(--accent-primary); font-size:0.75rem">[{{ $row['transaction']->job->code }}]</span>
                      @else
                        <span style="color:var(--text-muted)">—</span>
                      @endif
                    </td>
                    <td style="text-align:right; font-family:monospace; font-weight:700; color:#f87171">
                      {{ $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '—' }}
                    </td>
                    <td style="text-align:right; font-family:monospace; font-weight:700; color:#4ade80">
                      {{ $row['credit'] > 0 ? number_format($row['credit'], 0, ',', '.') : '—' }}
                    </td>
                    <td style="text-align:right; font-family:monospace; font-weight:700; color:#38bdf8">
                      Rp {{ number_format($row['running_balance'], 0, ',', '.') }}
                    </td>
                  </tr>
                @endforeach
              @endif
            </tbody>
            <tfoot>
              <tr style="border-top:1px solid rgba(255,255,255,0.1); background:rgba(15,23,42,0.3); font-weight:700">
                <td colspan="4" style="text-align:right; font-size:0.8rem; color:var(--text-secondary)">
                  Subtotal Mutasi Akun:
                </td>
                <td style="text-align:right; font-family:monospace; color:#f87171">
                  Rp {{ number_format($account['total_debit'], 0, ',', '.') }}
                </td>
                <td style="text-align:right; font-family:monospace; color:#4ade80">
                  Rp {{ number_format($account['total_credit'], 0, ',', '.') }}
                </td>
                <td style="text-align:right; font-family:monospace; color:#38bdf8; font-size:0.9rem">
                  Saldo: Rp {{ number_format($account['ending_balance'], 0, ',', '.') }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    @endforeach
  @endif
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) { lucide.createIcons(); }
  });
</script>
@endsection
