@php
  \Carbon\Carbon::setLocale('id');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Neraca - Sekolah Tinggi Teknologi Pekerjaan Umum</title>
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

    /* Print utility bar */
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
      letter-spacing: 0.5px;
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
      transition: all 0.2s;
    }
    .btn-secondary {
      background: #334155;
      color: #f8fafc;
      border-color: #475569;
    }
    .btn-secondary:hover {
      background: #475569;
    }
    .btn-primary {
      background: #0284c7;
      color: #fff;
    }
    .btn-primary:hover {
      background: #0369a1;
    }

    /* Sheet / Paper Container */
    .sheet-container {
      width: 100%;
      max-width: 860px;
      margin: 0 auto;
      background: #fff;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      border: 1px solid #cbd5e1;
    }

    .page {
      padding: 30px 40px;
      background: #fff;
      min-height: 1100px;
      position: relative;
    }

    .page-break {
      page-break-before: always;
      break-before: page;
    }

    /* Header Page 1 */
    .header-table {
      width: 100%;
      margin-bottom: 14px;
      border-collapse: collapse;
    }
    .header-logo-cell {
      width: 90px;
      vertical-align: middle;
      text-align: left;
    }
    .header-logo {
      width: 75px;
      height: 75px;
      object-fit: contain;
    }
    .header-title-cell {
      vertical-align: middle;
      text-align: center;
      padding-right: 75px; /* balance logo on left */
    }
    .inst-title {
      font-size: 12.5px;
      font-weight: 800;
      letter-spacing: 0.5px;
      margin: 0;
      text-transform: uppercase;
    }
    .doc-title {
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 1.5px;
      margin: 3px 0 0 0;
      text-transform: uppercase;
    }
    .period-title {
      font-size: 11px;
      font-weight: 800;
      margin: 3px 0 0 0;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Main Neraca Table */
    .neraca-table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      font-size: 9.5px;
    }
    .neraca-table th, .neraca-table td {
      border: 1px solid #000;
      padding: 2.5px 4px;
    }
    .neraca-table th {
      font-weight: 800;
      text-align: center;
      text-transform: uppercase;
      background: #fff;
    }
    .neraca-table td.cat-col {
      width: 26px;
      text-align: center;
      font-weight: 600;
      padding-left: 0;
      padding-right: 0;
    }
    .neraca-table td.curr-col {
      width: 20px;
      text-align: left;
      border-right: none !important;
      padding-right: 0;
      font-weight: 500;
    }
    .neraca-table td.num-col {
      width: 82px;
      text-align: right;
      border-left: none !important;
      font-weight: 500;
      white-space: nowrap;
    }
    .neraca-table td.desc-col {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .section-head {
      font-weight: 800;
      text-transform: uppercase;
    }
    .bold-row td {
      font-weight: 700;
    }
    .double-top {
      border-top: 1.5px solid #000 !important;
    }

    /* Signatures Matrix Page 1 */
    .signatures-container {
      margin-top: 24px;
      width: 100%;
      display: table;
      table-layout: fixed;
      font-size: 10px;
      line-height: 1.35;
    }
    .sig-col {
      display: table-cell;
      vertical-align: top;
      text-align: center;
    }
    .sig-title {
      font-weight: 700;
      margin-bottom: 2px;
    }
    .sig-space {
      height: 52px;
    }
    .sig-name {
      font-weight: 700;
      display: inline-block;
    }

    /* Page 2: Keterangan */
    .notes-title {
      font-size: 11px;
      font-weight: 800;
      margin-bottom: 14px;
      text-transform: uppercase;
    }
    .note-row {
      display: flex;
      margin-bottom: 8px;
      font-size: 9.5px;
      line-height: 1.35;
    }
    .note-num {
      width: 24px;
      flex-shrink: 0;
      font-weight: 700;
      text-align: left;
    }
    .note-content {
      flex: 1;
    }
    
    .note-subtable {
      border-collapse: collapse;
      margin-top: 2px;
      width: 100%;
      max-width: 580px;
    }
    .note-subtable td {
      padding: 1.5px 0;
      font-size: 9.5px;
    }
    .note-subtable td.label-col {
      width: 380px;
      padding-right: 15px;
    }
    .note-subtable td.rp-col {
      width: 24px;
      text-align: left;
    }
    .note-subtable td.amt-col {
      width: 95px;
      text-align: right;
      white-space: nowrap;
    }
    .note-subtable tr.sub-total td {
      font-weight: 700;
    }

    @media print {
      body {
        background: #fff !important;
        padding: 0 !important;
      }
      .no-print {
        display: none !important;
      }
      .sheet-container {
        max-width: 100% !important;
        box-shadow: none !important;
        border: none !important;
      }
      .page {
        padding: 10mm 12mm !important;
        min-height: auto !important;
      }
      .page-break {
        page-break-before: always !important;
        break-before: page !important;
      }
    }
  </style>
</head>
<body>

  <!-- Top Action Bar for Browser Preview -->
  <div class="utility-bar no-print">
    <div class="utility-title">Neraca Keuangan - Sekolah Tinggi Teknologi Pekerjaan Umum</div>
    <div style="display:flex; gap:10px">
      <button class="btn btn-secondary" onclick="window.close()">
        Tutup
      </button>
      <button class="btn btn-primary" onclick="window.print()" style="display:inline-flex; align-items:center; gap:6px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
        Cetak / Simpan PDF
      </button>
    </div>
  </div>

  <div class="sheet-container">
    
    <!-- ==================== HALAMAN 1 : NERACA ==================== -->
    <div class="page">
      
      <!-- Institutional Header -->
      <table class="header-table">
        <tr>
          <td class="header-logo-cell">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo STT PU" class="header-logo" />
          </td>
          <td class="header-title-cell">
            <div class="inst-title">SEKOLAH TINGGI TEKNOLOGI PEKERJAAN UMUM - JAKARTA</div>
            <div class="doc-title">NERACA</div>
            <div class="period-title">PERIODE {{ strtoupper($startCarbon->locale('id')->isoFormat('DD MMMM Y')) }} S/D {{ strtoupper($endCarbon->locale('id')->isoFormat('DD MMMM Y')) }}</div>
          </td>
        </tr>
      </table>

      <!-- Main Scontro Neraca Table -->
      <table class="neraca-table">
        <thead>
          <tr>
            <th colspan="3" style="width: 50%;">AKTIVA</th>
            <th class="cat-col">Cat</th>
            <th colspan="3" style="width: 50%;">PASIVA</th>
            <th class="cat-col">Cat</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sub-Headers: AKTIVA LANCAR & HUTANG LANCAR -->
          <tr>
            <td class="section-head desc-col">AKTIVA LANCAR</td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
            <td class="section-head desc-col">HUTANG LANCAR</td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 1: Kas & Biaya YMHD -->
          <tr>
            <td class="desc-col">11110&nbsp;&nbsp;Kas</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($kas, 2, ',', '.') }}</td>
            <td class="cat-col">1</td>
            <td class="desc-col">21399&nbsp;&nbsp;Biaya YMHD lainnya</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($biayaYMHD, 2, ',', '.') }}</td>
            <td class="cat-col">11</td>
          </tr>

          <!-- Row 2: Bank Mandiri Giro I & Hutang Unit Lainnya -->
          <tr>
            <td class="desc-col">11121&nbsp;&nbsp;Bank Mandiri Giro I</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($bankMandiri1, 2, ',', '.') }}</td>
            <td class="cat-col">2</td>
            <td class="desc-col">22220&nbsp;&nbsp;Hutang Unit Lainnya</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($hutangUnit, 2, ',', '.') }}</td>
            <td class="cat-col">12</td>
          </tr>

          <!-- Row 3: Bank Mandiri Giro II & Hutang YPP Pusat -->
          <tr>
            <td class="desc-col">11122&nbsp;&nbsp;Bank Mandiri Giro II</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($bankMandiri2, 2, ',', '.') }}</td>
            <td class="cat-col">3</td>
            <td class="desc-col">22209&nbsp;&nbsp;Hutang YPP Pusat</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($hutangYPP, 2, ',', '.') }}</td>
            <td class="cat-col">13</td>
          </tr>

          <!-- Row 4: Biaya Dibayar Dimuka & Hutang Imbalan Pasca Kerja -->
          <tr>
            <td class="desc-col">11599&nbsp;&nbsp;Biaya Dibayar Dimuka</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($biayaDimuka, 2, ',', '.') }}</td>
            <td class="cat-col">4</td>
            <td class="desc-col">21119&nbsp;&nbsp;Hutang Imbalan Pasca Kerja</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($hutangPasca, 2, ',', '.') }}</td>
            <td class="cat-col">14</td>
          </tr>

          <!-- Row 5: Total Aktiva Lancar & Total Hutang Lancar -->
          <tr class="bold-row">
            <td class="desc-col" style="padding-left: 28px;">Jumlah Aktiva Lancar</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($jumlahAktivaLancar, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
            <td class="desc-col" style="padding-left: 28px;">Jumlah Hutang Lancar</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($jumlahHutangLancar, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 6: Sub-Headers AKTIVA TETAP & EKUITAS -->
          <tr>
            <td class="section-head desc-col">AKTIVA TETAP</td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
            <td class="section-head desc-col">EKUITAS</td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 7: Gedung -->
          <tr>
            <td class="desc-col">12310&nbsp;&nbsp;Gedung</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($gedung, 2, ',', '.') }}</td>
            <td class="cat-col">5</td>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 8: Akm Peny Gedung -->
          <tr>
            <td class="desc-col">12311&nbsp;&nbsp;Akm Peny Gedung</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($akmGedung, 2, ',', '.') }}</td>
            <td class="cat-col">6</td>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 9: Nilai buku Gedung & Modal Donasi -->
          <tr>
            <td class="desc-col" style="padding-left: 24px; font-weight: 600;">Nilai buku Gedung</td>
            <td class="curr-col" style="font-weight: 600;">Rp</td>
            <td class="num-col" style="font-weight: 600;">{{ number_format($nbGedung, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
            <td class="desc-col">31210&nbsp;&nbsp;Modal Donasi</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($modalDonasi, 2, ',', '.') }}</td>
            <td class="cat-col">15</td>
          </tr>

          <!-- Row 10: Total Modal Donasi -->
          <tr>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
            <td class="desc-col" style="padding-left: 28px; font-weight: 600;">Total Modal Donasi</td>
            <td class="curr-col" style="font-weight: 600;">Rp</td>
            <td class="num-col" style="font-weight: 600;">{{ number_format($modalDonasi, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 11: Inventaris Kantor -->
          <tr>
            <td class="desc-col">12120&nbsp;&nbsp;Inventaris Kantor</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($invKantor, 2, ',', '.') }}</td>
            <td class="cat-col">7</td>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 12: Akum Peny Inv. Kantor -->
          <tr>
            <td class="desc-col">12121&nbsp;&nbsp;Akum Peny Inv. Kantor</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($akmInvKantor, 2, ',', '.') }}</td>
            <td class="cat-col">8</td>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 13: Nilai buku Inventaris Kantor & Surplus Th Lalu -->
          <tr>
            <td class="desc-col" style="padding-left: 24px; font-weight: 600;">Nilai buku Inventaris Kantor</td>
            <td class="curr-col" style="font-weight: 600;">Rp</td>
            <td class="num-col" style="font-weight: 600;">{{ number_format($nbInv, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
            <td class="desc-col">33110&nbsp;&nbsp;Surplus (Minus) tahun lalu</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">({{ number_format(abs($surplusThLalu), 2, ',', '.') }})</td>
            <td class="cat-col">16</td>
          </tr>

          <!-- Row 14: Surplus Th Berjalan -->
          <tr>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
            <td class="desc-col">33120&nbsp;&nbsp;Surplus (Minus) tahun berjalan</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">({{ number_format(abs($surplusThBerjalan), 2, ',', '.') }})</td>
            <td class="cat-col">17</td>
          </tr>

          <!-- Row 15: Peralatan Laboratorium -->
          <tr>
            <td class="desc-col">12130&nbsp;&nbsp;Peralatan Laboratorium</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($peralatanLab, 2, ',', '.') }}</td>
            <td class="cat-col">9</td>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 16: Akum Peny Peralatan Lab. -->
          <tr>
            <td class="desc-col">12131&nbsp;&nbsp;Akum Peny Peralatan Lab.</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($akmLab, 2, ',', '.') }}</td>
            <td class="cat-col">10</td>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 17: Nilai buku Peralatan Lab -->
          <tr>
            <td class="desc-col" style="padding-left: 24px; font-weight: 600;">Nilai buku Peralatan Lab</td>
            <td class="curr-col" style="font-weight: 600;">Rp</td>
            <td class="num-col" style="font-weight: 600;">{{ number_format($nbLab, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
            <td class="desc-col"></td>
            <td class="curr-col"></td>
            <td class="num-col"></td>
            <td class="cat-col"></td>
          </tr>

          <!-- Row 18: Jumlah Aktiva Tetap & JUMLAH EKUITAS -->
          <tr class="bold-row">
            <td class="desc-col" style="padding-left: 28px;">Jumlah Aktiva Tetap</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($jumlahAktivaTetap, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
            <td class="desc-col">JUMLAH EKUITAS</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($jumlahEkuitas, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
          </tr>

          <!-- Final Total Row: JUMLAH AKTIVA & JUMLAH PASIVA -->
          <tr class="bold-row double-top" style="font-size: 10px;">
            <td class="desc-col">JUMLAH AKTIVA</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($jumlahAktiva, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
            <td class="desc-col">JUMLAH PASIVA</td>
            <td class="curr-col">Rp</td>
            <td class="num-col">{{ number_format($jumlahPasiva, 2, ',', '.') }}</td>
            <td class="cat-col"></td>
          </tr>
        </tbody>
      </table>

      <!-- Date Row above Signatures -->
      <div style="text-align: right; margin-top: 18px; margin-bottom: 6px; padding-right: 15px; font-size: 10px;">
        Jakarta, {{ $endCarbon->locale('id')->translatedFormat('d F Y') }}
      </div>

      <!-- Signatures Matrix Exactly matching the Document -->
      <div class="signatures-container" style="margin-top: 0;">
        <!-- Col 1: Menyetujui Waket II -->
        <div class="sig-col" style="width: 32%;">
          <div class="sig-title">Menyetujui</div>
          <div>Waket II</div>
          <div class="sig-space"></div>
          <div class="sig-name">{{ $approver }}</div>
        </div>

        <!-- Col 2: Memeriksa & Mengetahui -->
        <div class="sig-col" style="width: 36%;">
          <div class="sig-title">Memeriksa</div>
          <div>Kabag. Keuangan dan Personalia</div>
          <div style="height: 40px;"></div>
          <div class="sig-name">{{ $verifier }}</div>

          <div style="margin-top: 14px;">
            <div class="sig-title">Mengetahui</div>
            <div style="height: 40px;"></div>
            <div class="sig-name">{{ $leader }}</div>
          </div>
        </div>

        <!-- Col 3: Dibuat oleh -->
        <div class="sig-col" style="width: 32%;">
          <div class="sig-title">Dibuat oleh,</div>
          <div>Staf. Adm Umum & Keuangan</div>
          <div class="sig-space"></div>
          <div class="sig-name">{{ $maker }}</div>
        </div>
      </div>

    </div>

    <!-- ==================== HALAMAN 2 : KETERANGAN ==================== -->
    <div class="page page-break">
      
      <div class="notes-title">
        KETERANGAN : ( Neraca, Periode {{ $startCarbon->locale('id')->isoFormat('DD MMMM Y') }} s/d {{ $endCarbon->locale('id')->isoFormat('DD MMMM Y') }} )
      </div>

      <!-- Note 1 -->
      <div class="note-row">
        <div class="note-num">1.</div>
        <div class="note-content">
          Jumlah Kas sebesar Rp. {{ number_format($kas, 2, ',', '.') }} sesuai dengan Kas On Hand Rp. 3.034.450, 00 dan Kas Kecil ( BTN ) sebesar Rp. 3.161.910 per {{ $endCarbon->translatedFormat('d F Y') }}.
        </div>
      </div>

      <!-- Note 2 -->
      <div class="note-row">
        <div class="note-num">2.</div>
        <div class="note-content">
          Jumlah Bank Mandiri Giro I Rp. {{ number_format($bankMandiri1, 2, ',', '.') }} sesuai dengan Rekening Koran per {{ $endCarbon->translatedFormat('d F Y') }}.
        </div>
      </div>

      <!-- Note 3 -->
      <div class="note-row">
        <div class="note-num">3.</div>
        <div class="note-content">
          Jumlah Bank Mandiri Giro II Rp. {{ number_format($bankMandiri2, 2, ',', '.') }} sesuai dengan Rekening Koran per {{ $endCarbon->translatedFormat('d F Y') }}.
        </div>
      </div>

      <!-- Note 4 -->
      <div class="note-row">
        <div class="note-num">4.</div>
        <div class="note-content">
          Jumlah Biaya Dibayar Dimuka per {{ $endCarbon->translatedFormat('d F Y') }} sebesar Rp. {{ number_format($biayaDimuka, 2, ',', '.') }} adalah Uang Muka Penelitian yang sedang berjalan, sehingga belum di pertanggungjawabkan dan Uang Transportasi dan Uang Saku Dosen Klas PDAM yang belum di pertanggungjawabkan.
        </div>
      </div>

      <!-- Note 5 -->
      <div class="note-row">
        <div class="note-num">5.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr>
              <td class="label-col">Gedung Lama</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">347.658.200</td>
            </tr>
            <tr>
              <td class="label-col">Renovasi Gedung</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">16.812.000.000</td>
            </tr>
            <tr class="sub-total">
              <td class="label-col">Jumlah Renovasi Gedung per {{ $endCarbon->translatedFormat('d F Y') }}</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">{{ number_format($gedung, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 6 -->
      <div class="note-row">
        <div class="note-num">6.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr>
              <td class="label-col">Akumulasi Penyusutan Gedung</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">767.202.443</td>
            </tr>
            <tr>
              <td class="label-col">Penyusutan Gedung Tahun Berjalan</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">1.509.163.377</td>
            </tr>
            <tr class="sub-total">
              <td class="label-col">Jumlah Akum. Penyusutan Gedung per {{ $endCarbon->translatedFormat('d F Y') }}</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">{{ number_format($akmGedung, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 7 -->
      <div class="note-row">
        <div class="note-num">7.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr>
              <td class="label-col">Inventaris Kantor Beli & Bantuan (Lama)</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">75.924.477</td>
            </tr>
            <tr>
              <td class="label-col">Inventaris Kantor ( Bantuan / HIBAH )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">1.399.015.272</td>
            </tr>
            <tr>
              <td class="label-col">Pembelian Inventaris Kantor STT PU Tahun Berjalan</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">114.182.060</td>
            </tr>
            <tr class="sub-total">
              <td class="label-col">Jumlah Inventaris Kantor per {{ $endCarbon->translatedFormat('d F Y') }}</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">{{ number_format($invKantor, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 8 -->
      <div class="note-row">
        <div class="note-num">8.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr>
              <td class="label-col">Akum. Penyusutan Inventaris Kantor STT PU</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">81.834.986</td>
            </tr>
            <tr>
              <td class="label-col">Penyusutan Inventaris Kantor STT PU Tahun Berjalan</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">380.808.116</td>
            </tr>
            <tr class="sub-total">
              <td class="label-col">Jumlah Akum Penyusutan Inv. Ktr per {{ $endCarbon->translatedFormat('d F Y') }}</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">{{ number_format($akmInvKantor, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 9 -->
      <div class="note-row">
        <div class="note-num">9.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr>
              <td class="label-col">Peralatan Laboratorium Lama</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">-</td>
            </tr>
            <tr>
              <td class="label-col">Peralatan Laboratorium ( Bantuan / HIBAH )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">423.194.540</td>
            </tr>
            <tr>
              <td class="label-col">Pembelian Peralatan Laboratorium STT PU Tahun Berjalan</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">22.028.610</td>
            </tr>
            <tr class="sub-total">
              <td class="label-col">Jumlah Peralatan Laboratorium per {{ $endCarbon->translatedFormat('d F Y') }}</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">{{ number_format($peralatanLab, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 10 -->
      <div class="note-row">
        <div class="note-num">10.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr>
              <td class="label-col">Akum. Penyusutan Peralatan Laboratorium STT PU</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">-</td>
            </tr>
            <tr>
              <td class="label-col">Penyusutan Laboratorium STT PU Tahun Berjalan</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">29.229.155</td>
            </tr>
            <tr class="sub-total">
              <td class="label-col">Jumlah Akum Penyusutan Peralatan Lab per {{ $endCarbon->translatedFormat('d F Y') }}</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">{{ number_format($akmLab, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 11 -->
      <div class="note-row">
        <div class="note-num">11.</div>
        <div class="note-content">
          Biaya YMHD lainnya per {{ $endCarbon->translatedFormat('d F Y') }} sebesar Rp. {{ number_format($biayaYMHD, 2, ',', '.') }} adalah cadangan Biaya Pengembangan Institusi sesuai kebijakan Managemen dan Honor Dosen Tidak Tetap yang belum terbayarkan.
        </div>
      </div>

      <!-- Note 12 -->
      <div class="note-row">
        <div class="note-num">12.</div>
        <div class="note-content">
          Hutang Unit Lainnya sebesar Rp. {{ number_format($hutangUnit, 2, ',', '.') }} adalah pinjaman dari luar Yayasan per {{ $endCarbon->translatedFormat('d F Y') }}.
        </div>
      </div>

      <!-- Note 13 -->
      <div class="note-row">
        <div class="note-num">13.</div>
        <div class="note-content">
          Hutang YPP Pusat sebesar Rp. {{ number_format($hutangYPP, 2, ',', '.') }} adalah pinjaman dari Yayasan Pendidikan Putra untuk mendanai Biaya Operasional STT PU per {{ $endCarbon->translatedFormat('d F Y') }}.
        </div>
      </div>

      <!-- Note 14 -->
      <div class="note-row">
        <div class="note-num">14.</div>
        <div class="note-content">
          Hutang Imbalan Pasca Kerja sebesar Rp. {{ number_format($hutangPasca, 2, ',', '.') }} adalah Kewajiban Finansial Institusi STT PU untuk memberikan pembayaran kepada karyawan, setelah mereka berhenti bekerja atau pensiun. per {{ $endCarbon->translatedFormat('d F Y') }}.
        </div>
      </div>

      <!-- Note 15 -->
      <div class="note-row">
        <div class="note-num">15.</div>
        <div class="note-content">
          <div>Modal Donasi sebesar Rp. {{ number_format($modalDonasi, 2, ',', '.') }} adalah bantuan dari luar yang berupa Inventaris Kantor, Peralatan Laboratorium dan Renovasi Gedung dengan perincian sbb:</div>
          <table class="note-subtable" style="margin-top: 4px;">
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi Awal</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">152.758.000</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( HIBAH KOPERTIS )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">494.467.400</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( HIBAH YPP )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">313.423.000</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( HIBAH PT. BRANTAS ABIPRAYA )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">5.200.000</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( HIBAH Alumni )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">7.598.000</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( HIBAH Dirjen Cipta Karya Kementerian PU )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">16.812.000.000</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( HIBAH PT Brantas Abipraya dan Dirjen SDA )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">1.399.015.272</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( Pimpinan )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">152.594.540</td>
            </tr>
            <tr>
              <td class="label-col" style="padding-left: 10px;">- Modal Donasi ( Pimpinan )</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">270.600.000</td>
            </tr>
            <tr class="sub-total">
              <td class="label-col">Jumlah Modal Donasi per 31 Desember 2024.</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">{{ number_format($modalDonasi, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 16 -->
      <div class="note-row">
        <div class="note-num">16.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr class="sub-total">
              <td class="label-col">Surplus ( Minus ) Tahun Lalu</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">({{ number_format(abs($surplusThLalu), 2, ',', '.') }})</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Note 17 -->
      <div class="note-row">
        <div class="note-num">17.</div>
        <div class="note-content">
          <table class="note-subtable">
            <tr class="sub-total">
              <td class="label-col">Surplus ( Minus ) Tahun Berjalan</td>
              <td class="rp-col">Rp</td>
              <td class="amt-col">({{ number_format(abs($surplusThBerjalan), 2, ',', '.') }})</td>
            </tr>
          </table>
        </div>
      </div>

    </div>

  </div>

</body>
</html>
