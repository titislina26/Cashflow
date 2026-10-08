@php
  \Carbon\Carbon::setLocale('id');
  $isIncome = ($transactionType === 'income');
  $cleanVoucher = strtoupper(trim($voucherNumber ?? ''));
  if (str_starts_with($cleanVoucher, 'PP')) {
      $voucherTitle = 'BUKTI PENGEMBALIAN PANJAR';
  } elseif (str_starts_with($cleanVoucher, 'PK')) {
      $voucherTitle = 'BUKTI PENGEMBALIAN PINJAMAN KARYAWAN';
  } elseif (str_starts_with($cleanVoucher, 'TTN')) {
      $voucherTitle = 'BUKTI PENERIMAAN BANK / PENCAIRAN CEK';
  } elseif (str_starts_with($cleanVoucher, 'TT')) {
      $voucherTitle = 'BUKTI PEMASUKAN KAS';
  } elseif (str_starts_with($cleanVoucher, 'KT')) {
      $voucherTitle = 'BUKTI PENGELUARAN KAS';
  } elseif (str_starts_with($cleanVoucher, 'P.') || str_starts_with($cleanVoucher, 'P ') || preg_match('/^P\d/', $cleanVoucher)) {
      $voucherTitle = 'BUKTI PENGELUARAN KAS (PANJAR KERJA)';
  } else {
      $voucherTitle = $isIncome ? 'BUKTI PEMASUKAN' : 'BUKTI PENGELUARAN';
  }
  
  // Format terbilang with exact single 'Rupiah' inside delimiter '#'
  $cleanSpelling = preg_replace('/\s+rupiah\s*$/i', '', trim($terbilang));
  $cleanSpelling = preg_replace('/\s+/', ' ', $cleanSpelling);
  $formattedTerbilang = '# ' . ucwords(strtolower($cleanSpelling)) . ' Rupiah #';
  
  // Default to at least 8 rows like the physical ledger voucher
  $padRows = max(8, count($items));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $voucherTitle }} - {{ $voucherNumber }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 12mm 15mm;
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
      background: #f1f5f9;
      font-size: 13px;
      line-height: 1.3;
    }

    /* Print utility bar */
    .utility-bar {
      max-width: 820px;
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
    .btn {
      padding: 7px 16px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 13px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
      border: none;
    }
    .btn-close {
      background: #334155;
      color: #fff;
    }
    .btn-close:hover {
      background: #475569;
    }
    .btn-print {
      background: #2563eb;
      color: #fff;
    }
    .btn-print:hover {
      background: #1d4ed8;
    }

    /* Voucher Paper */
    .voucher-paper {
      width: 100%;
      max-width: 820px;
      margin: 0 auto;
      background: #fff;
      padding: 25px 30px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      border-radius: 4px;
    }

    /* Header Section */
    .voucher-header {
      position: relative;
      margin-bottom: 6px;
      min-height: 75px;
    }
    .logo-container {
      position: absolute;
      left: 0;
      top: 0;
    }
    .logo-container img {
      width: 90px;
      height: auto;
    }
    .header-center {
      text-align: center;
      padding-top: 2px;
    }
    .institution-name {
      color: #0f4c3a; /* Dark Forest Green */
      font-size: 17px;
      font-weight: 800;
      letter-spacing: 0.5px;
      line-height: 1.25;
      margin: 0;
      text-transform: uppercase;
    }
    .voucher-title {
      color: #000;
      font-size: 21px;
      font-weight: 800;
      letter-spacing: 1px;
      margin-top: 14px;
      margin-bottom: 0;
      text-transform: uppercase;
    }

    /* Subheader (Prodi & No/Tanggal) */
    .voucher-subheader {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-top: 12px;
      margin-bottom: 6px;
      font-size: 13.5px;
    }
    .prodi-info {
      font-weight: bold;
      color: #000;
    }
    .prodi-info span {
      font-weight: normal;
    }
    .meta-table {
      border-collapse: collapse;
      font-size: 13.5px;
      font-weight: bold;
    }
    .meta-table td {
      padding: 1px 0;
      border: none;
    }
    .meta-label {
      width: 65px;
      text-align: left;
    }
    .meta-sep {
      width: 14px;
      text-align: center;
    }
    .meta-val {
      text-align: left;
      font-weight: normal;
    }

    /* Table Styles */
    .voucher-table {
      width: 100%;
      border-collapse: collapse;
      border: 2px solid #000;
    }
    .voucher-table th {
      background-color: #df5235 !important; /* Coral / Terracotta */
      color: #ffffff !important;
      border: 1.5px solid #000;
      padding: 6px 8px;
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .voucher-table td {
      border-left: 1.5px solid #000;
      border-right: 1.5px solid #000;
      border-bottom: 1px solid #777; /* Solid thin ruled line */
      padding: 4px 8px;
      font-size: 13px;
      height: 25px;
      color: #000;
    }
    .col-no {
      width: 5%;
      text-align: center;
    }
    .col-np {
      width: 9%;
      text-align: center;
      font-weight: 500;
    }
    .col-uraian {
      width: 48%;
      text-align: left;
    }
    .col-jumlah {
      width: 19%;
      text-align: right;
      white-space: nowrap;
    }
    .col-keterangan {
      width: 19%;
      text-align: left;
    }

    /* Currency prefix inside item row */
    .rp-tag {
      float: left;
      font-weight: bold;
      margin-right: 4px;
    }

    /* Total Row */
    .voucher-table tr.total-row td {
      border-top: 1.5px solid #000;
      border-bottom: 2px solid #000;
      height: 30px;
      padding: 4px 8px;
    }
    .total-amount-cell {
      border-bottom: 3px double #000 !important; /* Standard accounting double underline */
      font-weight: bold;
      font-size: 14.5px;
      text-align: right;
    }

    /* Terbilang Box */
    .terbilang-container {
      margin-top: 8px;
      background-color: #f3a712 !important; /* Warm golden amber */
      border: 2px solid #000;
      padding: 6px 12px;
      display: flex;
      align-items: center;
      font-size: 13.5px;
      color: #000;
    }
    .terbilang-title {
      font-weight: bold;
      margin-right: 8px;
      white-space: nowrap;
    }
    .terbilang-content {
      font-weight: bold;
      letter-spacing: 0.3px;
    }

    /* Signatures Section */
    .signatures-table {
      width: 100%;
      margin-top: 35px;
      border-collapse: collapse;
      border: none;
    }
    .signatures-table td {
      border: none;
      padding: 0 4px;
      text-align: center;
      vertical-align: top;
      width: 25%;
    }
    .sign-title {
      font-size: 13.5px;
      font-weight: bold;
      color: #000;
    }
    .sign-space {
      height: 65px; /* Room for handwritten signature */
      vertical-align: bottom !important;
      font-size: 13px;
      font-weight: bold;
      color: #000;
      white-space: nowrap;
      text-decoration: none !important;
    }

    /* Print media overrides */
    @media print {
      body {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .no-print {
        display: none !important;
      }
      .voucher-paper {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
      }
      .voucher-table th {
        background-color: #df5235 !important;
        color: #ffffff !important;
      }
      .terbilang-container {
        background-color: #f3a712 !important;
      }
    }
  </style>
</head>
<body>

  <!-- Screen Utility Bar -->
  <div class="utility-bar no-print">
    <div style="font-weight: bold; display: flex; align-items: center; gap: 8px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      Pratinjau Bukti Transaksi
    </div>
    <div style="display:flex; gap:8px">
      <button class="btn btn-close" onclick="window.close()">
        Tutup
      </button>
      <button class="btn btn-print" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
        Cetak / Unduh PDF
      </button>
    </div>
  </div>

  <!-- Voucher Paper Content -->
  <div class="voucher-paper">
    
    <!-- Header -->
    <div class="voucher-header">
      <div class="logo-container">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" onerror="this.style.display='none'" />
      </div>
      <div class="header-center">
        <h2 class="institution-name">SEKOLAH TINGGI TEKNOLOGI</h2>
        <h2 class="institution-name">PEKERJAAN UMUM</h2>
        <h1 class="voucher-title">{{ $voucherTitle }}</h1>
      </div>
    </div>

    <!-- Subheader: Prodi & Nomor / Tanggal -->
    <div class="voucher-subheader">
      <div class="prodi-info">
        Prodi : <span>{{ $prodi }}</span>
      </div>
      <div>
        <table class="meta-table">
          <tr>
            <td class="meta-label">Nomor</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $voucherNumber }}</td>
          </tr>
          <tr>
            <td class="meta-label">Tanggal</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $date->translatedFormat('d F Y') }}</td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Details Table -->
    <table class="voucher-table">
      <thead>
        <tr>
          <th class="col-no">No</th>
          <th class="col-np">NP</th>
          <th class="col-uraian">URAIAN</th>
          <th class="col-jumlah">JUMLAH</th>
          <th class="col-keterangan">KETERANGAN</th>
        </tr>
      </thead>
      <tbody>
        @php 
          $rowCount = 0; 
        @endphp
        @foreach($items as $item)
          @php $rowCount++; @endphp
          <tr>
            <td class="col-no">{{ $rowCount }}.</td>
            <td class="col-np">{{ $item['np'] ?? '' }}</td>
            <td class="col-uraian">{{ $item['desc'] ?? '' }}</td>
            <td class="col-jumlah">
              <span class="rp-tag">Rp</span>
              {{ number_format((float)($item['amount'] ?? 0), 2, ',', '.') }}
            </td>
            <td class="col-keterangan">{{ $item['ket'] ?? '' }}</td>
          </tr>
        @endforeach

        {{-- Fill empty ruled rows to match standard physical voucher height --}}
        @for($k = $rowCount; $k < $padRows; $k++)
          <tr>
            <td class="col-no">&nbsp;</td>
            <td class="col-np">&nbsp;</td>
            <td class="col-uraian">&nbsp;</td>
            <td class="col-jumlah">&nbsp;</td>
            <td class="col-keterangan">&nbsp;</td>
          </tr>
        @endfor
        
        <!-- Total Row -->
        <tr class="total-row">
          <td class="col-no">&nbsp;</td>
          <td class="col-np">&nbsp;</td>
          <td class="col-uraian">&nbsp;</td>
          <td class="col-jumlah total-amount-cell">
            {{ number_format((float)$totalAmount, 2, ',', '.') }}
          </td>
          <td class="col-keterangan">&nbsp;</td>
        </tr>
      </tbody>
    </table>

    <!-- Terbilang Box -->
    <div class="terbilang-container">
      <span class="terbilang-title">Terbilang :</span>
      <span class="terbilang-content">{{ $formattedTerbilang }}</span>
    </div>

    <!-- Signatures Matrix -->
    <table class="signatures-table">
      <tr>
        <td><div class="sign-title">Menyetujui</div></td>
        <td><div class="sign-title">Memeriksa</div></td>
        <td><div class="sign-title">Bendahara</div></td>
        <td><div class="sign-title">Yang Menerima</div></td>
      </tr>
      <tr>
        <td class="sign-space">{{ $approver }}</td>
        <td class="sign-space">{{ $verifier }}</td>
        <td class="sign-space">{{ $payer }}</td>
        <td class="sign-space">{{ $recipient }}</td>
      </tr>
    </table>

  </div>

</body>
</html>
