<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Job Surplus Defisit Statement (ISAK 335) - {{ $periodFormatted }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 15mm 15mm 15mm;
      @top-right {
        content: "Page " counter(page);
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        font-size: 10px;
        font-weight: bold;
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
      font-size: 10px;
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
    }

    /* Report Header */
    .report-header {
      text-align: center;
      margin-bottom: 18px;
    }
    .company-name {
      font-size: 15px;
      font-weight: 800;
      letter-spacing: 0.5px;
      color: #162252;
      margin-bottom: 2px;
    }
    .company-address {
      font-size: 11px;
      font-style: italic;
      color: #000000;
      margin-bottom: 12px;
    }
    .report-title {
      font-size: 16px;
      font-weight: 800;
      color: #7b1113;
      margin-bottom: 6px;
    }
    .report-period {
      font-size: 14px;
      font-weight: 800;
      color: #7b1113;
      margin-bottom: 16px;
    }

    /* Meta Row */
    .meta-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-size: 10px;
      font-weight: 700;
      margin-bottom: 4px;
    }
    .meta-left {
      line-height: 1.25;
    }
    .meta-right {
      text-align: right;
    }

    /* Financial Table */
    .financial-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.5px;
    }
    .financial-table thead th {
      font-size: 10px;
      font-weight: 700;
      padding: 4px 0;
      border-bottom: 1px solid #000000;
    }
    .financial-table thead th.col-name {
      text-align: left;
    }
    .financial-table thead th.col-amount {
      text-align: right;
      width: 150px;
    }

    /* Job Section */
    .job-block {
      margin-top: 14px;
      page-break-inside: auto;
    }
    .job-header-row td {
      font-weight: 700;
      font-size: 10.5px;
      padding: 10px 0 4px 0;
    }

    /* Category Section Title */
    .section-title-row td {
      font-size: 9.5px;
      font-weight: 600;
      padding: 6px 0 2px 0;
    }

    /* Line items */
    .item-row td.col-name {
      padding: 1.5px 0 1.5px 14px;
    }
    .item-row td.col-amount {
      padding: 1.5px 0;
      text-align: right;
      white-space: nowrap;
    }

    /* Subtotals */
    .total-row td.col-name {
      padding: 4px 0 4px 0;
      font-weight: 600;
    }
    .total-row td.col-amount {
      padding: 4px 0;
      text-align: right;
      font-weight: 600;
      white-space: nowrap;
    }
    .total-row td.col-amount span {
      border-top: 1px solid #000000;
      border-bottom: 1px solid #000000;
      padding: 1px 0;
      display: inline-block;
      min-width: 110px;
    }

    /* Net Profit */
    .net-profit-row td.col-name {
      padding: 8px 0 6px 0;
      font-weight: 700;
      font-size: 10px;
    }
    .net-profit-row td.col-amount {
      padding: 8px 0 6px 0;
      text-align: right;
      font-weight: 700;
      font-size: 10px;
      white-space: nowrap;
    }
    .net-profit-row td.col-amount span {
      border-top: 1px solid #000000;
      border-bottom: 3px double #000000;
      padding: 2px 0;
      display: inline-block;
      min-width: 110px;
    }

    .spacer-row td {
      height: 8px;
    }

    @media print {
      body {
        padding: 0;
      }
      .no-print {
        display: none !important;
      }
      .report-paper {
        max-width: 100% !important;
        padding: 0 !important;
      }
      .meta-right {
        display: none !important; /* Managed by @page counter */
      }
    }
  </style>
