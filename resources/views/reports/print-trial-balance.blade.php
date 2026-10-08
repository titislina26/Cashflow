@php
  \Carbon\Carbon::setLocale('id');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Neraca Saldo (Trial Balance) - STT Pekerjaan Umum</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 12mm 15mm 12mm 15mm;
    }
    
    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    
    body {
      font-family: Arial, Helvetica, sans-serif;
      margin: 0;
      padding: 20px;
      color: #000;
      background: #e2e8f0;
      font-size: 11px;
      line-height: 1.3;
    }

    .utility-bar {
      max-width: 900px;
      margin: 0 auto 20px auto;
      padding: 12px 20px;
      background: #1e293b;
      color: #fff;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .utility-title {
      font-weight: 700;
      font-size: 14px;
    }
    .btn {
      padding: 8px 18px;
      border-radius: 6px;
      font-weight: 700;
      cursor: pointer;
      font-size: 12px;
      border: 1px solid transparent;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
    }
    .btn-primary {
      background: #2563eb;
      color: #fff;
    }
    .btn-secondary {
      background: #475569;
      color: #fff;
    }

    .page-container {
      max-width: 900px;
      margin: 0 auto;
      background: #fff;
      padding: 30px 40px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      border-radius: 4px;
    }

    .header {
      text-align: center;
      margin-bottom: 24px;
      border-bottom: 2px solid #000;
      padding-bottom: 12px;
    }
    .institution-name {
      font-size: 14px;
      font-weight: 800;
      letter-spacing: 0.5px;
      margin: 0;
    }
    .report-title {
      font-size: 16px;
      font-weight: 800;
      text-transform: uppercase;
      margin: 4px 0;
    }
    .period-title {
      font-size: 11px;
      font-weight: 700;
      color: #333;
      margin: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
      margin-bottom: 24px;
    }
    th, td {
      border: 1px solid #94a3b8;
      padding: 5px 8px;
    }
    th {
      background-color: #f1f5f9;
      font-weight: 800;
      text-align: center;
    }
    .text-right {
      text-align: right;
    }
    .text-center {
      text-align: center;
    }
    .font-bold {
      font-weight: 700;
    }

    .sig-section {
      width: 100%;
      margin-top: 30px;
      page-break-inside: avoid;
    }
    .sig-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      text-align: center;
    }
    .sig-box {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100px;
    }
    .sig-role {
      font-size: 9px;
      font-weight: 700;
      color: #333;
    }
    .sig-name {
      font-size: 9.5px;
      font-weight: 800;
      text-decoration: underline;
    }

    @media print {
      body {
        background: #fff;
        padding: 0;
      }
      .utility-bar {
        display: none !important;
      }
      .page-container {
        box-shadow: none;
        padding: 0;
        max-width: 100%;
      }
    }
  </style>
</head>
<body>

  <div class="utility-bar">
    <div class="utility-title" style="display:flex; align-items:center; gap:8px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
      Cetak Neraca Saldo (A4)
    </div>
    <div style="display:flex; gap:10px">
      <button onclick="window.print()" class="btn btn-primary">Cetak Sekarang</button>
      <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
  </div>

  <div class="page-container">
    <div class="header">
      <h2 class="institution-name">SEKOLAH TINGGI TEKNOLOGI PEKERJAAN UMUM</h2>
      <h1 class="report-title">NERACA SALDO (TRIAL BALANCE)</h1>
      <p class="period-title">PERIODE: {{ strtoupper(\Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y')) }} S/D {{ strtoupper(\Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y')) }}</p>
    </div>

    <table>
      <thead>
        <tr>
          <th style="width: 70px;">KODE</th>
          <th>NAMA AKUN BUKU BESAR</th>
          <th style="width: 95px;">MUTASI DEBET</th>
          <th style="width: 95px;">MUTASI KREDIT</th>
          <th style="width: 105px;">SALDO DEBET</th>
          <th style="width: 105px;">SALDO KREDIT</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $row)
          <tr>
            <td class="text-center font-bold" style="font-family:monospace;">{{ $row['category']->code }}</td>
            <td>{{ $row['category']->name }}</td>
            <td class="text-right" style="font-family:monospace;">{{ $row['debit_mutation'] > 0 ? number_format($row['debit_mutation'], 0, ',', '.') : '—' }}</td>
            <td class="text-right" style="font-family:monospace;">{{ $row['credit_mutation'] > 0 ? number_format($row['credit_mutation'], 0, ',', '.') : '—' }}</td>
            <td class="text-right font-bold" style="font-family:monospace;">{{ $row['ending_debit'] > 0 ? number_format($row['ending_debit'], 0, ',', '.') : '—' }}</td>
            <td class="text-right font-bold" style="font-family:monospace;">{{ $row['ending_credit'] > 0 ? number_format($row['ending_credit'], 0, ',', '.') : '—' }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="background:#f8fafc; font-weight:800;">
          <td colspan="4" class="text-right" style="padding: 7px 8px;">TOTAL NERACA SALDO:</td>
          <td class="text-right" style="font-family:monospace; font-size:11px;">Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}</td>
          <td class="text-right" style="font-family:monospace; font-size:11px;">Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}</td>
        </tr>
        <tr style="background:#f1f5f9; font-weight:700;">
          <td colspan="4" class="text-right" style="font-size:9.5px;">STATUS KESEIMBANGAN (OUT OF BALANCE):</td>
          <td colspan="2" class="text-center" style="font-family:monospace; font-size:10px;">
            {{ $outOfBalance < 0.01 ? 'Rp 0 (SEIMBANG / BALANCED)' : 'SELISIH: Rp ' . number_format($outOfBalance, 0, ',', '.') }}
          </td>
        </tr>
      </tfoot>
    </table>

    <!-- Signature Matrix -->
    <div class="sig-section">
      <div style="text-align:right; font-size:10px; margin-bottom:12px">
        Jakarta, {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}
      </div>
      <div class="sig-grid">
        <div class="sig-box">
          <div class="sig-role">Menyetujui,<br><strong>Ketua STT-PU</strong></div>
          <div class="sig-name">{{ $leader }}</div>
        </div>
        <div class="sig-box">
          <div class="sig-role">Mengetahui,<br><strong>Wakil Ketua II</strong></div>
          <div class="sig-name">{{ $approver }}</div>
        </div>
        <div class="sig-box">
          <div class="sig-role">Verifikator,<br><strong>Ka. Lembaga Penjamin Mutu</strong></div>
          <div class="sig-name">{{ $verifier }}</div>
        </div>
        <div class="sig-box">
          <div class="sig-role">Pembuat Dokumen,<br><strong>Ka. BAUK</strong></div>
          <div class="sig-name">{{ $maker }}</div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
