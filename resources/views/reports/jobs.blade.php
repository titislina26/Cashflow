@extends('layouts.app')

@section('title', 'Laporan Proyek (Jobs) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <h2>Laporan Proyek (Jobs) 
        <span class="account-badge" data-account="{{ $activeAccount }}" style="display:inline-flex; align-items:center; gap:6px;">
          @if($activeAccount === 'petty_cash')
            <x-lucide-wallet style="width:14px; height:14px;" /> Kas Kecil
          @elseif($activeAccount === 'bank')
            <x-lucide-landmark style="width:14px; height:14px;" /> Bank
          @else
            <x-lucide-building-2 style="width:14px; height:14px;" /> Yayasan
          @endif
        </span>
      </h2>
      <p>Analisis profitabilitas dan transaksi per proyek/kegiatan</p>
    </div>
  </div>

  <!-- Tabs Navigation -->
  @php $activeTab = request('tab', 'summary'); @endphp
  <div class="tabs">
    <button class="tab-btn {{ $activeTab === 'summary' ? 'active' : '' }}" data-tab="summary" style="display:inline-flex; align-items:center; gap:6px;">
      <x-lucide-bar-chart-2 style="width:15px; height:15px;" /> Activity Summary
    </button>
    <button class="tab-btn {{ $activeTab === 'pnl' ? 'active' : '' }}" data-tab="pnl" style="display:inline-flex; align-items:center; gap:6px;">
      <x-lucide-trending-up style="width:15px; height:15px;" /> Surplus Defisit
    </button>
    <button class="tab-btn {{ $activeTab === 'transactions' ? 'active' : '' }}" data-tab="transactions" style="display:inline-flex; align-items:center; gap:6px;">
      <x-lucide-receipt style="width:15px; height:15px;" /> Job Transactions
    </button>
  </div>

  <!-- Hidden form to reload with correct tab and job filter -->
  <form action="{{ route('reports.jobs') }}" method="GET" id="report-filter-form" style="display:none">
    <input type="hidden" name="tab" id="filter-tab" value="{{ $activeTab }}">
    <input type="hidden" name="job_id" id="filter-job-id" value="{{ $selectedJobId }}">
  </form>

  <!-- TAB 1: Activity Summary -->
  <div id="tab-content-summary" class="report-tab-content" style="display: {{ $activeTab === 'summary' ? 'block' : 'none' }}">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-briefcase style="width:18px; height:18px; color:var(--accent-primary);" />
          Ringkasan Aktivitas Proyek (All Jobs)
        </h3>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 100px">Kode</th>
              <th>Nama Proyek</th>
              <th style="text-align: right">Pemasukan (In)</th>
              <th style="text-align: right">Pengeluaran (Out)</th>
              <th style="text-align: right">Selisih Bersih (Net)</th>
              <th style="width: 110px; text-align: center">Status</th>
            </tr>
          </thead>
          <tbody>
            @php 
              $totalIn = 0;
              $totalOut = 0;
            @endphp
            @if(empty($activitySummary))
              <tr>
                <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted)">Belum ada data proyek.</td>
              </tr>
            @else
              @foreach($activitySummary as $act)
                @php
                  $totalIn += $act['income'];
                  $totalOut += $act['expense'];
                  $net = $act['net'];
                @endphp
                <tr>
                  <td style="font-weight: 700; color: var(--accent-primary)" class="font-mono-num">{{ $act['job']->code }}</td>
                  <td style="font-weight: 600">{{ $act['job']->name }}</td>
                  <td style="text-align: right" class="text-income font-mono-num tabular-nums font-bold">Rp {{ number_format($act['income'], 0, ',', '.') }}</td>
                  <td style="text-align: right" class="text-expense font-mono-num tabular-nums font-bold">Rp {{ number_format($act['expense'], 0, ',', '.') }}</td>
                  <td style="text-align: right; font-weight: 700; color: {{ $net >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                    {{ $net >= 0 ? '+' : '' }}Rp {{ number_format($net, 0, ',', '.') }}
                  </td>
                  <td style="text-align: center">
                    <span class="badge" style="background: {{ $act['job']->status === 'active' ? 'var(--color-income-bg)' : 'rgba(255,255,255,0.05)' }}; color: {{ $act['job']->status === 'active' ? 'var(--color-income)' : 'var(--text-muted)' }}; display:inline-flex; align-items:center; gap:4px;">
                      @if($act['job']->status === 'active')
                        <x-lucide-check-circle style="width:12px; height:12px;" />
                      @else
                        <x-lucide-circle-off style="width:12px; height:12px;" />
                      @endif
                      {{ $act['job']->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                </tr>
              @endforeach
              <tr style="border-top: 2px solid var(--border-primary); font-weight: 800; background: rgba(255,255,255,0.02)">
                <td colspan="2">TOTAL KESELURUHAN</td>
                <td style="text-align: right" class="text-income font-mono-num tabular-nums">Rp {{ number_format($totalIn, 0, ',', '.') }}</td>
                <td style="text-align: right" class="text-expense font-mono-num tabular-nums">Rp {{ number_format($totalOut, 0, ',', '.') }}</td>
                @php $totalNet = $totalIn - $totalOut; @endphp
                <td style="text-align: right; color: {{ $totalNet >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                  {{ $totalNet >= 0 ? '+' : '' }}Rp {{ number_format($totalNet, 0, ',', '.') }}
                </td>
                <td></td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB 2: Profit & Loss per Job -->
  <div id="tab-content-pnl" class="report-tab-content" style="display: {{ $activeTab === 'pnl' ? 'block' : 'none' }}">
    <div class="flex-between mb-2">
      <div style="display:flex; align-items:center; gap:8px">
        <label class="form-label" style="margin-bottom:0; font-weight:600">Pilih Proyek:</label>
        <select class="form-select job-selector" style="min-width: 250px">
          @foreach($jobs as $j)
            <option value="{{ $j->id }}" {{ $selectedJobId == $j->id ? 'selected' : '' }}>[{{ $j->code }}] {{ $j->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    @if(!$selectedJob)
      <div class="card" style="padding: 40px 0; text-align: center;">
        <div class="empty-state-title" style="color: var(--text-secondary)">Tidak ada proyek aktif</div>
      </div>
    @else
      <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header" style="text-align: center; border-bottom: 2px solid var(--border-primary); padding-bottom: 15px;">
          <h3 style="font-size: 1.3rem; color: var(--text-primary)">LAPORAN SURPLUS DEFISIT PROYEK (ISAK 335)</h3>
          <h4 style="color: var(--accent-primary); font-family:'Outfit'; margin-top:4px">[{{ $selectedJob->code }}] {{ $selectedJob->name }}</h4>
        </div>
        
        <div class="table-container" style="padding: 15px 10px;">
          <table class="data-table" style="border: none;">
            <tbody>
              <!-- INCOME -->
              <tr style="background: rgba(16, 185, 129, 0.05)">
                <td colspan="2" style="font-weight: 700; color: var(--color-income)">PEMASUKAN (INCOME)</td>
              </tr>
              @if(empty($jobPnlIncome))
                <tr>
                  <td style="padding-left: 20px; color: var(--text-muted); font-style: italic">Tidak ada pendapatan tercatat</td>
                  <td style="text-align: right; color: var(--text-muted)" class="font-mono-num tabular-nums">Rp 0</td>
                </tr>
              @else
                @foreach($jobPnlIncome as $item)
                  <tr>
                    <td style="padding-left: 25px; color: var(--text-secondary)">
                      <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $item['color'] ?? '#10b981' }}; margin-right:8px;"></span>
                      {{ $item['name'] }}
                    </td>
                    <td style="text-align: right; font-weight: 500; color: var(--text-primary)" class="font-mono-num tabular-nums">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                  </tr>
                @endforeach
              @endif
              <tr style="font-weight: 700; background: rgba(255,255,255,0.01); border-top: 1px dashed var(--border-primary)">
                <td style="padding-left: 20px;">Total Pemasukan Proyek</td>
                <td style="text-align: right; color: var(--color-income)" class="font-mono-num tabular-nums">Rp {{ number_format($totalJobIncome, 0, ',', '.') }}</td>
              </tr>

              <!-- SPACING -->
              <tr style="height: 20px; background: none"><td colspan="2" style="border: none"></td></tr>

              <!-- EXPENSES -->
              <tr style="background: rgba(244, 63, 94, 0.05)">
                <td colspan="2" style="font-weight: 700; color: var(--color-expense)">PENGELUARAN (EXPENSES)</td>
              </tr>
              @if(empty($jobPnlExpense))
                <tr>
                  <td style="padding-left: 20px; color: var(--text-muted); font-style: italic">Tidak ada pengeluaran tercatat</td>
                  <td style="text-align: right; color: var(--text-muted)" class="font-mono-num tabular-nums">Rp 0</td>
                </tr>
              @else
                @foreach($jobPnlExpense as $item)
                  <tr>
                    <td style="padding-left: 25px; color: var(--text-secondary)">
                      <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $item['color'] ?? '#f43f5e' }}; margin-right:8px;"></span>
                      {{ $item['name'] }}
                    </td>
                    <td style="text-align: right; font-weight: 500; color: var(--text-primary)" class="font-mono-num tabular-nums">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                  </tr>
                @endforeach
              @endif
              <tr style="font-weight: 700; background: rgba(255,255,255,0.01); border-top: 1px dashed var(--border-primary)">
                <td style="padding-left: 20px;">Total Pengeluaran Proyek</td>
                <td style="text-align: right; color: var(--color-expense)" class="font-mono-num tabular-nums">Rp {{ number_format($totalJobExpense, 0, ',', '.') }}</td>
              </tr>

              <!-- SPACING -->
              <tr style="height: 30px; background: none"><td colspan="2" style="border: none"></td></tr>

              <!-- NET SURPLUS / DEFISIT -->
              <tr style="font-weight: 800; background: rgba(255,255,255,0.03); border-top: 2px solid var(--border-primary)">
                <td style="font-size: 1.05rem;">SURPLUS / (DEFISIT) BERSIH PROYEK</td>
                <td style="text-align: right; color: {{ $jobNetProfit >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}; font-size: 1.1rem" class="font-mono-num tabular-nums">
                  {{ $jobNetProfit >= 0 ? '+' : '' }}Rp {{ number_format($jobNetProfit, 0, ',', '.') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>

  <!-- TAB 3: Job Transactions -->
  <div id="tab-content-transactions" class="report-tab-content" style="display: {{ $activeTab === 'transactions' ? 'block' : 'none' }}">
    <div class="flex-between mb-2">
      <div style="display:flex; align-items:center; gap:8px">
        <label class="form-label" style="margin-bottom:0; font-weight:600">Pilih Proyek:</label>
        <select class="form-select job-selector" style="min-width: 250px">
          @foreach($jobs as $j)
            <option value="{{ $j->id }}" {{ $selectedJobId == $j->id ? 'selected' : '' }}>[{{ $j->code }}] {{ $j->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-receipt style="width:18px; height:18px; color:var(--accent-primary);" />
          Daftar Transaksi Kas Proyek
        </h3>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Sumber Dana</th>
              <th>Kategori</th>
              <th>Deskripsi</th>
              <th>Tipe</th>
              <th style="text-align: right">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @if(!$selectedJob || $jobTransactions->isEmpty())
              <tr>
                <td colspan="6" style="text-align:center; padding: 40px 0;">
                  <div class="empty-state-title" style="color: var(--text-secondary)">Tidak ada transaksi tercatat untuk proyek ini.</div>
                </td>
              </tr>
            @else
              @foreach($jobTransactions as $tx)
                <tr>
                  <td>{{ $tx->date->format('d M Y') }}</td>
                  <td>
                    <span class="badge" style="background: {{ $tx->account === 'petty_cash' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(99, 102, 241, 0.1)' }}; color: {{ $tx->account === 'petty_cash' ? 'var(--color-warning)' : 'var(--accent-secondary)' }}; display:inline-flex; align-items:center; gap:4px;">
                      @if($tx->account === 'petty_cash')
                        <x-lucide-wallet style="width:12px; height:12px;" />
                      @else
                        <x-lucide-landmark style="width:12px; height:12px;" />
                      @endif
                      {{ $tx->account === 'petty_cash' ? 'Kas Kecil' : 'Bank' }}
                    </span>
                  </td>
                  <td>
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $tx->category->color ?? '#818cf8' }}; margin-right:6px;"></span>
                    {{ $tx->category->name }}
                  </td>
                  <td>{{ $tx->description ?? '—' }}</td>
                  <td>
                    <span class="badge badge-{{ $tx->type }}">
                      {{ $tx->type === 'income' ? 'Masuk' : 'Keluar' }}
                    </span>
                  </td>
                  <td style="text-align: right; font-weight: 700; color: {{ $tx->type === 'income' ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                    {{ $tx->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>

      <!-- Pagination for Job Transactions -->
      @if($selectedJob && $jobTransactions->hasPages())
        <div class="pagination">
          @if ($jobTransactions->onFirstPage())
            <span class="pagination-btn disabled"><x-lucide-chevron-left style="width:14px; height:14px;" /></span>
          @else
            <a href="javascript:void(0)" class="pagination-btn pg-link" data-url="{{ $jobTransactions->previousPageUrl() }}"><x-lucide-chevron-left style="width:14px; height:14px;" /></a>
          @endif

          @for ($i = 1; $i <= $jobTransactions->lastPage(); $i++)
            @if ($i == $jobTransactions->currentPage())
              <span class="pagination-btn active">{{ $i }}</span>
            @else
              <a href="javascript:void(0)" class="pagination-btn pg-link" data-url="{{ $jobTransactions->url($i) }}">{{ $i }}</a>
            @endif
          @endfor

          @if ($jobTransactions->hasMorePages())
            <a href="javascript:void(0)" class="pagination-btn pg-link" data-url="{{ $jobTransactions->nextPageUrl() }}"><x-lucide-chevron-right style="width:14px; height:14px;" /></a>
          @else
            <span class="pagination-btn disabled"><x-lucide-chevron-right style="width:14px; height:14px;" /></span>
          @endif

          <span class="pagination-info">{{ $jobTransactions->total() }} transaksi</span>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) { lucide.createIcons(); }
    const tabInput = document.getElementById('filter-tab');
    const jobInput = document.getElementById('filter-job-id');
    const filterForm = document.getElementById('report-filter-form');

    // --- Tab Switching ---
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.report-tab-content');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const targetTab = tab.dataset.tab;
        tabInput.value = targetTab;

        contents.forEach(content => {
          if (content.id === 'tab-content-' + targetTab) {
            content.style.display = 'block';
          } else {
            content.style.display = 'none';
          }
        });
      });
    });

    // --- Job Selector ---
    document.querySelectorAll('.job-selector').forEach(selector => {
      selector.addEventListener('change', function() {
        jobInput.value = this.value;
        filterForm.submit();
      });
    });

    // --- Pagination Catcher ---
    document.querySelectorAll('.pg-link').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const urlStr = this.dataset.url;
        if (urlStr) {
          // Parse url query parameters to merge
          const url = new URL(urlStr);
          const page = url.searchParams.get('page');
          
          // Build reload URL
          const currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set('page', page);
          currentUrl.searchParams.set('tab', tabInput.value);
          currentUrl.searchParams.set('job_id', jobInput.value);
          
          window.location.href = currentUrl.toString();
        }
      });
    });
  });
</script>
@endsection
