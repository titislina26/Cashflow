<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Job Activity (Summary) - {{ $periodFormatted }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 15mm 15mm 15mm;
      @top-right {
        content: "Page " counter(page);
      }
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
      margin: 0;
      padding: 20px;
      color: #000000;
      background: #ffffff;
      font-size: 11px;
      line-height: 1.35;
    }

    /* Print utility bar (hidden in print) */
    .utility-bar {
      margin-bottom: 24px;
      padding: 12px 20px;
      background: #f1f5f9;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 1px 3px rgba(0,0,0,0.08);
      border: 1px solid #cbd5e1;
    }
    .utility-title {
      font-weight: 700;
      color: #1e293b;
      font-size: 14px;
    }
    .btn {
      padding: 8px 18px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 12px;
      border: 1px solid transparent;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
      transition: background 0.15s ease;
    }
    .btn-secondary {
      background: #ffffff;
      color: #475569;
      border-color: #cbd5e1;
    }
    .btn-secondary:hover {
      background: #f8fafc;
    }
    .btn-primary {
      background: #162252;
      color: #ffffff;
    }
    .btn-primary:hover {
      background: #0d1430;
    }

    /* Report Paper */
    .report-paper {
      width: 100%;
      max-width: 820px;
      margin: 0 auto;
      background: #ffffff;
      padding: 10px 0;
    }

    /* Report Header */
    .report-header {
      text-align: center;
      margin-bottom: 12px;
    }
    .company-name {
      font-size: 16px;
      font-weight: 800;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      color: #000000;
    }
    .company-address {
      font-size: 12px;
      color: #000000;
      margin-top: 2px;
      margin-bottom: 16px;
    }
    .report-title {
      font-size: 16px;
      font-weight: 800;
      color: #7b1113;
      margin-bottom: 8px;
    }
    .report-period {
      font-size: 15px;
      font-weight: 800;
      color: #7b1113;
      margin-bottom: 18px;
    }

    /* Meta Row */
    .meta-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-size: 11px;
      font-weight: 700;
      margin-bottom: 4px;
    }
    .meta-left {
      line-height: 1.25;
    }
    .meta-right {
      text-align: right;
    }

    /* Main Table Layout */
    .table-header {
      display: flex;
      border-bottom: 1px solid #000000;
      padding-bottom: 4px;
      margin-bottom: 12px;
      font-weight: 700;
      font-size: 11px;
    }
    .col-name {
      flex: 1;
      text-align: left;
    }
    .col-debit {
      width: 140px;
      text-align: right;
    }
    .col-credit {
      width: 150px;
      text-align: right;
    }
    .col-net {
      width: 160px;
      text-align: right;
    }

    /* Category Block */
    .category-block {
      margin-bottom: 16px;
      page-break-inside: avoid;
    }
    .category-title {
      font-weight: 700;
      font-size: 11px;
      margin-bottom: 6px;
      color: #000000;
    }

    .job-table {
      width: 100%;
      border-collapse: collapse;
    }
    .job-table td {
      padding: 2px 0;
      vertical-align: middle;
      font-size: 11px;
    }
    .job-name {
      padding-left: 28px !important;
      display: flex;
      align-items: center;
    }
    .job-code {
      display: inline-block;
      width: 36px;
      flex-shrink: 0;
    }

    /* Category Total */
    .total-row td {
      padding-top: 6px !important;
      padding-bottom: 4px !important;
    }
    .total-label {
      text-align: right;
      padding-right: 28px !important;
      font-weight: 400;
    }
    .total-amount {
      border-top: 1px solid #000000;
      border-bottom: 1px solid #000000;
      font-weight: 400;
    }

    /* Grand Total */
    .grand-total-block {
      margin-top: 24px;
      page-break-inside: avoid;
    }
    .grand-total-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11px;
      font-weight: 700;
    }
    .grand-total-table td {
      padding: 8px 0;
    }
    .grand-label {
      text-align: right;
      padding-right: 28px !important;
    }
    .grand-amount {
      border-top: 1px solid #000000;
      border-bottom: 3px double #000000;
    }

    /* Print media styling */
    @media print {
      body {
        padding: 0;
        background: #ffffff;
      }
      .no-print {
        display: none !important;
      }
      .report-paper {
        max-width: 100%;
        margin: 0;
        padding: 0;
      }
      .category-block {
        page-break-inside: avoid;
      }
      .grand-total-block {
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body>

  <!-- Utility Bar (Hidden during print) -->
  <div class="utility-bar no-print">
    <div class="utility-title">Pratinjau Cetak: Job Activity (Summary)</div>
    <div style="display:flex; gap:10px;">
      <button class="btn btn-secondary" onclick="window.close()">Tutup</button>
      <button class="btn btn-primary" onclick="window.print()" style="display:inline-flex; align-items:center; gap:6px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
        Cetak / Simpan PDF (A4)
      </button>
    </div>
  </div>

  <div class="report-paper">
    <!-- Header -->
    <div class="report-header">
      <div class="company-name">STT Pekerjaan Umum</div>
      <div class="company-address">Jl. Laksamana Malahayati</div>
      <div class="report-title">Job Activity (Summary)</div>
      <div class="report-period">{{ $periodFormatted }}</div>
    </div>

    <!-- Metadata Row -->
    <div class="meta-row">
      <div class="meta-left">
        <div>{{ $printDate }}</div>
        <div>{{ $printTime }}</div>
      </div>
      <div class="meta-right">
        Page 1
      </div>
    </div>

    <!-- Table Header Columns -->
    <div class="table-header">
      <div class="col-name">Name</div>
      <div class="col-debit">Debit</div>
      <div class="col-credit">Credit</div>
      <div class="col-net">Net Activity</div>
    </div>

    <!-- Categories and Job Activities -->
    @if(empty($activitySummary))
      <div style="text-align: center; padding: 40px; color: #64748b;">
        Tidak ada transaksi aktivitas proyek pada periode ini (Kategori 4-0000 s/d 9-5599).
      </div>
    @else
      @foreach($activitySummary as $catData)
        <div class="category-block">
          <div class="category-title">
            {{ $catData['category']->code ? $catData['category']->code . ' ' : '' }}{{ $catData['category']->name }}
          </div>
          
          <table class="job-table">
            <tbody>
              @foreach($catData['jobs'] as $jData)
                <tr>
                  <td class="col-name">
                    <div class="job-name">
                      <span class="job-code">{{ $jData['job']->code }}</span>
                      <span>{{ $jData['job']->name }}</span>
                    </div>
                  </td>
                  <td class="col-debit">Rp{{ number_format($jData['debit'], 2, ',', '.') }}</td>
                  <td class="col-credit">Rp{{ number_format($jData['credit'], 2, ',', '.') }}</td>
                  <td class="col-net">Rp{{ number_format($jData['net'], 2, ',', '.') }}{{ $jData['isCredit'] ? ' cr' : '' }}</td>
                </tr>
              @endforeach
              
              <!-- Total per category -->
              <tr class="total-row">
                <td class="col-name total-label">Total:</td>
                <td class="col-debit total-amount">Rp{{ number_format($catData['total_debit'], 2, ',', '.') }}</td>
                <td class="col-credit total-amount">Rp{{ number_format($catData['total_credit'], 2, ',', '.') }}</td>
                <td class="col-net total-amount">Rp{{ number_format($catData['total_net'], 2, ',', '.') }}{{ $catData['is_credit'] ? ' cr' : '' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      @endforeach

      <!-- Grand Total -->
      <div class="grand-total-block">
        <table class="grand-total-table">
          <tr>
            <td class="col-name grand-label">Grand Total:</td>
            <td class="col-debit grand-amount">{{ number_format($grandTotalDebit, 2, ',', '.') }}</td>
            <td class="col-credit grand-amount">{{ number_format($grandTotalCredit, 2, ',', '.') }}</td>
            <td class="col-net grand-amount">Rp{{ number_format($grandNet, 2, ',', '.') }}{{ $isGrandCredit ? ' cr' : '' }}</td>
          </tr>
        </table>
      </div>
    @endif
  </div>

</body>
</html>
