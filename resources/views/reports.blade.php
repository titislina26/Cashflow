@extends('layouts.app')

@section('title', 'Laporan Keuangan — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <h2>Laporan 
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
      <p>Analisis keuangan & laporan arus kas</p>
    </div>
    <div class="flex gap-1">
      <select class="form-select font-mono-num" id="report-year" style="min-width:120px">
        @foreach($availableYears as $y)
          <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
      </select>
      <button class="btn btn-secondary" id="btn-export-report" style="display:inline-flex; align-items:center; gap:6px;">
        <x-lucide-download style="width:16px; height:16px;" /> Export CSV
      </button>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab-btn active" data-tab="monthly" style="display:inline-flex; align-items:center; gap:6px;">
      <x-lucide-calendar style="width:15px; height:15px;" /> Bulanan
    </button>
    <button class="tab-btn" data-tab="category" style="display:inline-flex; align-items:center; gap:6px;">
      <x-lucide-folder-tree style="width:15px; height:15px;" /> Per Kategori
    </button>
    <button class="tab-btn" data-tab="trend" style="display:inline-flex; align-items:center; gap:6px;">
      <x-lucide-trending-up style="width:15px; height:15px;" /> Tren
    </button>
  </div>

  <!-- Tab Content: Bulanan -->
  <div id="tab-content-monthly" class="report-tab-content">
    <!-- Summary -->
    <div class="summary-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:24px">
      <div class="summary-card income">
        <div class="summary-card-header">
          <span class="summary-card-label">Total Pemasukan {{ $year }}</span>
          <div class="summary-card-icon" style="display:flex; align-items:center; justify-content:center;">
            <x-lucide-arrow-down-left style="width:20px; height:20px; color:#22c55e;" />
          </div>
        </div>
        <div class="summary-card-value font-mono-num tabular-nums">Rp {{ number_format($totalIncomeYear, 0, ',', '.') }}</div>
      </div>
      <div class="summary-card expense">
        <div class="summary-card-header">
          <span class="summary-card-label">Total Pengeluaran {{ $year }}</span>
          <div class="summary-card-icon" style="display:flex; align-items:center; justify-content:center;">
            <x-lucide-arrow-up-right style="width:20px; height:20px; color:#ef4444;" />
          </div>
        </div>
        <div class="summary-card-value font-mono-num tabular-nums">Rp {{ number_format($totalExpenseYear, 0, ',', '.') }}</div>
      </div>
      <div class="summary-card balance">
        <div class="summary-card-header">
          <span class="summary-card-label">Netto {{ $year }}</span>
          <div class="summary-card-icon" style="display:flex; align-items:center; justify-content:center;">
            <x-lucide-scale style="width:20px; height:20px; color:#6366f1;" />
          </div>
        </div>
        @php $netYear = $totalIncomeYear - $totalExpenseYear; @endphp
        <div class="summary-card-value font-mono-num tabular-nums">Rp {{ number_format($netYear, 0, ',', '.') }}</div>
      </div>
    </div>

    <!-- Monthly Table -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-calendar style="width:18px; height:18px; color:var(--accent-primary);" />
          Ringkasan Bulanan — {{ $year }}
        </h3>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Bulan</th>
              <th style="text-align:right">Pemasukan</th>
              <th style="text-align:right">Pengeluaran</th>
              <th style="text-align:right">Netto</th>
              <th style="text-align:right">Saldo Kumulatif</th>
            </tr>
          </thead>
          <tbody>
            @php $totalTx = 0; @endphp
            @foreach($monthlyReport as $m)
              <tr>
                <td style="font-weight:600">{{ $m['month_name'] }}</td>
                <td style="text-align:right" class="text-income font-mono-num tabular-nums font-bold">Rp {{ number_format($m['income'], 0, ',', '.') }}</td>
                <td style="text-align:right" class="text-expense font-mono-num tabular-nums font-bold">Rp {{ number_format($m['expense'], 0, ',', '.') }}</td>
                <td style="text-align:right; font-weight:700; color:{{ $m['net'] >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                  {{ $m['net'] >= 0 ? '+' : '' }}Rp {{ number_format($m['net'], 0, ',', '.') }}
                </td>
                <td style="text-align:right; font-weight:600; color:{{ $m['cumulative'] >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                  Rp {{ number_format($m['cumulative'], 0, ',', '.') }}
                </td>
              </tr>
            @endforeach
            <tr style="border-top:2px solid var(--border-primary); font-weight:800; background: rgba(255,255,255,0.02)">
              <td>TOTAL</td>
              <td style="text-align:right" class="text-income font-mono-num tabular-nums">Rp {{ number_format($totalIncomeYear, 0, ',', '.') }}</td>
              <td style="text-align:right" class="text-expense font-mono-num tabular-nums">Rp {{ number_format($totalExpenseYear, 0, ',', '.') }}</td>
              <td style="text-align:right; color:{{ $netYear >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                {{ $netYear >= 0 ? '+' : '' }}Rp {{ number_format($netYear, 0, ',', '.') }}
              </td>
              <td style="text-align:right; color:{{ end($monthlyReport)['cumulative'] >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                Rp {{ number_format(end($monthlyReport)['cumulative'], 0, ',', '.') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tab Content: Kategori -->
  <div id="tab-content-category" class="report-tab-content" style="display:none">
    <div class="charts-grid" style="margin-bottom:24px">
      <!-- Income by category -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
            <x-lucide-arrow-down-left style="width:18px; height:18px; color:#22c55e;" />
            Pemasukan per Kategori
          </h3>
        </div>
        <div class="chart-container" style="height:280px" id="report-income-chart-wrapper">
          @if(count($categoryIncomeAnalysis) > 0)
            <canvas id="chart-report-income"></canvas>
          @else
            <div class="empty-state" style="padding:40px 0">
              <div class="empty-state-icon" style="display:flex; justify-content:center; margin-bottom:10px;">
                <x-lucide-pie-chart style="width:36px; height:36px; color:var(--text-muted); stroke-width:1.5;" />
              </div>
              <div class="empty-state-desc">Belum ada data pemasukan tahun ini</div>
            </div>
          @endif
        </div>
      </div>

      <!-- Expense by category -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
            <x-lucide-arrow-up-right style="width:18px; height:18px; color:#ef4444;" />
            Pengeluaran per Kategori
          </h3>
        </div>
        <div class="chart-container" style="height:280px" id="report-expense-chart-wrapper">
          @if(count($categoryExpenseAnalysis) > 0)
            <canvas id="chart-report-expense"></canvas>
          @else
            <div class="empty-state" style="padding:40px 0">
              <div class="empty-state-icon" style="display:flex; justify-content:center; margin-bottom:10px;">
                <x-lucide-pie-chart style="width:36px; height:36px; color:var(--text-muted); stroke-width:1.5;" />
              </div>
              <div class="empty-state-desc">Belum ada data pengeluaran tahun ini</div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Category Details Tables -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; flex-wrap:wrap">
      <!-- Income Category Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
            <x-lucide-arrow-down-left style="width:16px; height:16px; color:#22c55e;" />
            Detail Pemasukan
          </h3>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width:28px"></th>
                <th style="width:95px">Kode</th>
                <th>Kategori</th>
                <th style="text-align:right">Total</th>
                <th style="text-align:right; width:90px">Persentase</th>
              </tr>
            </thead>
            <tbody>
              @if(empty($categoryIncomeAnalysis))
                <tr>
                  <td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted)">Tidak ada data pemasukan</td>
                </tr>
              @else
                @foreach($categoryIncomeAnalysis as $cat)
                  <tr>
                    <td style="text-align:center;">
                      <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $cat['color'] ?? '#10b981' }};"></span>
                    </td>
                    <td>
                      @if(!empty($cat['code']))
                        <span class="badge font-mono-num" style="font-size: 0.78rem; font-weight: 700; color: #a5b4fc; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.28); padding: 2px 6px; border-radius: 4px;">
                          {{ $cat['code'] }}
                        </span>
                      @else
                        <span style="color: var(--text-muted); font-size: 0.8rem;">—</span>
                      @endif
                    </td>
                    <td style="font-weight:600">{{ $cat['name'] }}</td>
                    <td style="text-align:right" class="font-bold text-income font-mono-num tabular-nums">Rp {{ number_format($cat['amount'], 0, ',', '.') }}</td>
                    <td style="text-align:right">
                      <div class="badge badge-income font-mono-num tabular-nums" style="font-weight:600; display:inline-block; min-width:55px">
                        {{ number_format($cat['percentage'], 1) }}%
                      </div>
                    </td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
      </div>

      <!-- Expense Category Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
            <x-lucide-arrow-up-right style="width:16px; height:16px; color:#ef4444;" />
            Detail Pengeluaran
          </h3>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width:28px"></th>
                <th style="width:95px">Kode</th>
                <th>Kategori</th>
                <th style="text-align:right">Total</th>
                <th style="text-align:right; width:90px">Persentase</th>
              </tr>
            </thead>
            <tbody>
              @if(empty($categoryExpenseAnalysis))
                <tr>
                  <td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted)">Tidak ada data pengeluaran</td>
                </tr>
              @else
                @foreach($categoryExpenseAnalysis as $cat)
                  <tr>
                    <td style="text-align:center;">
                      <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $cat['color'] ?? '#f43f5e' }};"></span>
                    </td>
                    <td>
                      @if(!empty($cat['code']))
                        <span class="badge font-mono-num" style="font-size: 0.78rem; font-weight: 700; color: #f43f5e; background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.28); padding: 2px 6px; border-radius: 4px;">
                          {{ $cat['code'] }}
                        </span>
                      @else
                        <span style="color: var(--text-muted); font-size: 0.8rem;">—</span>
                      @endif
                    </td>
                    <td style="font-weight:600">{{ $cat['name'] }}</td>
                    <td style="text-align:right" class="font-bold text-expense font-mono-num tabular-nums">Rp {{ number_format($cat['amount'], 0, ',', '.') }}</td>
                    <td style="text-align:right">
                      <div class="badge badge-expense font-mono-num tabular-nums" style="font-weight:600; display:inline-block; min-width:55px">
                        {{ number_format($cat['percentage'], 1) }}%
                      </div>
                    </td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Tab Content: Tren -->
  <div id="tab-content-trend" class="report-tab-content" style="display:none">
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-trending-up style="width:18px; height:18px; color:var(--accent-primary);" />
          Tren Arus Kas — {{ $year }}
        </h3>
      </div>
      <div class="chart-container" style="height:350px">
        <canvas id="chart-trend"></canvas>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-line-chart style="width:18px; height:18px; color:var(--accent-primary);" />
          Kumulatif Arus Kas — {{ $year }}
        </h3>
      </div>
      <div class="chart-container" style="height:300px">
        <canvas id="chart-cumulative"></canvas>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) { lucide.createIcons(); }

    // --- Year Switcher ---
    document.getElementById('report-year').addEventListener('change', function () {
      window.location.href = "{{ route('reports.index') }}?year=" + this.value;
    });

    // --- Tab Switching Logic ---
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.report-tab-content');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const targetTab = tab.dataset.tab;
        contents.forEach(content => {
          if (content.id === 'tab-content-' + targetTab) {
            content.style.display = '';
          } else {
            content.style.display = 'none';
          }
        });
      });
    });

    // Formatting helper
    function formatCurrency(val) {
      return 'Rp ' + Number(val).toLocaleString('id-ID');
    }

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: {
          position: 'top',
          labels: {
            color: '#a0a0c0',
            usePointStyle: true,
            padding: 20,
            font: { family: 'Inter', size: 12 }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(17, 17, 40, 0.95)',
          titleColor: '#f0f0f8',
          bodyColor: '#a0a0c0',
          padding: 12,
          cornerRadius: 8,
          callbacks: {
            label: (ctx) => `${ctx.dataset.label}: ${formatCurrency(ctx.raw)}`
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(255,255,255,0.04)' },
          ticks: { color: '#6b6b8d', font: { family: 'Inter', size: 11 } }
        },
        y: {
          grid: { color: 'rgba(255,255,255,0.04)' },
          ticks: {
            color: '#6b6b8d',
            font: { family: 'Inter', size: 11 },
            callback: (val) => formatCurrency(val)
          }
        }
      }
    };

    // --- Initialize Charts ---
    
    // 1. Category Income Doughnut
    const incomeCtx = document.getElementById('chart-report-income');
    if (incomeCtx) {
      const data = {!! json_encode($categoryIncomeAnalysis) !!};
      new Chart(incomeCtx, {
        type: 'doughnut',
        data: {
          labels: data.map(c => c.name),
          datasets: [{
            data: data.map(c => c.amount),
            backgroundColor: data.map(c => c.color),
            borderColor: 'rgba(10, 10, 26, 0.8)',
            borderWidth: 2,
            hoverOffset: 8,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '65%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: { color: '#a0a0c0', usePointStyle: true, padding: 12, font: { family: 'Inter', size: 11 } }
            },
            tooltip: {
              backgroundColor: 'rgba(17, 17, 40, 0.95)',
              titleColor: '#f0f0f8',
              bodyColor: '#a0a0c0',
              padding: 12,
              cornerRadius: 8,
              callbacks: { label: (ctx) => `${ctx.label}: ${formatCurrency(ctx.raw)}` }
            }
          }
        }
      });
    }

    // 2. Category Expense Doughnut
    const expenseCtx = document.getElementById('chart-report-expense');
    if (expenseCtx) {
      const data = {!! json_encode($categoryExpenseAnalysis) !!};
      new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
          labels: data.map(c => c.name),
          datasets: [{
            data: data.map(c => c.amount),
            backgroundColor: data.map(c => c.color),
            borderColor: 'rgba(10, 10, 26, 0.8)',
            borderWidth: 2,
            hoverOffset: 8,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '65%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: { color: '#a0a0c0', usePointStyle: true, padding: 12, font: { family: 'Inter', size: 11 } }
            },
            tooltip: {
              backgroundColor: 'rgba(17, 17, 40, 0.95)',
              titleColor: '#f0f0f8',
              bodyColor: '#a0a0c0',
              padding: 12,
              cornerRadius: 8,
              callbacks: { label: (ctx) => `${ctx.label}: ${formatCurrency(ctx.raw)}` }
            }
          }
        }
      });
    }

    // Month Short names array
    const monthLabels = {!! json_encode(array_map(function($m) { return substr($m['month_name'], 0, 3); }, $monthlyReport)) !!};

    // 3. Trend Line Chart
    const trendCtx = document.getElementById('chart-trend');
    if (trendCtx) {
      new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: monthLabels,
          datasets: [
            {
              label: 'Pemasukan',
              data: {!! json_encode($incomeTrend) !!},
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.05)',
              borderWidth: 2.5,
              tension: 0.4,
              fill: true,
              pointRadius: 4,
              pointBackgroundColor: '#10b981',
            },
            {
              label: 'Pengeluaran',
              data: {!! json_encode($expenseTrend) !!},
              borderColor: '#f43f5e',
              backgroundColor: 'rgba(244, 63, 94, 0.05)',
              borderWidth: 2.5,
              tension: 0.4,
              fill: true,
              pointRadius: 4,
              pointBackgroundColor: '#f43f5e',
            },
            {
              label: 'Netto',
              data: {!! json_encode($netTrend) !!},
              borderColor: '#8b5cf6',
              borderWidth: 2,
              borderDash: [5, 5],
              tension: 0.4,
              fill: false,
              pointRadius: 3,
              pointBackgroundColor: '#8b5cf6',
            }
          ]
        },
        options: chartOptions
      });
    }

    // 4. Cumulative Line Chart
    const cumCtx = document.getElementById('chart-cumulative');
    if (cumCtx) {
      const monthlyData = {!! json_encode($monthlyReport) !!};
      new Chart(cumCtx, {
        type: 'line',
        data: {
          labels: monthLabels,
          datasets: [
            {
              label: 'Kum. Netto',
              data: monthlyData.map(m => m.cumulative),
              borderColor: '#8b5cf6',
              backgroundColor: 'rgba(139, 92, 246, 0.08)',
              borderWidth: 2.5,
              tension: 0.4,
              fill: true,
              pointRadius: 4,
              pointBackgroundColor: '#8b5cf6',
            }
          ]
        },
        options: chartOptions
      });
    }

    // --- CSV Export Logic ---
    document.getElementById('btn-export-report').addEventListener('click', function () {
      const data = {!! json_encode($monthlyReport) !!};
      const csvContent = [];
      csvContent.push('Bulan,Pemasukan,Pengeluaran,Netto,Kumulatif');
      
      data.forEach(m => {
        csvContent.push(`"${m.month_name}",${m.income},${m.expense},${m.net},${m.cumulative}`);
      });

      const csvString = '\uFEFF' + csvContent.join('\n'); // Include UTF-8 BOM
      const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', `laporan_cashflow_{{ $activeAccount }}_{{ $year }}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  });
</script>
@endsection
