@extends('layouts.app')

@section('title', 'Buku Besar (General Ledger) — Cashflow Management')

@section('content')
<div class="page-content">
  <!-- Page Header -->
  <div class="page-header" style="margin-bottom: 16px;">
    <div>
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
        <h2 style="margin:0">Buku Besar</h2>
        <span class="badge" style="font-size:0.78rem; background:rgba(99,102,241,0.15); color:#a5b4fc; border:1px solid rgba(99,102,241,0.3); padding:2px 8px; border-radius:6px; font-weight:600">
          General Ledger
        </span>
      </div>
      <p style="margin:0; color:var(--text-secondary); font-size:0.88rem;">Mutasi debet, kredit, dan saldo berjalan per akun buku besar.</p>
    </div>
    <div class="flex gap-1" style="flex-wrap:wrap">
      <a href="{{ route('reports.general-ledger.export-csv', request()->query()) }}" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-size:0.84rem;">
        <x-lucide-download style="width:15px; height:15px;" /> Export CSV
      </a>
      <a href="{{ route('reports.general-ledger.print', request()->query()) }}" target="_blank" class="btn btn-primary" style="text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px; font-size:0.84rem;">
        <x-lucide-printer style="width:15px; height:15px;" /> Cetak Buku Besar (A4)
      </a>
    </div>
  </div>

  <!-- Sleek Summary Metric Strip (Ramping & Simpel) -->
  <div style="display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap;">
    <div class="card" style="flex: 1; min-width: 180px; padding: 10px 16px; display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));">
      <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.12); color: #818cf8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <x-lucide-calendar style="width: 18px; height: 18px;" />
      </div>
      <div>
        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Periode Laporan</div>
        <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-primary);">
          {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </div>
      </div>
    </div>

    <div class="card" style="flex: 1; min-width: 160px; padding: 10px 16px; display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));">
      <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(59, 130, 246, 0.12); color: #60a5fa; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <x-lucide-layers style="width: 18px; height: 18px;" />
      </div>
      <div>
        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Akun Tampil</div>
        <div style="font-size: 0.88rem; font-weight: 700; color: var(--text-primary);">
          {{ count($ledgerData) }} Akun <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">({{ $activeAccountsCount }} bermutasi)</span>
        </div>
      </div>
    </div>

    <div class="card" style="flex: 1; min-width: 180px; padding: 10px 16px; display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));">
      <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(239, 68, 68, 0.12); color: #f87171; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <x-lucide-arrow-down-left style="width: 18px; height: 18px;" />
      </div>
      <div>
        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Total Mutasi Debet</div>
        <div style="font-size: 0.95rem; font-weight: 800; color: #f87171;" class="font-mono-num">
          Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
        </div>
      </div>
    </div>

    <div class="card" style="flex: 1; min-width: 180px; padding: 10px 16px; display: flex; align-items: center; gap: 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));">
      <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(34, 197, 94, 0.12); color: #4ade80; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <x-lucide-arrow-up-right style="width: 18px; height: 18px;" />
      </div>
      <div>
        <div style="font-size: 0.72rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Total Mutasi Kredit</div>
        <div style="font-size: 0.95rem; font-weight: 800; color: #4ade80;" class="font-mono-num">
          Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Bar yang Ramping & Bersih -->
  <form action="{{ route('reports.general-ledger') }}" method="GET" id="gl-filter-form" style="margin-bottom: 16px;">
    <div class="filter-bar" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <!-- Select Account -->
      <select class="form-select" name="category_id" id="gl-filter-category" style="min-width: 240px; font-size: 0.85rem;" onchange="document.getElementById('gl-filter-form').submit()">
        <option value="all" {{ $categoryId === 'all' ? 'selected' : '' }}>Semua Akun Buku Besar</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>
            [{{ $c->code }}] {{ $c->name }}
          </option>
        @endforeach
      </select>

      <!-- Select Job -->
      <select class="form-select" name="job_id" id="gl-filter-job" style="min-width: 160px; font-size: 0.85rem;" onchange="document.getElementById('gl-filter-form').submit()">
        <option value="all" {{ $jobId === 'all' ? 'selected' : '' }}>Semua Proyek (Jobs)</option>
        @foreach($jobs as $j)
          <option value="{{ $j->id }}" {{ $jobId == $j->id ? 'selected' : '' }}>
            [{{ $j->code }}] {{ $j->name }}
          </option>
        @endforeach
      </select>

      <!-- Date Range -->
      <div style="display: flex; align-items: center; gap: 6px;">
        <input type="date" class="form-input" name="start_date" id="gl-filter-from" value="{{ $startDate }}" style="width: auto; min-width: 125px; font-size: 0.85rem;" />
        <span style="color: var(--text-muted); font-size: 0.82rem;">s/d</span>
        <input type="date" class="form-input" name="end_date" id="gl-filter-to" value="{{ $endDate }}" style="width: auto; min-width: 125px; font-size: 0.85rem;" />
      </div>

      <!-- Toggle Sembunyikan Tabel Kosong (Pilihan User) -->
      <label style="display: inline-flex; align-items: center; gap: 7px; font-size: 0.82rem; color: var(--text-secondary); cursor: pointer; user-select: none; margin-left: 4px;">
        <input type="checkbox" name="only_active" value="1" {{ $onlyActive ? 'checked' : '' }} onchange="document.getElementById('gl-filter-form').submit()" style="cursor: pointer; width: 15px; height: 15px; accent-color: var(--color-primary, #3b82f6);" />
        <span>Sembunyikan akun tanpa mutasi</span>
      </label>

      <button type="submit" class="btn btn-secondary btn-sm" style="padding: 7px 14px; display: inline-flex; align-items: center; gap: 4px; font-size: 0.82rem; margin-left: auto;">
        <x-lucide-filter style="width: 14px; height: 14px;" /> Terapkan
      </button>
    </div>
  </form>

  <!-- Ledger Accounts -->
  @if(empty($ledgerData))
    <div class="card" style="text-align: center; padding: 48px 20px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08)); border-radius: 12px;">
      <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); color: var(--color-primary, #818cf8); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
        <x-lucide-book-open style="width: 26px; height: 26px;" />
      </div>
      <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">
        Tidak Ada Transaksi Mutasi pada Periode Ini
      </div>
      <div style="font-size: 0.85rem; color: var(--text-muted); max-width: 480px; margin: 0 auto 16px; line-height: 1.5;">
        Belum ada pergerakan transaksi kas/bank yang tercatat antara <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>.
      </div>
      @if($onlyActive)
        <div>
          <a href="{{ request()->fullUrlWithQuery(['only_active' => '0']) }}" class="btn btn-secondary btn-sm" style="text-decoration: none; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px;">
            <x-lucide-eye style="width: 14px; height: 14px;" /> Tampilkan Semua Akun (Termasuk Saldo Awal)
          </a>
        </div>
      @endif
    </div>
  @else
    @foreach($ledgerData as $account)
      @php
        $hasRows = !empty($account['transactions']);
      @endphp

      @if($hasRows)
        <!-- Akun yang MEMILIKI mutasi: Render tabel mutasi yang bersih dan ramping -->
        <div class="card mb-3" style="border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08)); border-radius: 10px; overflow: hidden; background: rgba(255, 255, 255, 0.02);">
          
          <!-- Compact Account Header Strip -->
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: rgba(15, 23, 42, 0.45); border-bottom: 1px solid rgba(255, 255, 255, 0.06); flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="badge" style="font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #a5b4fc; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); padding: 2px 7px; border-radius: 5px;">
                {{ $account['category']->code }}
              </span>
              <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary, #f8fafc);">
                {{ $account['category']->name }}
              </span>
              <span style="font-size: 0.72rem; color: {{ $account['is_debit_normal'] ? '#fca5a5' : '#86efac' }}; background: rgba(255, 255, 255, 0.04); padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                {{ $account['is_debit_normal'] ? 'Debet Normal' : 'Kredit Normal' }}
              </span>
            </div>

            <div style="display: flex; align-items: center; gap: 14px; font-size: 0.82rem; flex-wrap: wrap;">
              <span style="color: var(--text-secondary);">
                Saldo Awal: <strong class="font-mono-num" style="color: #cbd5e1;">Rp {{ number_format($account['beginning_balance'], 0, ',', '.') }}</strong>
              </span>
              <span style="color: var(--text-muted);">➔</span>
              <span style="color: var(--text-secondary);">
                Saldo Akhir: <strong class="font-mono-num" style="color: #38bdf8; font-weight: 800;">Rp {{ number_format($account['ending_balance'], 0, ',', '.') }}</strong>
              </span>
            </div>
          </div>

          <!-- Clean Transactions Table -->
          <div class="table-container" style="margin: 0; border: none; border-radius: 0;">
            <table class="data-table" style="font-size: 0.84rem; margin: 0;">
              <thead>
                <tr style="background: rgba(15, 23, 42, 0.25);">
                  <th style="width: 105px;">Tanggal</th>
                  <th style="width: 120px;">No. Bukti</th>
                  <th>Uraian / Deskripsi</th>
                  <th style="text-align: right; width: 125px; color: #f87171;">Debet (Rp)</th>
                  <th style="text-align: right; width: 125px; color: #4ade80;">Kredit (Rp)</th>
                  <th style="text-align: right; width: 145px; color: #38bdf8;">Saldo Berjalan</th>
                </tr>
              </thead>
              <tbody>
                <!-- Row Saldo Awal Periode -->
                <tr style="background: rgba(255, 255, 255, 0.015); font-style: italic; color: var(--text-muted);">
                  <td>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                  <td>—</td>
                  <td><span style="font-weight: 600;">Saldo Awal Periode</span></td>
                  <td style="text-align: right;">—</td>
                  <td style="text-align: right;">—</td>
                  <td style="text-align: right; font-family: monospace; font-weight: 700; color: #cbd5e1;">
                    Rp {{ number_format($account['beginning_balance'], 0, ',', '.') }}
                  </td>
                </tr>

                @foreach($account['transactions'] as $row)
                  <tr>
                    <td style="white-space: nowrap; color: var(--text-secondary);">
                      {{ $row['transaction']->date->format('d M Y') }}
                    </td>
                    <td>
                      <span style="font-family: monospace; font-size: 0.8rem; color: #a5b4fc; font-weight: 600;">
                        {{ $row['transaction']->voucher_number ?? '—' }}
                      </span>
                    </td>
                    <td>
                      <div style="display: flex; align-items: center; gap: 6px;">
                        <span>{{ $row['transaction']->description }}</span>
                        @if($row['transaction']->job)
                          <span style="font-size: 0.72rem; font-weight: 700; color: var(--accent-primary, #818cf8); background: rgba(99, 102, 241, 0.12); padding: 1px 5px; border-radius: 4px;">
                            [{{ $row['transaction']->job->code }}]
                          </span>
                        @endif
                      </div>
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #f87171;">
                      {{ $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '—' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #4ade80;">
                      {{ $row['credit'] > 0 ? number_format($row['credit'], 0, ',', '.') : '—' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #38bdf8;">
                      Rp {{ number_format($row['running_balance'], 0, ',', '.') }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr style="border-top: 1px solid rgba(255, 255, 255, 0.08); background: rgba(15, 23, 42, 0.35); font-weight: 700; font-size: 0.82rem;">
                  <td colspan="3" style="text-align: right; color: var(--text-muted); text-transform: uppercase;">
                    Total Mutasi Akun:
                  </td>
                  <td style="text-align: right; font-family: monospace; color: #f87171;">
                    Rp {{ number_format($account['total_debit'], 0, ',', '.') }}
                  </td>
                  <td style="text-align: right; font-family: monospace; color: #4ade80;">
                    Rp {{ number_format($account['total_credit'], 0, ',', '.') }}
                  </td>
                  <td style="text-align: right; font-family: monospace; color: #38bdf8; font-size: 0.88rem;">
                    Rp {{ number_format($account['ending_balance'], 0, ',', '.') }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      @else
        <!-- Akun TANPA mutasi (hanya tampil jika user mematikan 'Sembunyikan akun tanpa mutasi'): Render mini card 1 baris -->
        <div class="card mb-2" style="padding: 9px 16px; border: 1px solid var(--border-color, rgba(255, 255, 255, 0.06)); background: rgba(255, 255, 255, 0.015); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge" style="font-family: monospace; font-size: 0.78rem; font-weight: 700; color: #94a3b8; background: rgba(255, 255, 255, 0.06); padding: 2px 6px; border-radius: 4px;">
              {{ $account['category']->code }}
            </span>
            <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-secondary);">
              {{ $account['category']->name }}
            </span>
            <span style="font-size: 0.72rem; color: var(--text-muted); font-style: italic;">
              (Tidak ada transaksi mutasi)
            </span>
          </div>

          <div style="display: flex; align-items: center; gap: 14px; font-size: 0.8rem;">
            <span style="color: var(--text-muted);">
              Saldo Awal: <strong class="font-mono-num" style="color: #cbd5e1;">Rp {{ number_format($account['beginning_balance'], 0, ',', '.') }}</strong>
            </span>
            <span style="color: var(--text-muted);">|</span>
            <span style="color: var(--text-muted);">
              Saldo Akhir: <strong class="font-mono-num" style="color: #38bdf8;">Rp {{ number_format($account['ending_balance'], 0, ',', '.') }}</strong>
            </span>
          </div>
        </div>
      @endif
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
