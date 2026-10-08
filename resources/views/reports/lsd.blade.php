@extends('layouts.app')

@section('title', 'Laporan Surplus Defisit (LSD) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <h2>Laporan Surplus Defisit (LSD) 
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
      <p>Laporan neraca keuangan, surplus defisit (ISAK 335), dan rekap mutasi proyek terpadu</p>
    </div>
  </div>

  <!-- Global Date Filter -->
  <div class="flex-between mb-2" style="background: var(--bg-card); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--border-primary);">
    <div style="display:flex; align-items:center; gap:15px">
      <div style="display:flex; align-items:center; gap:8px">
        <label class="form-label" style="margin-bottom:0; font-weight:600">Dari Tanggal:</label>
        <input type="date" class="form-input global-date-input font-mono-num" id="input-start-date" value="{{ $startDate }}" style="width:auto" required />
      </div>
      <div style="display:flex; align-items:center; gap:8px">
        <label class="form-label" style="margin-bottom:0; font-weight:600">Sampai:</label>
        <input type="date" class="form-input global-date-input font-mono-num" id="input-end-date" value="{{ $endDate }}" style="width:auto" required />
      </div>
    </div>
    <div style="font-size: 0.85rem; color: var(--text-secondary);">
      *Filter ini berlaku untuk seluruh tab laporan.
    </div>
  </div>

  <!-- Tabs Navigation -->
  @php $activeTab = request('tab', 'balance_sheet'); @endphp
  <div class="tabs">
    <button class="tab-btn {{ $activeTab === 'balance_sheet' ? 'active' : '' }}" data-tab="balance_sheet" style="display:inline-flex; align-items:center; gap:6px;">
      <x-lucide-scale style="width:15px; height:15px;" /> Balance Sheet
    </button>
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

  <!-- Hidden form to reload with correct tab and inputs -->
  <form action="{{ route('reports.lsd') }}" method="GET" id="report-filter-form" style="display:none">
    <input type="hidden" name="tab" id="filter-tab" value="{{ $activeTab }}">
    <input type="hidden" name="job_id" id="filter-job-id" value="{{ $selectedJobId }}">
    <input type="hidden" name="start_date" id="filter-start-date" value="{{ $startDate }}">
    <input type="hidden" name="end_date" id="filter-end-date" value="{{ $endDate }}">
  </form>

  <!-- TAB 1: Neraca Standar (Balance Sheet) -->
  <div id="tab-content-balance_sheet" class="report-tab-content" style="display: {{ $activeTab === 'balance_sheet' ? 'block' : 'none' }}">
    <div class="flex-between mb-2" style="justify-content: flex-end">
      <div style="display:flex; gap:8px">
        <a href="{{ route('reports.neraca.export-csv', ['start_date' => $startDate, 'end_date' => $endDate, 'as_of_date' => $asOfDate]) }}" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-download style="width:16px; height:16px;" /> Export CSV
        </a>
        <a href="{{ route('reports.neraca.print', ['start_date' => $startDate, 'end_date' => $endDate, 'as_of_date' => $asOfDate]) }}" target="_blank" class="btn btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-printer style="width:16px; height:16px;" /> Cetak PDF
        </a>
      </div>
    </div>

    <div class="card" style="max-width: 1000px; margin: 0 auto;">
      <div class="card-header" style="text-align: center; border-bottom: 2px solid var(--border-primary); padding-bottom: 16px;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 6px;">
          <img src="{{ asset('images/logo.jpg') }}" alt="Logo STT PU" style="width: 48px; height: 48px; object-fit: contain;" />
          <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--text-primary); margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">SEKOLAH TINGGI TEKNOLOGI PEKERJAAN UMUM - JAKARTA</h3>
            <div style="font-size: 1.05rem; font-weight: 800; color: var(--accent-primary); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 2px;">NERACA</div>
          </div>
        </div>
        <p style="color: var(--text-secondary); margin: 4px 0 0 0; font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">
          PERIODE {{ strtoupper(\Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('DD MMMM Y')) }} S/D {{ strtoupper(\Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('DD MMMM Y')) }}
        </p>
      </div>
      
      <div class="table-container" style="padding: 16px 12px; overflow-x: auto;">
        <table class="data-table" style="border: 1.5px solid var(--border-primary); border-collapse: collapse; width: 100%; font-size: 0.85rem;">
          <thead>
            <tr style="background: rgba(255,255,255,0.04); border-bottom: 1.5px solid var(--border-primary);">
              <th colspan="2" style="text-align: center; font-weight: 800; color: var(--text-primary); border-right: 1px solid var(--border-primary); padding: 8px;">AKTIVA</th>
              <th style="width: 40px; text-align: center; font-weight: 700; color: var(--text-secondary); border-right: 2px solid var(--border-primary); padding: 8px;">Cat</th>
              <th colspan="2" style="text-align: center; font-weight: 800; color: var(--text-primary); border-right: 1px solid var(--border-primary); padding: 8px;">PASIVA</th>
              <th style="width: 40px; text-align: center; font-weight: 700; color: var(--text-secondary); padding: 8px;">Cat</th>
            </tr>
          </thead>
          <tbody style="font-family: monospace;">
            <!-- Sub-headers -->
            <tr style="background: rgba(255,255,255,0.02); font-weight: 800;">
              <td colspan="2" style="color: var(--accent-primary); padding: 6px 10px; border-right: 1px solid var(--border-primary);">AKTIVA LANCAR</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td colspan="2" style="color: var(--accent-primary); padding: 6px 10px; border-right: 1px solid var(--border-primary);">HUTANG LANCAR</td>
              <td></td>
            </tr>

            <!-- Row 1: Kas & Biaya YMHD -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">11110&nbsp;&nbsp;Kas</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['kas'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">1</td>
              <td style="padding: 5px 8px;">21399&nbsp;&nbsp;Biaya YMHD lainnya</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['biayaYMHD'], 2, ',', '.') }}</td>
              <td style="text-align: center; color: var(--text-secondary);">11</td>
            </tr>

            <!-- Row 2: Bank Mandiri Giro I & Hutang Unit Lainnya -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">11121&nbsp;&nbsp;Bank Mandiri Giro I</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['bankMandiri1'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">2</td>
              <td style="padding: 5px 8px;">22220&nbsp;&nbsp;Hutang Unit Lainnya</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['hutangUnit'], 2, ',', '.') }}</td>
              <td style="text-align: center; color: var(--text-secondary);">12</td>
            </tr>

            <!-- Row 3: Bank Mandiri Giro II & Hutang YPP Pusat -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">11122&nbsp;&nbsp;Bank Mandiri Giro II</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['bankMandiri2'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">3</td>
              <td style="padding: 5px 8px;">22209&nbsp;&nbsp;Hutang YPP Pusat</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['hutangYPP'], 2, ',', '.') }}</td>
              <td style="text-align: center; color: var(--text-secondary);">13</td>
            </tr>

            <!-- Row 4: Biaya Dibayar Dimuka & Hutang Imbalan Pasca Kerja -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">11599&nbsp;&nbsp;Biaya Dibayar Dimuka</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['biayaDimuka'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">4</td>
              <td style="padding: 5px 8px;">21119&nbsp;&nbsp;Hutang Imbalan Pasca Kerja</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['hutangPasca'], 2, ',', '.') }}</td>
              <td style="text-align: center; color: var(--text-secondary);">14</td>
            </tr>

            <!-- Row 5: Total Aktiva Lancar & Total Hutang Lancar -->
            <tr style="border-bottom: 1.5px solid var(--border-primary); font-weight: 700; background: rgba(255,255,255,0.02);">
              <td style="padding: 6px 8px 6px 20px;">Jumlah Aktiva Lancar</td>
              <td style="text-align: right; padding: 6px 8px; border-right: 1px solid var(--border-primary); color: var(--color-income);">Rp {{ number_format($neraca['jumlahAktivaLancar'], 2, ',', '.') }}</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 6px 8px 6px 20px;">Jumlah Hutang Lancar</td>
              <td style="text-align: right; padding: 6px 8px; border-right: 1px solid var(--border-primary); color: var(--color-expense);">Rp {{ number_format($neraca['jumlahHutangLancar'], 2, ',', '.') }}</td>
              <td></td>
            </tr>

            <!-- Row 6: Sub-Headers AKTIVA TETAP & EKUITAS -->
            <tr style="background: rgba(255,255,255,0.02); font-weight: 800;">
              <td colspan="2" style="color: var(--accent-primary); padding: 6px 10px; border-right: 1px solid var(--border-primary);">AKTIVA TETAP</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td colspan="2" style="color: var(--accent-primary); padding: 6px 10px; border-right: 1px solid var(--border-primary);">ASET NETO (EKUITAS)</td>
              <td></td>
            </tr>

            <!-- Row 7: Gedung -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">12310&nbsp;&nbsp;Gedung</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['gedung'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">5</td>
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td></td>
            </tr>

            <!-- Row 8: Akm Peny Gedung -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">12311&nbsp;&nbsp;Akm Peny Gedung</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['akmGedung'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">6</td>
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td></td>
            </tr>

            <!-- Row 9: Nilai buku Gedung & Modal Donasi -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px 5px 20px; font-weight: 600;">Nilai buku Gedung</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary); font-weight: 600;">Rp {{ number_format($neraca['nbGedung'], 2, ',', '.') }}</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 5px 8px;">31210&nbsp;&nbsp;Modal Donasi</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['modalDonasi'], 2, ',', '.') }}</td>
              <td style="text-align: center; color: var(--text-secondary);">15</td>
            </tr>

            <!-- Row 10: Total Modal Donasi -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 5px 8px 5px 20px; font-weight: 600;">Total Modal Donasi</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary); font-weight: 600;">Rp {{ number_format($neraca['modalDonasi'], 2, ',', '.') }}</td>
              <td></td>
            </tr>

            <!-- Row 11: Inventaris Kantor -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">12120&nbsp;&nbsp;Inventaris Kantor</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['invKantor'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">7</td>
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td></td>
            </tr>

            <!-- Row 12: Akum Peny Inv. Kantor -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">12121&nbsp;&nbsp;Akum Peny Inv. Kantor</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['akmInvKantor'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">8</td>
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td></td>
            </tr>

            <!-- Row 13: Nilai buku Inventaris Kantor & Surplus Th Lalu -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px 5px 20px; font-weight: 600;">Nilai buku Inventaris Kantor</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary); font-weight: 600;">Rp {{ number_format($neraca['nbInv'], 2, ',', '.') }}</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 5px 8px;">33110&nbsp;&nbsp;Surplus (Minus) tahun lalu</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp ({{ number_format(abs($neraca['surplusThLalu']), 2, ',', '.') }})</td>
              <td style="text-align: center; color: var(--text-secondary);">16</td>
            </tr>

            <!-- Row 14: Surplus Th Berjalan -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 5px 8px;">33120&nbsp;&nbsp;Surplus (Minus) tahun berjalan</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp ({{ number_format(abs($neraca['surplusThBerjalan']), 2, ',', '.') }})</td>
              <td style="text-align: center; color: var(--text-secondary);">17</td>
            </tr>

            <!-- Row 15: Peralatan Laboratorium -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">12130&nbsp;&nbsp;Peralatan Laboratorium</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['peralatanLab'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">9</td>
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td></td>
            </tr>

            <!-- Row 16: Akum Peny Peralatan Lab. -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px;">12131&nbsp;&nbsp;Akum Peny Peralatan Lab.</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary);">Rp {{ number_format($neraca['akmLab'], 2, ',', '.') }}</td>
              <td style="text-align: center; border-right: 2px solid var(--border-primary); color: var(--text-secondary);">10</td>
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td></td>
            </tr>

            <!-- Row 17: Nilai buku Peralatan Lab -->
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 8px 5px 20px; font-weight: 600;">Nilai buku Peralatan Lab</td>
              <td style="text-align: right; padding: 5px 8px; border-right: 1px solid var(--border-primary); font-weight: 600;">Rp {{ number_format($neraca['nbLab'], 2, ',', '.') }}</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 5px 8px;"></td>
              <td style="border-right: 1px solid var(--border-primary);"></td>
              <td></td>
            </tr>

            <!-- Row 18: Jumlah Aktiva Tetap & JUMLAH EKUITAS -->
            <tr style="border-bottom: 1.5px solid var(--border-primary); font-weight: 700; background: rgba(255,255,255,0.02);">
              <td style="padding: 6px 8px 6px 20px;">Jumlah Aktiva Tetap</td>
              <td style="text-align: right; padding: 6px 8px; border-right: 1px solid var(--border-primary); color: var(--color-income);">Rp {{ number_format($neraca['jumlahAktivaTetap'], 2, ',', '.') }}</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 6px 8px;">JUMLAH ASET NETO</td>
              <td style="text-align: right; padding: 6px 8px; border-right: 1px solid var(--border-primary); color: var(--color-income);">Rp {{ number_format($neraca['jumlahEkuitas'], 2, ',', '.') }}</td>
              <td></td>
            </tr>

            <!-- Row 19: Grand Totals (JUMLAH AKTIVA & JUMLAH PASIVA) -->
            <tr style="font-weight: 800; font-size: 0.95rem; background: rgba(255,255,255,0.05); border-top: 2px solid var(--border-primary);">
              <td style="padding: 10px 8px; color: var(--text-primary);">JUMLAH AKTIVA</td>
              <td style="text-align: right; padding: 10px 8px; border-right: 1px solid var(--border-primary); color: var(--color-income);">Rp {{ number_format($neraca['jumlahAktiva'], 2, ',', '.') }}</td>
              <td style="border-right: 2px solid var(--border-primary);"></td>
              <td style="padding: 10px 8px; color: var(--text-primary);">JUMLAH PASIVA</td>
              <td style="text-align: right; padding: 10px 8px; border-right: 1px solid var(--border-primary); color: var(--color-income);">Rp {{ number_format($neraca['jumlahPasiva'], 2, ',', '.') }}</td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Signatures Footer -->
      <div style="border-top: 1px dashed var(--border-primary); padding: 24px 24px 24px; font-size: 0.85rem; color: var(--text-secondary);">
        <!-- Baris Tanggal di Atas Kanan (di atas Dibuat oleh) -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 14px; padding-right: 16px;">
          <div style="color: var(--text-muted); font-size: 0.84rem;">
            Jakarta, {{ \Carbon\Carbon::parse($endDate)->locale('id')->translatedFormat('d F Y') }}
          </div>
        </div>

        <!-- Baris 1: 3 Kolom Sejajar Sempurna -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; text-align: center;">
          <!-- Kolom 1: Menyetujui -->
          <div style="display: flex; flex-direction: column; justify-content: space-between; min-height: 135px;">
            <div>
              <div style="font-weight: 700; color: var(--text-primary);">Menyetujui,</div>
              <div style="color: var(--text-muted); font-size: 0.82rem; margin-top: 2px;">Waket II</div>
            </div>
            <div>
              <div style="font-weight: 700; color: var(--text-primary); border-top: 1px solid var(--border-primary); padding-top: 6px; display: inline-block; min-width: 200px;">
                Ir. Rina Agustin Indriani, MURP
              </div>
            </div>
          </div>

          <!-- Kolom 2: Memeriksa -->
          <div style="display: flex; flex-direction: column; justify-content: space-between; min-height: 135px;">
            <div>
              <div style="font-weight: 700; color: var(--text-primary);">Memeriksa,</div>
              <div style="color: var(--text-muted); font-size: 0.82rem; margin-top: 2px;">Kabag. Keuangan dan Personalia</div>
            </div>
            <div>
              <div style="font-weight: 700; color: var(--text-primary); border-top: 1px solid var(--border-primary); padding-top: 6px; display: inline-block; min-width: 200px;">
                Noor'aini Kartikarini, SM
              </div>
            </div>
          </div>

          <!-- Kolom 3: Dibuat oleh (Sejajar dengan Memeriksa dan Menyetujui) -->
          <div style="display: flex; flex-direction: column; justify-content: space-between; min-height: 135px;">
            <div>
              <div style="font-weight: 700; color: var(--text-primary);">Dibuat oleh,</div>
              <div style="color: var(--text-muted); font-size: 0.82rem; margin-top: 2px;">Staf. Adm Umum & Keuangan</div>
            </div>
            <div>
              <div style="font-weight: 700; color: var(--text-primary); border-top: 1px solid var(--border-primary); padding-top: 6px; display: inline-block; min-width: 200px;">
                Irma Yaniarti
              </div>
            </div>
          </div>
        </div>

        <!-- Baris 2: Mengetahui (Ketua) Simetris di Tengah Bawah -->
        <div style="margin-top: 32px; display: flex; justify-content: center; text-align: center;">
          <div style="display: flex; flex-direction: column; justify-content: space-between; min-height: 120px; width: 340px;">
            <div>
              <div style="font-weight: 700; color: var(--text-primary);">Mengetahui,</div>
              <div style="color: var(--text-muted); font-size: 0.82rem; margin-top: 2px;">Ketua STT Pekerjaan Umum</div>
            </div>
            <div>
              <div style="font-weight: 700; color: var(--text-primary); border-top: 1px solid var(--border-primary); padding-top: 6px; display: inline-block; min-width: 240px;">
                Dr. Ir. Arie Setiadi Moerwanto, MSc
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB 2: Activity Summary (Jobs) -->
  <div id="tab-content-summary" class="report-tab-content" style="display: {{ $activeTab === 'summary' ? 'block' : 'none' }}">
    <div class="flex-between mb-2" style="justify-content: flex-end">
      <div style="display:flex; gap:8px">
        <a href="{{ route('reports.summary.export-csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-download style="width:16px; height:16px;" /> Export CSV
        </a>
        <a href="{{ route('reports.summary.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-printer style="width:16px; height:16px;" /> Cetak PDF
        </a>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Ringkasan Aktivitas Proyek (All Jobs)</h3>
      </div>
      <div class="table-container">
        <table class="data-table" style="font-size: 0.95rem">
          <thead>
            <tr style="border-bottom: 2px solid var(--border-primary)">
              <th>Name</th>
              <th style="text-align: right">Debit</th>
              <th style="text-align: right">Credit</th>
              <th style="text-align: right">Net Activity</th>
            </tr>
          </thead>
          <tbody>
            @php 
              $grandTotalDebit = 0;
              $grandTotalCredit = 0;
            @endphp
            @if(empty($activitySummary))
              <tr>
                <td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted)">Belum ada data aktivitas proyek.</td>
              </tr>
            @else
              @foreach($activitySummary as $catData)
                @php
                  $grandTotalDebit += $catData['total_debit'];
                  $grandTotalCredit += $catData['total_credit'];
                @endphp
                <tr style="background: none">
                  <td colspan="4" style="font-weight: 600; padding-top: 15px;">
                    {{ $catData['category']->code ?? '' }} {{ $catData['category']->name }}
                  </td>
                </tr>
                @foreach($catData['jobs'] as $jData)
                  <tr>
                    <td style="padding-left: 40px; color: var(--text-secondary)">
                      <span style="display: inline-block; width: 40px;">{{ $jData['job']->code }}</span> {{ $jData['job']->name }}
                    </td>
                    <td style="text-align: right; color: var(--text-primary)">
                      Rp {{ number_format($jData['debit'], 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: var(--text-primary)">
                      Rp {{ number_format($jData['credit'], 2, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: var(--text-primary)">
                      Rp {{ number_format($jData['net'], 2, ',', '.') }} {{ $jData['isCredit'] ? 'cr' : '' }}
                    </td>
                  </tr>
                @endforeach
                <tr style="border-top: 1px solid rgba(255,255,255,0.1)">
                  <td style="text-align: right; padding-right: 20px;">Total:</td>
                  <td style="text-align: right;">Rp {{ number_format($catData['total_debit'], 2, ',', '.') }}</td>
                  <td style="text-align: right;">Rp {{ number_format($catData['total_credit'], 2, ',', '.') }}</td>
                  <td style="text-align: right;">
                    Rp {{ number_format($catData['total_net'], 2, ',', '.') }} {{ $catData['is_credit'] ? 'cr' : '' }}
                  </td>
                </tr>
              @endforeach
              
              <!-- SPACING -->
              <tr><td colspan="4" style="border: none; height: 20px;"></td></tr>
              
              <tr style="font-weight: 600;">
                <td style="text-align: right; padding-right: 20px;">Grand Total:</td>
                <td style="text-align: right; border-bottom: 2px solid var(--border-primary)">Rp {{ number_format($grandTotalDebit, 2, ',', '.') }}</td>
                <td style="text-align: right; border-bottom: 2px solid var(--border-primary)">Rp {{ number_format($grandTotalCredit, 2, ',', '.') }}</td>
                <td style="text-align: right; border-bottom: 2px solid var(--border-primary)">
                  @php 
                    $grandNet = abs($grandTotalCredit - $grandTotalDebit); 
                    $isGrandCredit = $grandTotalCredit >= $grandTotalDebit;
                  @endphp
                  Rp {{ number_format($grandNet, 2, ',', '.') }} {{ $isGrandCredit ? 'cr' : '' }}
                </td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB 3: Profit & Loss per Job -->
  <div id="tab-content-pnl" class="report-tab-content" style="display: {{ $activeTab === 'pnl' ? 'block' : 'none' }}">
    <div class="flex-between mb-2">
      <div style="display:flex; align-items:center; gap:8px">
        <label class="form-label" style="margin-bottom:0; font-weight:600">Pilih Proyek:</label>
        <select class="form-select job-selector" style="min-width: 250px">
          <option value="all" {{ $selectedJobId === 'all' ? 'selected' : '' }}>[SEMUA PROYEK] Konsolidasi</option>
          @foreach($jobs as $j)
            <option value="{{ $j->id }}" {{ $selectedJobId == $j->id ? 'selected' : '' }}>[{{ $j->code }}] {{ $j->name }}</option>
          @endforeach
        </select>
      </div>
      @if($selectedJobId)
      <div style="display:flex; gap:8px">
        <a href="{{ route('reports.pnl.export-csv', ['job_id' => $selectedJobId, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-download style="width:16px; height:16px;" /> Export CSV
        </a>
        <a href="{{ route('reports.pnl.print', ['job_id' => $selectedJobId, 'start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="btn btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-printer style="width:16px; height:16px;" /> Cetak PDF
        </a>
      </div>
      @endif
    </div>

    @if(empty($statementData['jobReports']))
      <div class="card" style="padding: 40px 0; text-align: center;">
        <div class="empty-state-title" style="color: var(--text-secondary)">Tidak ada transaksi tercatat pada periode ini</div>
      </div>
    @else
      <div class="card" style="max-width: 880px; margin: 0 auto;">
        <div class="card-header" style="text-align: center; border-bottom: 2px solid var(--border-primary); padding-bottom: 15px;">
          <h3 style="font-size: 1.3rem; color: var(--text-primary)">JOB SURPLUS DEFISIT STATEMENT (ISAK 335)</h3>
          <h4 style="color: var(--accent-primary); font-family:'Outfit'; margin-top:4px">
            {{ $selectedJobId === 'all' ? '[SEMUA PROYEK] Laporan Konsolidasi' : '[' . $selectedJob->code . '] ' . $selectedJob->name }}
          </h4>
          <p style="color: var(--text-secondary); margin-top: 4px; font-size: 0.85rem">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} through {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
          </p>
        </div>
        
        <div class="table-container" style="padding: 15px 10px;">
          <table class="data-table" style="border: none; font-size: 0.95rem;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-primary)">
                <th style="padding-left: 15px;">Account Name</th>
                <th style="text-align: right; width: 180px;">Selected Period</th>
                <th style="text-align: right; width: 180px;">Year to Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach($statementData['jobReports'] as $report)
                <!-- JOB HEADER -->
                <tr style="background: rgba(255,255,255,0.03);">
                  <td colspan="3" style="font-weight: 800; font-size: 1.05rem; padding-top: 18px; padding-bottom: 8px; color: var(--accent-primary)">
                    {{ $report['job']->code }} &nbsp;&nbsp;&nbsp; {{ $report['job']->name }}
                  </td>
                </tr>

                <!-- INCOME -->
                @if(!empty($report['sections']['Income']))
                  <tr style="background: none">
                    <td colspan="3" style="font-weight: 700; padding-left: 20px; padding-top: 10px; color: #10b981;">Income</td>
                  </tr>
                  @foreach($report['sections']['Income'] as $item)
                    <tr>
                      <td style="padding-left: 35px; color: var(--text-secondary)">
                        <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted); margin-right: 6px;">[{{ $item['code'] }}]</span>
                        {{ $item['name'] }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                      </td>
                    </tr>
                  @endforeach
                  <tr style="font-weight: 700; border-top: 1px solid var(--border-primary); background: rgba(255,255,255,0.01)">
                    <td style="padding-left: 20px;">Total Income</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['income']['period'], 2, ',', '.') }}</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['income']['ytd'], 2, ',', '.') }}</td>
                  </tr>
                @endif

                <!-- COST OF SALES -->
                @if(!empty($report['sections']['Cost of Sales']))
                  <tr style="background: none">
                    <td colspan="3" style="font-weight: 700; padding-left: 20px; padding-top: 12px; color: #f59e0b;">Cost of Sales</td>
                  </tr>
                  @foreach($report['sections']['Cost of Sales'] as $item)
                    <tr>
                      <td style="padding-left: 35px; color: var(--text-secondary)">
                        <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted); margin-right: 6px;">[{{ $item['code'] }}]</span>
                        {{ $item['name'] }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                      </td>
                    </tr>
                  @endforeach
                  <tr style="font-weight: 700; border-top: 1px solid var(--border-primary); background: rgba(255,255,255,0.01)">
                    <td style="padding-left: 20px;">Total Cost of Sales</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['cos']['period'], 2, ',', '.') }}</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['cos']['ytd'], 2, ',', '.') }}</td>
                  </tr>
                @endif

                <!-- EXPENSE -->
                @if(!empty($report['sections']['Expense']))
                  <tr style="background: none">
                    <td colspan="3" style="font-weight: 700; padding-left: 20px; padding-top: 12px; color: #ef4444;">Expense</td>
                  </tr>
                  @foreach($report['sections']['Expense'] as $item)
                    <tr>
                      <td style="padding-left: 35px; color: var(--text-secondary)">
                        <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted); margin-right: 6px;">[{{ $item['code'] }}]</span>
                        {{ $item['name'] }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                      </td>
                    </tr>
                  @endforeach
                  <tr style="font-weight: 700; border-top: 1px solid var(--border-primary); background: rgba(255,255,255,0.01)">
                    <td style="padding-left: 20px;">Total Expense</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['expense']['period'], 2, ',', '.') }}</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['expense']['ytd'], 2, ',', '.') }}</td>
                  </tr>
                @endif

                <!-- OTHER INCOME -->
                @if(!empty($report['sections']['Other Income']))
                  <tr style="background: none">
                    <td colspan="3" style="font-weight: 700; padding-left: 20px; padding-top: 12px; color: #10b981;">Other Income</td>
                  </tr>
                  @foreach($report['sections']['Other Income'] as $item)
                    <tr>
                      <td style="padding-left: 35px; color: var(--text-secondary)">
                        <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted); margin-right: 6px;">[{{ $item['code'] }}]</span>
                        {{ $item['name'] }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                      </td>
                    </tr>
                  @endforeach
                  <tr style="font-weight: 700; border-top: 1px solid var(--border-primary); background: rgba(255,255,255,0.01)">
                    <td style="padding-left: 20px;">Total Other Income</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['other_income']['period'], 2, ',', '.') }}</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['other_income']['ytd'], 2, ',', '.') }}</td>
                  </tr>
                @endif

                <!-- OTHER EXPENSE -->
                @if(!empty($report['sections']['Other Expense']))
                  <tr style="background: none">
                    <td colspan="3" style="font-weight: 700; padding-left: 20px; padding-top: 12px; color: #ef4444;">Other Expense</td>
                  </tr>
                  @foreach($report['sections']['Other Expense'] as $item)
                    <tr>
                      <td style="padding-left: 35px; color: var(--text-secondary)">
                        <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted); margin-right: 6px;">[{{ $item['code'] }}]</span>
                        {{ $item['name'] }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                      </td>
                      <td style="text-align: right; color: var(--text-primary)">
                        {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                      </td>
                    </tr>
                  @endforeach
                  <tr style="font-weight: 700; border-top: 1px solid var(--border-primary); background: rgba(255,255,255,0.01)">
                    <td style="padding-left: 20px;">Total Other Expense</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['other_expense']['period'], 2, ',', '.') }}</td>
                    <td style="text-align: right;">Rp{{ number_format($report['totals']['other_expense']['ytd'], 2, ',', '.') }}</td>
                  </tr>
                @endif

                <!-- NET SURPLUS (DEFISIT) -->
                <tr style="background: rgba(255,255,255,0.05); font-weight: 800; border-top: 1px solid var(--border-primary); border-bottom: 2px solid var(--border-primary)">
                  <td style="padding-left: 20px; padding-top: 10px; padding-bottom: 10px; font-size: 0.95rem;">Net Surplus (Defisit)</td>
                  <td style="text-align: right; padding-top: 10px; padding-bottom: 10px; font-size: 0.95rem; color: {{ $report['totals']['net_profit']['period'] >= 0 ? '#10b981' : '#ef4444' }};">
                    {{ $report['totals']['net_profit']['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['net_profit']['period']), 2, ',', '.') }}
                  </td>
                  <td style="text-align: right; padding-top: 10px; padding-bottom: 10px; font-size: 0.95rem; color: {{ $report['totals']['net_profit']['ytd'] >= 0 ? '#10b981' : '#ef4444' }};">
                    {{ $report['totals']['net_profit']['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['net_profit']['ytd']), 2, ',', '.') }}
                  </td>
                </tr>

                <!-- SPACING -->
                <tr style="height: 25px; background: none"><td colspan="3" style="border: none"></td></tr>
              @endforeach

              @if($selectedJobId === 'all')
                <!-- GRAND CONSOLIDATED NET SURPLUS (DEFISIT) -->
                <tr style="background: rgba(255,255,255,0.08); font-weight: 800; border-top: 3px double var(--border-primary); border-bottom: 3px double var(--border-primary)">
                  <td style="padding-left: 15px; padding-top: 14px; padding-bottom: 14px; font-size: 1.05rem;">GRAND TOTAL NET SURPLUS (DEFISIT)</td>
                  <td style="text-align: right; padding-top: 14px; padding-bottom: 14px; font-size: 1.05rem; color: {{ $jobNetProfit >= 0 ? '#10b981' : '#ef4444' }};">
                    {{ $jobNetProfit < 0 ? '-' : '' }}Rp{{ number_format(abs($jobNetProfit), 2, ',', '.') }}
                  </td>
                  <td style="text-align: right; padding-top: 14px; padding-bottom: 14px; font-size: 1.05rem; color: {{ $jobNetProfitYtd >= 0 ? '#10b981' : '#ef4444' }};">
                    {{ $jobNetProfitYtd < 0 ? '-' : '' }}Rp{{ number_format(abs($jobNetProfitYtd), 2, ',', '.') }}
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>

  <!-- TAB 4: Job Transactions -->
  <div id="tab-content-transactions" class="report-tab-content" style="display: {{ $activeTab === 'transactions' ? 'block' : 'none' }}">
    <div class="flex-between mb-2">
      <div style="display:flex; align-items:center; gap:8px">
        <label class="form-label" style="margin-bottom:0; font-weight:600">Pilih Proyek:</label>
        <select class="form-select job-selector" style="min-width: 250px">
          <option value="all" {{ $selectedJobId === 'all' ? 'selected' : '' }}>[SEMUA PROYEK] Konsolidasi</option>
          @foreach($jobs as $j)
            <option value="{{ $j->id }}" {{ $selectedJobId == $j->id ? 'selected' : '' }}>[{{ $j->code }}] {{ $j->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Transaksi Kas Proyek</h3>
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
            @if((!$selectedJob && $selectedJobId !== 'all') || $jobTransactions->isEmpty())
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
    const startDateInput = document.getElementById('filter-start-date');
    const endDateInput = document.getElementById('filter-end-date');
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

    // --- Date Selector ---
    document.getElementById('input-start-date').addEventListener('change', function() {
      startDateInput.value = this.value;
      filterForm.submit();
    });

    document.getElementById('input-end-date').addEventListener('change', function() {
      endDateInput.value = this.value;
      filterForm.submit();
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
          currentUrl.searchParams.set('start_date', startDateInput.value);
          currentUrl.searchParams.set('end_date', endDateInput.value);
          
          window.location.href = currentUrl.toString();
        }
      });
    });
  });
</script>
@endsection
