@extends('layouts.app')

@section('title', 'Proyeksi Keuangan — Cashflow Management')

@section('content')
<div class="page-content">
  @if(isset($insufficientData) && $insufficientData)
    <div class="page-header">
      <div>
        <h2>Proyeksi 
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
        <p>Proyeksi arus kas berdasarkan data historis</p>
      </div>
    </div>
    <div class="card">
      <div class="empty-state">
        <div class="empty-state-icon" style="display:flex; justify-content:center; margin-bottom:12px;">
          <x-lucide-sparkles style="width:44px; height:44px; color:var(--text-muted); stroke-width:1.5;" />
        </div>
        <div class="empty-state-title">Data belum cukup</div>
        <div class="empty-state-desc">Dibutuhkan minimal 3 bulan data transaksi untuk membuat proyeksi. Tambahkan lebih banyak transaksi atau muat data contoh di sidebar.</div>
      </div>
    </div>
  @else
    <div class="page-header">
      <div>
        <h2>Proyeksi 
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
        <p>Proyeksi arus kas {{ $projectionMonths }} bulan ke depan berdasarkan data historis</p>
      </div>
      <div class="flex gap-1">
        <select class="form-select" id="projection-months" style="min-width:140px">
          <option value="3" {{ $projectionMonths == 3 ? 'selected' : '' }}>3 Bulan</option>
          <option value="6" {{ $projectionMonths == 6 ? 'selected' : '' }}>6 Bulan</option>
          <option value="12" {{ $projectionMonths == 12 ? 'selected' : '' }}>12 Bulan</option>
        </select>
      </div>
    </div>

    <!-- Scenario Cards -->
    <div class="scenario-grid">
      <div class="scenario-card optimistic">
        <div class="scenario-card-label" style="display:flex; align-items:center; gap:6px;">
          <x-lucide-trending-up style="width:16px; height:16px; color:#22c55e;" /> Optimistik
        </div>
        <div class="scenario-card-value font-mono-num tabular-nums">Rp {{ number_format($optCumulative, 0, ',', '.') }}</div>
        <div class="scenario-card-desc">
          Saldo kumulatif {{ $projectionMonths }} bulan (best case)
        </div>
      </div>
      <div class="scenario-card realistic">
        <div class="scenario-card-label" style="display:flex; align-items:center; gap:6px;">
          <x-lucide-activity style="width:16px; height:16px; color:#3b82f6;" /> Realistik
        </div>
        <div class="scenario-card-value font-mono-num tabular-nums">Rp {{ number_format($realCumulative, 0, ',', '.') }}</div>
        <div class="scenario-card-desc">
          Saldo kumulatif {{ $projectionMonths }} bulan (most likely)
        </div>
      </div>
      <div class="scenario-card pessimistic">
        <div class="scenario-card-label" style="display:flex; align-items:center; gap:6px;">
          <x-lucide-trending-down style="width:16px; height:16px; color:#ef4444;" /> Pesimistik
        </div>
        <div class="scenario-card-value font-mono-num tabular-nums">Rp {{ number_format($pesCumulative, 0, ',', '.') }}</div>
        <div class="scenario-card-desc">
          Saldo kumulatif {{ $projectionMonths }} bulan (worst case)
        </div>
      </div>
    </div>

    <!-- Projection Chart -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-line-chart style="width:18px; height:18px; color:var(--accent-primary);" />
          Grafik Proyeksi Arus Kas
        </h3>
      </div>
      <div class="chart-container" style="height:380px">
        <canvas id="chart-projection"></canvas>
      </div>
    </div>

    <!-- Projection Details Table -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-calendar style="width:18px; height:18px; color:var(--accent-primary);" />
          Detail Proyeksi per Bulan (Skema Realistik)
        </h3>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Bulan</th>
              <th style="text-align:right">Pemasukan (Est.)</th>
              <th style="text-align:right">Pengeluaran (Est.)</th>
              <th style="text-align:right">Netto (Est.)</th>
              <th style="text-align:right">Saldo Kumulatif</th>
            </tr>
          </thead>
          <tbody>
            @foreach($realistic as $m)
              <tr>
                <td style="font-weight:600">{{ $m['label'] }}</td>
                <td style="text-align:right" class="text-income font-mono-num tabular-nums">Rp {{ number_format($m['income'], 0, ',', '.') }}</td>
                <td style="text-align:right" class="text-expense font-mono-num tabular-nums">Rp {{ number_format($m['expense'], 0, ',', '.') }}</td>
                <td style="text-align:right; font-weight:700; color:{{ $m['net'] >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                  {{ $m['net'] >= 0 ? '+' : '' }}Rp {{ number_format($m['net'], 0, ',', '.') }}
                </td>
                <td style="text-align:right; font-weight:600; color:{{ $m['cumulative'] >= 0 ? 'var(--color-income)' : 'var(--color-expense)' }}" class="font-mono-num tabular-nums">
                  Rp {{ number_format($m['cumulative'], 0, ',', '.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Methodology -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-calculator style="width:18px; height:18px; color:var(--accent-primary);" />
          Metodologi
        </h3>
      </div>
      <div style="color:var(--text-secondary); font-size:0.85rem; line-height:1.8">
        <p><strong>Realistik:</strong> Berdasarkan rata-rata pemasukan dan pengeluaran dari data historis yang tersedia serta garis tren linier.</p>
        <p class="mt-1"><strong>Optimistik:</strong> Estimasi kenaikan pemasukan +20% dan penurunan pengeluaran -15% dari skenario realistik.</p>
        <p class="mt-1"><strong>Pesimistik:</strong> Estimasi penurunan pemasukan -20% dan kenaikan pengeluaran +15% dari skenario realistik.</p>
        <div style="display:inline-flex; align-items:flex-start; gap:6px; margin-top:8px; font-size:0.78rem; color:var(--text-muted);">
          <x-lucide-info style="width:14px; height:14px; flex-shrink:0; margin-top:2px;" />
          Proyeksi ini bersifat estimasi matematis berdasarkan data masa lalu. Hasil aktual dapat bervariasi bergantung pada fluktuasi riil operasional kampus.
        </div>
      </div>
    </div>
  @endif
</div>
@endsection

@section('scripts')
@if(!isset($insufficientData) || !$insufficientData)
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) { lucide.createIcons(); }
    // --- Months selector ---
    document.getElementById('projection-months').addEventListener('change', function () {
      window.location.href = "{{ route('projections.index') }}?months=" + this.value;
    });

    // Formatting helper
    function formatCurrency(val) {
      return 'Rp ' + Number(val).toLocaleString('id-ID');
    }

    // Chart init
    const canvas = document.getElementById('chart-projection');
    if (canvas) {
      const optData = {!! json_encode(array_column($optimistic, 'cumulative')) !!};
      const realData = {!! json_encode(array_column($realistic, 'cumulative')) !!};
      const pesData = {!! json_encode(array_column($pessimistic, 'cumulative')) !!};
      const labels = {!! json_encode(array_column($realistic, 'shortLabel')) !!};

      new Chart(canvas, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Optimistik',
              data: optData,
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.05)',
              borderWidth: 2,
              borderDash: [6, 4],
              tension: 0.4,
              fill: false,
              pointRadius: 4,
              pointBackgroundColor: '#10b981',
            },
            {
              label: 'Realistik',
              data: realData,
              borderColor: '#8b5cf6',
              backgroundColor: 'rgba(139, 92, 246, 0.1)',
              borderWidth: 3,
              tension: 0.4,
              fill: true,
              pointRadius: 5,
              pointBackgroundColor: '#8b5cf6',
            },
            {
              label: 'Pesimistik',
              data: pesData,
              borderColor: '#f43f5e',
              backgroundColor: 'rgba(244, 63, 94, 0.05)',
              borderWidth: 2,
              borderDash: [6, 4],
              tension: 0.4,
              fill: false,
              pointRadius: 4,
              pointBackgroundColor: '#f43f5e',
            },
          ]
        },
        options: {
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
        }
      });
    }
  });
</script>
@endif
@endsection
