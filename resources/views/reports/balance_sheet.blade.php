@extends('layouts.app')

@section('title', 'Neraca Standar (Balance Sheet) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <h2>Neraca Standar 
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
      <p>Laporan posisi keuangan perusahaan (Aset, Kewajiban, & Ekuitas)</p>
    </div>
    <div class="flex gap-1">
      <form action="{{ route('reports.balance-sheet') }}" method="GET" id="balance-sheet-filter" style="display:flex; gap:8px">
        <input type="date" class="form-input font-mono-num" name="as_of_date" id="as-of-date" value="{{ $asOfDate }}" required />
      </form>
    </div>
  </div>

  <div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header" style="text-align: center; border-bottom: 2px solid var(--border-primary); padding-bottom: 20px;">
      <h3 style="font-size: 1.5rem; font-family: 'Outfit', sans-serif; color: var(--text-primary)">LAPORAN NERACA</h3>
      <p style="color: var(--text-secondary); margin-top: 4px; font-size: 0.9rem">Per Tanggal: {{ \Carbon\Carbon::parse($asOfDate)->translatedFormat('d F Y') }}</p>
    </div>
    
    <div class="table-container" style="padding: 20px 10px;">
      <table class="data-table" style="border: none;">
        <tbody>
          <!-- ASSETS SECTION -->
          <tr style="background: rgba(255,255,255,0.02)">
            <td colspan="2" style="font-weight: 800; font-size: 1.1rem; color: var(--accent-primary); letter-spacing: 0.5px; border-bottom: 1px solid var(--border-accent)">ASET (ASSETS)</td>
          </tr>
          
          <tr style="background: none">
            <td style="font-weight: 600; padding-left: 20px; color: var(--text-primary)">Kas & Setara Kas</td>
            <td style="text-align: right;"></td>
          </tr>
          <tr>
            <td style="padding-left: 40px; color: var(--text-secondary); display:flex; align-items:center; gap:6px;">
              <x-lucide-wallet style="width:14px; height:14px; color:var(--accent-primary);" />
              Saldo Kas Kecil
            </td>
            <td style="text-align: right; font-weight: 600; color: var(--text-primary)" class="font-mono-num tabular-nums">Rp {{ number_format($pettyCashBalance, 0, ',', '.') }}</td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-primary)">
            <td style="padding-left: 40px; color: var(--text-secondary); display:flex; align-items:center; gap:6px;">
              <x-lucide-landmark style="width:14px; height:14px; color:var(--accent-secondary);" />
              Saldo Kas Bank
            </td>
            <td style="text-align: right; font-weight: 600; color: var(--text-primary)" class="font-mono-num tabular-nums">Rp {{ number_format($bankBalance, 0, ',', '.') }}</td>
          </tr>
          
          <!-- TOTAL ASSETS -->
          <tr style="font-weight: 800; background: rgba(255,255,255,0.03); border-top: 2px solid var(--border-primary)">
            <td style="padding-left: 20px; color: var(--text-primary)">TOTAL ASET</td>
            <td style="text-align: right; color: var(--color-income); font-size: 1.05rem" class="font-mono-num tabular-nums">Rp {{ number_format($totalAssets, 0, ',', '.') }}</td>
          </tr>
          
          <!-- SPACING -->
          <tr style="height: 30px; background: none"><td colspan="2" style="border: none"></td></tr>
          
          <!-- LIABILITIES & EQUITY SECTION -->
          <tr style="background: rgba(255,255,255,0.02)">
            <td colspan="2" style="font-weight: 800; font-size: 1.1rem; color: var(--accent-primary); letter-spacing: 0.5px; border-bottom: 1px solid var(--border-accent)">KEWAJIBAN & EKUITAS (LIABILITIES & EQUITY)</td>
          </tr>
          
          <!-- LIABILITIES -->
          <tr style="background: none">
            <td style="font-weight: 600; padding-left: 20px; color: var(--text-primary)">Kewajiban</td>
            <td style="text-align: right;"></td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-primary)">
            <td style="padding-left: 40px; color: var(--text-secondary)">Utang Usaha & Kewajiban Lainnya</td>
            <td style="text-align: right; font-weight: 600; color: var(--text-muted)" class="font-mono-num tabular-nums">Rp 0</td>
          </tr>
          <tr style="font-weight: 700; background: rgba(255,255,255,0.01)">
            <td style="padding-left: 20px; color: var(--text-primary)">Total Kewajiban</td>
            <td style="text-align: right; color: var(--text-muted)" class="font-mono-num tabular-nums">Rp 0</td>
          </tr>
          
          <!-- SPACING -->
          <tr style="height: 10px; background: none"><td colspan="2" style="border: none"></td></tr>
          
          <!-- EQUITY -->
          <tr style="background: none">
            <td style="font-weight: 600; padding-left: 20px; color: var(--text-primary)">Ekuitas</td>
            <td style="text-align: right;"></td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-primary)">
            <td style="padding-left: 40px; color: var(--text-secondary); display:flex; align-items:center; gap:6px;">
              <x-lucide-trending-up style="width:14px; height:14px; color:#22c55e;" />
              Laba Berjalan / Ditahan
            </td>
            <td style="text-align: right; font-weight: 600; color: var(--text-primary)" class="font-mono-num tabular-nums">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</td>
          </tr>
          <tr style="font-weight: 700; background: rgba(255,255,255,0.01)">
            <td style="padding-left: 20px; color: var(--text-primary)">Total Ekuitas</td>
            <td style="text-align: right; color: var(--text-primary)" class="font-mono-num tabular-nums">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</td>
          </tr>
          
          <!-- TOTAL LIABILITIES & EQUITY -->
          <tr style="font-weight: 800; background: rgba(255,255,255,0.03); border-top: 2px solid var(--border-primary)">
            <td style="padding-left: 20px; color: var(--text-primary)">TOTAL KEWAJIBAN & EKUITAS</td>
            <td style="text-align: right; color: var(--color-income); font-size: 1.05rem" class="font-mono-num tabular-nums">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; border-radius: 8px; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; gap: 8px;">
      <x-lucide-check-circle style="width:18px; height:18px; color:var(--color-income);" />
      <span style="font-size: 0.9rem; font-weight: 600; color: var(--color-income)">Neraca Seimbang (Balanced): Aset = Kewajiban + Ekuitas</span>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) { lucide.createIcons(); }
    document.getElementById('as-of-date').addEventListener('change', function () {
      document.getElementById('balance-sheet-filter').submit();
    });
  });
</script>
@endsection