</head>
<body>

  <!-- Print Utility Bar -->
  <div class="utility-bar no-print">
    <div class="utility-title">
      Pratinjau Cetak: Job Surplus Defisit Statement (ISAK 335)
    </div>
    <div style="display:flex; gap:8px">
      <button class="btn btn-secondary" onclick="window.close()">
        Tutup Halaman
      </button>
      <button class="btn btn-primary" onclick="window.print()" style="display:inline-flex; align-items:center; gap:6px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
        Cetak / Simpan PDF (A4)
      </button>
    </div>
  </div>

  <!-- Report Paper -->
  <div class="report-paper">
    
    <!-- Report Header -->
    <div class="report-header">
      <div class="company-name">STT Pekerjaan Umum</div>
      <div class="company-address">Jl. Laksamana Malahayati</div>
      <div class="report-title">Job Surplus Defisit Statement (ISAK 335)</div>
      <div class="report-period">{{ $periodFormatted }}</div>
    </div>

    <!-- Meta Information -->
    <div class="meta-row">
      <div class="meta-left">
        <div>{{ $printDate }}</div>
        <div>{{ $printTime }}</div>
      </div>
      <div class="meta-right">
        Page 1
      </div>
    </div>

    <!-- Financial Table -->
    <table class="financial-table">
      <thead>
        <tr>
          <th class="col-name">Account Name</th>
          <th class="col-amount">Selected Period</th>
          <th class="col-amount">Year to Date</th>
        </tr>
      </thead>
      <tbody>
        @foreach($statementData['jobReports'] as $report)
          <!-- JOB HEADER -->
          <tr class="job-header-row">
            <td colspan="3">
              {{ $report['job']->code }} &nbsp;&nbsp;&nbsp; {{ $report['job']->name }}
            </td>
          </tr>

          <!-- INCOME -->
          @if(!empty($report['sections']['Income']))
            <tr class="section-title-row">
              <td colspan="3">Income</td>
            </tr>
            @foreach($report['sections']['Income'] as $item)
              <tr class="item-row">
                <td class="col-name">{{ $item['name'] }}</td>
                <td class="col-amount">
                  {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                </td>
                <td class="col-amount">
                  {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                </td>
              </tr>
            @endforeach
            <tr class="total-row">
              <td class="col-name">Total Income</td>
              <td class="col-amount">
                <span>{{ $report['totals']['income']['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['income']['period']), 2, ',', '.') }}</span>
              </td>
              <td class="col-amount">
                <span>{{ $report['totals']['income']['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['income']['ytd']), 2, ',', '.') }}</span>
              </td>
            </tr>
          @endif

          <!-- COST OF SALES -->
          @if(!empty($report['sections']['Cost of Sales']))
            <tr class="section-title-row">
              <td colspan="3">Cost of Sales</td>
            </tr>
            @foreach($report['sections']['Cost of Sales'] as $item)
              <tr class="item-row">
                <td class="col-name">{{ $item['name'] }}</td>
                <td class="col-amount">
                  {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                </td>
                <td class="col-amount">
                  {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                </td>
              </tr>
            @endforeach
            <tr class="total-row">
              <td class="col-name">Total Cost of Sales</td>
              <td class="col-amount">
                <span>{{ $report['totals']['cos']['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['cos']['period']), 2, ',', '.') }}</span>
              </td>
              <td class="col-amount">
                <span>{{ $report['totals']['cos']['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['cos']['ytd']), 2, ',', '.') }}</span>
              </td>
            </tr>
          @endif

          <!-- EXPENSE -->
          @if(!empty($report['sections']['Expense']))
            <tr class="section-title-row">
              <td colspan="3">Expense</td>
            </tr>
            @foreach($report['sections']['Expense'] as $item)
              <tr class="item-row">
                <td class="col-name">{{ $item['name'] }}</td>
                <td class="col-amount">
                  {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                </td>
                <td class="col-amount">
                  {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                </td>
              </tr>
            @endforeach
            <tr class="total-row">
              <td class="col-name">Total Expense</td>
              <td class="col-amount">
                <span>{{ $report['totals']['expense']['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['expense']['period']), 2, ',', '.') }}</span>
              </td>
              <td class="col-amount">
                <span>{{ $report['totals']['expense']['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['expense']['ytd']), 2, ',', '.') }}</span>
              </td>
            </tr>
          @endif

          <!-- OTHER INCOME -->
          @if(!empty($report['sections']['Other Income']))
            <tr class="section-title-row">
              <td colspan="3">Other Income</td>
            </tr>
            @foreach($report['sections']['Other Income'] as $item)
              <tr class="item-row">
                <td class="col-name">{{ $item['name'] }}</td>
                <td class="col-amount">
                  {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                </td>
                <td class="col-amount">
                  {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                </td>
              </tr>
            @endforeach
            <tr class="total-row">
              <td class="col-name">Total Other Income</td>
              <td class="col-amount">
                <span>{{ $report['totals']['other_income']['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['other_income']['period']), 2, ',', '.') }}</span>
              </td>
              <td class="col-amount">
                <span>{{ $report['totals']['other_income']['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['other_income']['ytd']), 2, ',', '.') }}</span>
              </td>
            </tr>
          @endif

          <!-- OTHER EXPENSE -->
          @if(!empty($report['sections']['Other Expense']))
            <tr class="section-title-row">
              <td colspan="3">Other Expense</td>
            </tr>
            @foreach($report['sections']['Other Expense'] as $item)
              <tr class="item-row">
                <td class="col-name">{{ $item['name'] }}</td>
                <td class="col-amount">
                  {{ $item['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['period']), 2, ',', '.') }}
                </td>
                <td class="col-amount">
                  {{ $item['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($item['ytd']), 2, ',', '.') }}
                </td>
              </tr>
            @endforeach
            <tr class="total-row">
              <td class="col-name">Total Other Expense</td>
              <td class="col-amount">
                <span>{{ $report['totals']['other_expense']['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['other_expense']['period']), 2, ',', '.') }}</span>
              </td>
              <td class="col-amount">
                <span>{{ $report['totals']['other_expense']['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['other_expense']['ytd']), 2, ',', '.') }}</span>
              </td>
            </tr>
          @endif

          <!-- NET SURPLUS (DEFISIT) -->
          <tr class="net-profit-row">
            <td class="col-name">Net Surplus (Defisit)</td>
            <td class="col-amount">
              <span>{{ $report['totals']['net_profit']['period'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['net_profit']['period']), 2, ',', '.') }}</span>
            </td>
            <td class="col-amount">
              <span>{{ $report['totals']['net_profit']['ytd'] < 0 ? '-' : '' }}Rp{{ number_format(abs($report['totals']['net_profit']['ytd']), 2, ',', '.') }}</span>
            </td>
          </tr>

          <tr class="spacer-row"><td colspan="3"></td></tr>
        @endforeach
      </tbody>
    </table>

  </div>

</body>
</html>
