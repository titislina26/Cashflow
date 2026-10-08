@extends('layouts.app')

@section('title', 'Dashboard — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <h2>Dashboard 
        <span class="account-badge" data-account="{{ $activeAccount }}">
          @if($activeAccount === 'petty_cash')
            <x-lucide-wallet /> Kas Kecil
          @elseif($activeAccount === 'bank_mandiri_1')
            <x-lucide-landmark /> Bank Mandiri 1
          @else
            <x-lucide-building-2 /> Bank Mandiri 2
          @endif
        </span>
      </h2>
      <p>Ringkasan performa finansial dan arus kas — {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
    </div>
    <div class="flex gap-1">
      <button class="btn btn-primary" id="btn-add-tx" style="cursor:pointer">
        <x-lucide-plus /> Transaksi Baru
      </button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="summary-grid">
    <div class="summary-card balance" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(168, 85, 247, 0.12)); border: 1px solid rgba(139, 92, 246, 0.25);">
      <div class="summary-card-header">
        <span class="summary-card-label">Saldo Aktif ({{ $activeAccount === 'petty_cash' ? 'Kas Kecil' : ($activeAccount === 'bank_mandiri_1' ? 'Mandiri 1' : 'Mandiri 2') }})</span>
        <div class="summary-card-icon" style="color: #8b5cf6;">
          @if($activeAccount === 'petty_cash')
            <x-lucide-wallet />
          @elseif($activeAccount === 'bank_mandiri_1')
            <x-lucide-landmark />
          @else
            <x-lucide-building-2 />
          @endif
        </div>
      </div>
      <div class="summary-card-value">
        @if($activeAccount === 'petty_cash')
            Rp {{ number_format($pettyCashBalance, fmod($pettyCashBalance, 1) !== 0.0 ? 2 : 0, ',', '.') }}
        @elseif($activeAccount === 'bank_mandiri_1')
            Rp {{ number_format($bankMandiri1Balance, fmod($bankMandiri1Balance, 1) !== 0.0 ? 2 : 0, ',', '.') }}
        @else
            Rp {{ number_format($bankMandiri2Balance, fmod($bankMandiri2Balance, 1) !== 0.0 ? 2 : 0, ',', '.') }}
        @endif
      </div>
      <div class="summary-card-sub">Khusus rekening aktif saat ini</div>
    </div>

    <div class="summary-card income" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.12)); border: 1px solid rgba(16, 185, 129, 0.25);">
      <div class="summary-card-header">
        <span class="summary-card-label">Total Keseluruhan Saldo Kas</span>
        <div class="summary-card-icon" style="color: #10b981;"><x-lucide-coins /></div>
      </div>
      <div class="summary-card-value">Rp {{ number_format($totalSaldoKas, fmod($totalSaldoKas, 1) !== 0.0 ? 2 : 0, ',', '.') }}</div>
      <div class="summary-card-sub">Konsolidasi Kas Kecil + Mandiri 1 + Mandiri 2</div>
    </div>

    <div class="summary-card expense" style="background: linear-gradient(135deg, rgba(244, 63, 94, 0.12), rgba(225, 29, 72, 0.12)); border: 1px solid rgba(244, 63, 94, 0.25);">
      <div class="summary-card-header">
        <span class="summary-card-label">Total Pengeluaran Bulan Ini</span>
        <div class="summary-card-icon" style="color: #f43f5e;"><x-lucide-trending-down /></div>
      </div>
      <div class="summary-card-value">Rp {{ number_format($thisMonthExpense, fmod($thisMonthExpense, 1) !== 0.0 ? 2 : 0, ',', '.') }}</div>
      <div class="summary-card-sub">
        @if($expenseGrowth > 0)
          <span style="color:#ef4444; font-weight:600;">↑ {{ number_format(abs($expenseGrowth), 1) }}%</span> vs bulan lalu
        @elseif($expenseGrowth < 0)
          <span style="color:#10b981; font-weight:600;">↓ {{ number_format(abs($expenseGrowth), 1) }}%</span> vs bulan lalu
        @else
          <span style="color:var(--text-muted);">0.0%</span> vs bulan lalu
        @endif
      </div>
    </div>

    <div class="summary-card count" id="btn-panjar-recap" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(217, 70, 239, 0.12)); border: 1px solid rgba(245, 158, 11, 0.3); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
      <div class="summary-card-header">
        <span class="summary-card-label">Panjar Kerja Aktif (ACC 11599)</span>
        <div class="summary-card-icon" style="color: #f59e0b;"><x-lucide-hourglass /></div>
      </div>
      <div class="summary-card-value" style="color: #d97706;">Rp {{ number_format($totalPanjarAktif, fmod($totalPanjarAktif, 1) !== 0.0 ? 2 : 0, ',', '.') }}</div>
      <div class="summary-card-sub" style="display:flex; align-items:center; justify-content:space-between; gap:4px; color:#b45309;">
        <span style="display:flex; align-items:center; gap:4px;">Klik untuk rekap panjar <x-lucide-chevron-right style="width:14px; height:14px;" /></span>
        @if(isset($overduePanjarCount) && $overduePanjarCount > 0)
          <span class="badge" style="background:#fee2e2; color:#b91c1c; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:9999px; border:1px solid #fca5a5;">
            ⚠️ {{ $overduePanjarCount }} Panjar &gt; 30 Hari
          </span>
        @endif
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="charts-grid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Tren Arus Kas (6 Bulan Terakhir)</h3>
      </div>
      <div class="chart-container">
        <canvas id="chart-cashflow"></canvas>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Pengeluaran per Kategori (Bulan Ini)</h3>
      </div>
      <div class="chart-container" id="expense-chart-wrapper">
        @if(count($expenseCategories) > 0)
          <canvas id="chart-category"></canvas>
        @else
          <div class="empty-state" style="padding:40px 0">
            <div class="empty-state-icon" style="color:var(--text-muted);"><x-lucide-pie-chart style="width:36px; height:36px;" /></div>
            <div class="empty-state-desc">Belum ada data pengeluaran pada bulan ini</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Transaksi Kas & Bank Terbaru</h3>
      <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm" style="text-decoration:none; display:flex; align-items:center; gap:6px;">
        <span>Lihat Semua</span> <x-lucide-arrow-right style="width:14px; height:14px;" />
      </a>
    </div>
    @if($recentTransactions->isEmpty())
      <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--text-muted);"><x-lucide-inbox style="width:42px; height:42px;" /></div>
        <div class="empty-state-title">Belum ada transaksi</div>
        <div class="empty-state-desc">Mulai catat mutasi arus kas institusi dengan menambah transaksi baru.</div>
        <button class="btn btn-primary" id="btn-empty-add-tx" style="margin-top:12px; cursor:pointer">
          <x-lucide-plus /> Tambah Transaksi
        </button>
      </div>
    @else
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Kategori</th>
              <th>Deskripsi</th>
              <th>Tipe</th>
              <th style="text-align:right">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentTransactions as $tx)
              <tr>
                <td>{{ $tx->date->format('d M Y') }}</td>
                <td>
                  <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $tx->category->color ?? '#6366f1' }}; display: inline-block; margin-right: 6px;"></span>
                  @if($tx->category && $tx->category->code)
                    <span class="badge" style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #4f46e5; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); padding: 1px 5px; border-radius: 4px; margin-right: 4px;">
                      {{ $tx->category->code }}
                    </span>
                  @endif
                  {{ $tx->category->name ?? 'Lainnya' }}
                </td>
                <td>{{ $tx->description ?? '—' }}</td>
                <td>
                  <span class="badge badge-{{ $tx->type }}">
                    {{ $tx->type === 'income' ? 'Masuk' : 'Keluar' }}
                  </span>
                </td>
                <td style="text-align:right" class="font-bold money-cell font-mono-num {{ $tx->type === 'income' ? 'text-income' : 'text-expense' }}">
                  {{ $tx->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

@include('partials.transaction-modal')
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Formatting helper
    function formatCurrency(val) {
      return 'Rp ' + Number(val).toLocaleString('id-ID');
    }

    // 1. Cashflow Trend Chart
    const cashflowCtx = document.getElementById('chart-cashflow');
    if (cashflowCtx) {
      new Chart(cashflowCtx, {
        type: 'bar',
        data: {
          labels: {!! json_encode($months) !!},
          datasets: [
            {
              label: 'Pemasukan',
              data: {!! json_encode($incomeTrend) !!},
              backgroundColor: 'rgba(16, 185, 129, 0.75)',
              borderColor: '#10b981',
              borderWidth: 1,
              borderRadius: 6,
              borderSkipped: false,
            },
            {
              label: 'Pengeluaran',
              data: {!! json_encode($expenseTrend) !!},
              backgroundColor: 'rgba(244, 63, 94, 0.75)',
              borderColor: '#f43f5e',
              borderWidth: 1,
              borderRadius: 6,
              borderSkipped: false,
            },
            {
              label: 'Netto',
              data: {!! json_encode(array_map(function($i, $e) { return $i - $e; }, $incomeTrend, $expenseTrend)) !!},
              type: 'line',
              borderColor: '#4f46e5',
              backgroundColor: 'rgba(79, 70, 229, 0.08)',
              borderWidth: 2.5,
              pointRadius: 4,
              pointBackgroundColor: '#4f46e5',
              tension: 0.35,
              fill: true,
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
              labels: {
                color: '#475569',
                usePointStyle: true,
                padding: 18,
                font: { family: 'Inter', size: 12, weight: 500 }
              }
            },
            tooltip: {
              backgroundColor: '#0f172a',
              titleColor: '#f8fafc',
              bodyColor: '#cbd5e1',
              borderColor: '#334155',
              borderWidth: 1,
              padding: 12,
              cornerRadius: 8,
              callbacks: {
                label: (ctx) => `${ctx.dataset.label}: ${formatCurrency(ctx.raw)}`
              }
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(0,0,0,0.04)' },
              ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } }
            },
            y: {
              grid: { color: 'rgba(0,0,0,0.04)' },
              ticks: {
                color: '#64748b',
                font: { family: 'JetBrains Mono', size: 11 },
                callback: (val) => formatCurrency(val)
              }
            }
          }
        }
      });
    }

    // 2. Category Doughnut Chart
    const categoryCtx = document.getElementById('chart-category');
    if (categoryCtx) {
      const expenseCats = {!! json_encode($expenseCategories) !!};
      new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
          labels: expenseCats.map(c => c.name),
          datasets: [{
            data: expenseCats.map(c => c.amount),
            backgroundColor: expenseCats.map(c => c.color),
            borderColor: '#ffffff',
            borderWidth: 2,
            hoverOffset: 6,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '68%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: '#475569',
                usePointStyle: true,
                padding: 12,
                font: { family: 'Inter', size: 11 }
              }
            },
            tooltip: {
              backgroundColor: '#0f172a',
              titleColor: '#f8fafc',
              bodyColor: '#cbd5e1',
              borderColor: '#334155',
              borderWidth: 1,
              padding: 12,
              cornerRadius: 8,
              callbacks: {
                label: (ctx) => `${ctx.label}: ${formatCurrency(ctx.raw)}`
              }
            }
          }
        }
      });
    }

    // --- Dashboard Transaction Modal Logic ---
    const overlay = document.getElementById('tx-modal-overlay');
    const form = document.getElementById('tx-modal-form');
    const title = document.getElementById('tx-modal-title');
    const methodInput = document.getElementById('tx-form-method');
    
    const typeInput = document.getElementById('modal-type');
    const btnIncome = document.getElementById('btn-select-income');
    const btnExpense = document.getElementById('btn-select-expense');
    const btnTransfer = document.getElementById('btn-select-transfer');
    
    const dateInput = document.getElementById('modal-date');
    const amountInput = document.getElementById('modal-amount');
    const descInput = document.getElementById('modal-desc');
    const saveBtn = document.getElementById('btn-modal-save');

    const modalAccount = document.getElementById('modal-account');
    const modalAccountTip = document.getElementById('modal-account-tip');
    const modalAccountWarning = document.getElementById('modal-account-warning');

    function updateAccountUi() {
      const selected = modalAccount.value;
      const amount = parseFloat(amountInput.value) || 0;
      
      if (selected === 'petty_cash') {
        modalAccountTip.textContent = "Dikhususkan untuk pendanaan operasional ringan dan rutin harian (di bawah Rp 5.000.000).";
        if (amount >= 5000000) {
          modalAccountWarning.style.display = 'block';
        } else {
          modalAccountWarning.style.display = 'none';
        }
      } else {
        modalAccountTip.textContent = "Dikhususkan untuk perputaran dana berskala besar (pencairan cek, pembayaran vendor, gaji).";
        modalAccountWarning.style.display = 'none';
      }
    }

    modalAccount.addEventListener('change', updateAccountUi);
    amountInput.addEventListener('input', updateAccountUi);

    function setFormType(type) {
      typeInput.value = type;
      btnIncome.classList.toggle('active', type === 'income');
      btnExpense.classList.toggle('active', type === 'expense');
      if (btnTransfer) btnTransfer.classList.toggle('active', type === 'transfer');

      if (window.onTransactionTypeChange) {
        window.onTransactionTypeChange(type);
      }
    }

    btnIncome.addEventListener('click', () => setFormType('income'));
    btnExpense.addEventListener('click', () => setFormType('expense'));
    if (btnTransfer) btnTransfer.addEventListener('click', () => setFormType('transfer'));

    // Open Modal for Create
    function openAddModal() {
      form.action = "{{ route('transactions.store') }}";
      methodInput.value = "POST";
      title.innerHTML = `Tambah Transaksi Baru`;
      
      modalAccount.value = "{{ $activeAccount }}";
      amountInput.value = '';
      descInput.value = '';
      dateInput.value = "{{ date('Y-m-d') }}";
      const modalJob = document.getElementById('modal-job');
      if (modalJob) modalJob.value = '';
      
      setFormType('expense');
      if (window.selectModalCategoryDefault) {
        window.selectModalCategoryDefault('expense');
      }
      updateAccountUi();
      saveBtn.textContent = 'Tambah Transaksi';
      overlay.classList.add('active');
      if (window.lucide) {
        lucide.createIcons();
      }
    }

    const btnAddTx = document.getElementById('btn-add-tx');
    if (btnAddTx) {
      btnAddTx.addEventListener('click', openAddModal);
    }
    const btnEmptyAddTx = document.getElementById('btn-empty-add-tx');
    if (btnEmptyAddTx) {
      btnEmptyAddTx.addEventListener('click', openAddModal);
    }

    function closeModal() {
      overlay.classList.remove('active');
      if (window.closeCategoryDropdown) {
        window.closeCategoryDropdown();
      }
    }

    document.getElementById('btn-modal-close')?.addEventListener('click', closeModal);
    document.getElementById('btn-modal-cancel')?.addEventListener('click', closeModal);
    overlay?.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal();
    });

    // Panjar Modal Logic
    const panjarCard = document.getElementById('btn-panjar-recap');
    const panjarModal = document.getElementById('panjar-modal-overlay');
    const panjarDetailModal = document.getElementById('panjar-source-detail-modal');
    const panjarDataBySource = @json($panjarBySource);

    function formatRupiah(num) {
      return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function openPanjarSourceDetail(sourceKey) {
      const data = panjarDataBySource[sourceKey];
      if (!data) return;

      // Update popup header & info box
      const titleEl = document.getElementById('panjar-popup-title');
      const metaName = document.getElementById('panjar-meta-account-name');
      const metaCode = document.getElementById('panjar-meta-account-code');
      const metaTotal = document.getElementById('panjar-meta-account-total');
      const metaCount = document.getElementById('panjar-meta-account-count');

      if (titleEl) titleEl.textContent = 'Daftar Pemegang Panjar — ' + data.name;
      if (metaName) metaName.textContent = data.name;
      if (metaCode) metaCode.textContent = data.code;
      if (metaTotal) metaTotal.textContent = formatRupiah(data.total);
      if (metaCount) {
        metaCount.textContent = (data.active_count || 0) + ' Orang';
        metaCount.style.color = (data.active_count || 0) > 0 ? '#ea580c' : 'var(--text-muted)';
      }

      const iconWrapper = document.getElementById('panjar-popup-icon');
      if (iconWrapper) {
        iconWrapper.style.color = data.color;
        iconWrapper.style.background = data.bg;
      }

      // Populate tbody
      const tbody = document.getElementById('panjar-popup-tbody');
      const tfootTotal = document.getElementById('panjar-popup-tfoot-total');
      if (!tbody) return;
      tbody.innerHTML = '';

      const activeRecipients = Object.values(data.recipients || {}).filter(r => r.total > 0);

      if (activeRecipients.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align: center; padding: 36px 16px; color: var(--text-muted); font-size: 0.85rem;">
              <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span>Nihil — Tidak ada pemegang panjar aktif yang bersumber dari <strong>${data.name}</strong>.</span>
              </div>
            </td>
          </tr>
        `;
        if (tfootTotal) tfootTotal.textContent = 'Rp 0';
      } else {
        activeRecipients.forEach((rec, idx) => {
          const tr = document.createElement('tr');
          tr.style.borderBottom = '1px solid var(--border-primary)';
          tr.innerHTML = `
            <td style="text-align: center; font-weight: 600; color: var(--text-muted); padding: 11px 10px;">${idx + 1}</td>
            <td style="white-space: nowrap; padding: 11px 10px; font-size: 0.8rem;">${rec.last_date || '—'}</td>
            <td style="padding: 11px 10px;">
              <span class="badge" style="font-family: monospace; font-size: 0.72rem; background: rgba(0,0,0,0.06); color: #334155; padding: 2px 6px; border-radius: 4px;">
                ${rec.voucher || '—'}
              </span>
            </td>
            <td style="padding: 11px 10px;">
              <div style="font-weight: 700; color: var(--text-primary); font-size: 0.88rem;">${rec.name}</div>
              ${rec.count > 1 ? `<span class="badge" style="font-size:0.68rem; margin-top:2px; padding:1px 5px; background:rgba(0,0,0,0.05);">${rec.count} nota</span>` : ''}
            </td>
            <td style="padding: 11px 10px;">
              <span class="badge" style="background: rgba(99,102,241,0.1); color: #4f46e5; font-size: 0.73rem; padding: 2px 6px; border-radius: 4px;">
                ${rec.job || '—'}
              </span>
            </td>
            <td style="padding: 11px 10px; color: var(--text-muted); font-size: 0.82rem;">${rec.description || '—'}</td>
            <td style="text-align: center; padding: 11px 10px; white-space: nowrap;">
              <span class="badge" style="font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 9999px; color: ${rec.status_color || '#10b981'}; background: ${rec.status_bg || 'rgba(16, 185, 129, 0.15)'}; border: 1px solid ${rec.status_color || '#10b981'}40;">
                ${rec.status_label || (rec.age_days != null ? rec.age_days + ' Hari' : '—')}
              </span>
            </td>
            <td style="text-align: right; font-weight: 800; color: #b45309; padding: 11px 10px;" class="money-cell font-mono-num">
              ${formatRupiah(rec.total)}
            </td>
          `;
          tbody.appendChild(tr);
        });
        if (tfootTotal) tfootTotal.textContent = formatRupiah(data.total);
      }

      if (panjarDetailModal) {
        panjarDetailModal.classList.add('active');
        if (window.lucide) lucide.createIcons();
      }
    }

    if (panjarCard && panjarModal) {
      panjarCard.addEventListener('click', () => {
        panjarModal.classList.add('active');
        if (window.lucide) {
          lucide.createIcons();
        }
      });
      
      const btnPanjarClose = document.getElementById('btn-panjar-close');
      if (btnPanjarClose) btnPanjarClose.addEventListener('click', () => {
        panjarModal.classList.remove('active');
      });
      
      panjarModal.addEventListener('click', (e) => {
        if (e.target === panjarModal) panjarModal.classList.remove('active');
      });

      // Clicking Top Cards -> Open popup detail
      document.querySelectorAll('.panjar-source-card').forEach(card => {
        card.addEventListener('click', () => {
          const source = card.dataset.source;
          openPanjarSourceDetail(source);
        });
      });

      // Detail Modal Close Buttons
      const btnClosePanjarDetail = document.getElementById('btn-close-panjar-detail');
      const btnClosePanjarDetailFooter = document.getElementById('btn-close-panjar-detail-footer');

      if (btnClosePanjarDetail && panjarDetailModal) {
        btnClosePanjarDetail.addEventListener('click', () => {
          panjarDetailModal.classList.remove('active');
        });
      }
      if (btnClosePanjarDetailFooter && panjarDetailModal) {
        btnClosePanjarDetailFooter.addEventListener('click', () => {
          panjarDetailModal.classList.remove('active');
        });
      }
      if (panjarDetailModal) {
        panjarDetailModal.addEventListener('click', (e) => {
          if (e.target === panjarDetailModal) {
            panjarDetailModal.classList.remove('active');
          }
        });
      }


    }
  });
</script>

<div class="modal-overlay" id="panjar-modal-overlay">
  <div class="modal" style="max-width: 720px; width: 95%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:8px;">
        <div style="width:32px; height:32px; border-radius:8px; background:rgba(245,158,11,0.15); color:#d97706; display:flex; align-items:center; justify-content:center;">
          <x-lucide-hourglass style="width:18px; height:18px;" />
        </div>
        <div>
          <h3 style="margin:0; font-size:1.1rem">Rekap Panjar Kerja Aktif (ACC 11599)</h3>
          <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px">Klik sumber rekening untuk melihat daftar nama pemegang uang muka</div>
        </div>
      </div>
      <button type="button" class="modal-close" id="btn-panjar-close" aria-label="Tutup modal">
        <x-lucide-x />
      </button>
    </div>
    <div class="modal-body" style="padding: 20px;">
      @if(isset($panjarTransactions) && $panjarTransactions->count() > 0)
        <!-- Total Header Summary Banner -->
        <div style="background: linear-gradient(135deg, rgba(245,158,11,0.08) 0%, rgba(245,158,11,0.02) 100%); border: 1px solid rgba(245,158,11,0.25); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
          <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #b45309; letter-spacing: 0.5px;">Total Panjar Beredar</div>
            <div style="font-size: 1.45rem; font-weight: 800; color: #b45309; margin-top: 2px;" class="font-mono-num">
              Rp {{ number_format($totalPanjarAktif, 0, ',', '.') }}
            </div>
          </div>
          <div style="font-size: 0.8rem; color: var(--text-muted); max-width: 280px; text-align: right;">
            Pilih salah satu kas/bank di bawah untuk melihat rincian pemegang panjar.
          </div>
        </div>

        <!-- 3 Cards: Kas Kecil, Mandiri 1, Mandiri 2 -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
          <!-- Kas Kecil Card -->
          <div class="panjar-source-card" data-source="petty_cash" style="background:rgba(245,158,11,0.05); border:1px solid rgba(245,158,11,0.25); border-radius:14px; padding:18px; cursor:pointer; transition:all 0.2s;" title="Klik untuk lihat rincian pemegang panjar Kas Kecil">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
              <div style="width:38px; height:38px; border-radius:10px; background:rgba(245,158,11,0.18); color:#d97706; display:flex; align-items:center; justify-content:center;">
                <x-lucide-wallet style="width:20px; height:20px;" />
              </div>
              <span class="badge" style="font-size:0.72rem; background:rgba(245,158,11,0.12); color:#d97706; border:1px solid rgba(245,158,11,0.25); border-radius:6px; font-weight:700">
                {{ $panjarBySource['petty_cash']['active_count'] ?? 0 }} Orang
              </span>
            </div>
            <div style="margin-top:14px;">
              <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase">Kas Kecil (1-1110)</div>
              <div style="font-size:1.25rem; font-weight:800; color:#d97706; margin-top:2px;" class="font-mono-num">
                Rp {{ number_format($panjarSummaryByAccount['petty_cash'] ?? 0, 0, ',', '.') }}
              </div>
            </div>
            <div style="margin-top:14px; padding-top:10px; border-top:1px dashed rgba(245,158,11,0.2); display:flex; align-items:center; justify-content:space-between; font-size:0.78rem; color:#d97706; font-weight:600;">
              <span>Lihat Rincian Nama</span>
              <x-lucide-chevron-right style="width:14px; height:14px;" />
            </div>
          </div>

          <!-- Mandiri 1 Card -->
          <div class="panjar-source-card" data-source="bank_mandiri_1" style="background:rgba(59,130,246,0.05); border:1px solid rgba(59,130,246,0.25); border-radius:14px; padding:18px; cursor:pointer; transition:all 0.2s;" title="Klik untuk lihat rincian pemegang panjar Bank Mandiri 1">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
              <div style="width:38px; height:38px; border-radius:10px; background:rgba(59,130,246,0.18); color:#2563eb; display:flex; align-items:center; justify-content:center;">
                <x-lucide-landmark style="width:20px; height:20px;" />
              </div>
              <span class="badge" style="font-size:0.72rem; background:rgba(59,130,246,0.12); color:#2563eb; border:1px solid rgba(59,130,246,0.25); border-radius:6px; font-weight:700">
                {{ $panjarBySource['bank_mandiri_1']['active_count'] ?? 0 }} Orang
              </span>
            </div>
            <div style="margin-top:14px;">
              <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase">Bank Mandiri 1 (1-1121)</div>
              <div style="font-size:1.25rem; font-weight:800; color:#2563eb; margin-top:2px;" class="font-mono-num">
                Rp {{ number_format($panjarSummaryByAccount['bank_mandiri_1'] ?? 0, 0, ',', '.') }}
              </div>
            </div>
            <div style="margin-top:14px; padding-top:10px; border-top:1px dashed rgba(59,130,246,0.2); display:flex; align-items:center; justify-content:space-between; font-size:0.78rem; color:#2563eb; font-weight:600;">
              <span>Lihat Rincian Nama</span>
              <x-lucide-chevron-right style="width:14px; height:14px;" />
            </div>
          </div>

          <!-- Mandiri 2 Card -->
          <div class="panjar-source-card" data-source="bank_mandiri_2" style="background:rgba(139,92,246,0.05); border:1px solid rgba(139,92,246,0.25); border-radius:14px; padding:18px; cursor:pointer; transition:all 0.2s;" title="Klik untuk lihat rincian pemegang panjar Bank Mandiri 2">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
              <div style="width:38px; height:38px; border-radius:10px; background:rgba(139,92,246,0.18); color:#7c3aed; display:flex; align-items:center; justify-content:center;">
                <x-lucide-building-2 style="width:20px; height:20px;" />
              </div>
              <span class="badge" style="font-size:0.72rem; background:rgba(139,92,246,0.12); color:#7c3aed; border:1px solid rgba(139,92,246,0.25); border-radius:6px; font-weight:700">
                {{ $panjarBySource['bank_mandiri_2']['active_count'] ?? 0 }} Orang
              </span>
            </div>
            <div style="margin-top:14px;">
              <div style="font-size:0.75rem; color:var(--text-muted); font-weight:600; text-transform:uppercase">Bank Mandiri 2 (1-1122)</div>
              <div style="font-size:1.25rem; font-weight:800; color:#7c3aed; margin-top:2px;" class="font-mono-num">
                Rp {{ number_format($panjarSummaryByAccount['bank_mandiri_2'] ?? 0, 0, ',', '.') }}
              </div>
            </div>
            <div style="margin-top:14px; padding-top:10px; border-top:1px dashed rgba(139,92,246,0.2); display:flex; align-items:center; justify-content:space-between; font-size:0.78rem; color:#7c3aed; font-weight:600;">
              <span>Lihat Rincian Nama</span>
              <x-lucide-chevron-right style="width:14px; height:14px;" />
            </div>
          </div>
        </div>
      @else
        <div class="empty-state">
          <div class="empty-state-icon" style="color:#10b981;"><x-lucide-check-circle-2 style="width:40px; height:40px;" /></div>
          <div class="empty-state-title">Tidak ada panjar aktif</div>
          <div class="empty-state-desc">Seluruh uang muka/panjar kerja telah dipertanggungjawabkan secara lengkap.</div>
        </div>
      @endif
    </div>
  </div>
</div>

<!-- Secondary Modal: Popup Rincian Daftar Nama Pemegang (SIAKAD Screenshot 3 Style) -->
<div class="modal-overlay" id="panjar-source-detail-modal" style="z-index: 1100;">
  <div class="modal" style="max-width: 900px; width: 95%;">
    <!-- Header -->
    <div class="modal-header" style="background: var(--bg-surface, #ffffff); border-bottom: 1px solid var(--border-primary);">
      <div style="display:flex; align-items:center; gap:10px;">
        <div id="panjar-popup-icon" style="width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; background: rgba(37, 99, 235, 0.1); color: #2563eb;">
          <x-lucide-users style="width:18px; height:18px;" />
        </div>
        <div>
          <h3 style="margin:0; font-size:1.1rem; color:var(--text-primary);" id="panjar-popup-title">
            Daftar Pemegang Panjar Aktif
          </h3>
          <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px">
            Rincian nama pemegang uang muka dan pertanggungjawaban
          </div>
        </div>
      </div>
      <button type="button" class="modal-close" id="btn-close-panjar-detail" aria-label="Tutup rincian">
        <x-lucide-x />
      </button>
    </div>

    <!-- Body -->
    <div class="modal-body" style="padding: 20px; max-height: 520px; overflow-y: auto;">
      <!-- SIAKAD-style Metadata Info Box (Screenshot 3) -->
      <div style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-primary); border-radius: 8px; padding: 14px 18px; margin-bottom: 18px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
          <tbody>
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="width: 220px; padding: 5px 0; color: var(--text-muted); font-weight: 600;">Periode Buku / Status</td>
              <td style="padding: 5px 0; font-weight: 700; color: var(--text-primary);">{{ date('F Y') }} &bull; Aktif (Beredar)</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 0; color: var(--text-muted); font-weight: 600;">Sumber Rekening Kas / Bank</td>
              <td style="padding: 5px 0; font-weight: 800; color: var(--text-primary);" id="panjar-meta-account-name">Bank Mandiri Giro I</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 0; color: var(--text-muted); font-weight: 600;">Kode Akun Rekening</td>
              <td style="padding: 5px 0; font-family: monospace; font-weight: 700; color: #2563eb;" id="panjar-meta-account-code">1-1121</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 0; color: var(--text-muted); font-weight: 600;">Akun Induk Panjar</td>
              <td style="padding: 5px 0; font-family: monospace; color: var(--text-muted);">1-1500 / ACC 11599 (Biaya Dibayar Dimuka)</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-primary);">
              <td style="padding: 5px 0; color: var(--text-muted); font-weight: 600;">Total Saldo Panjar Akun Ini</td>
              <td style="padding: 5px 0; font-weight: 800; font-size: 1.05rem; color: #b45309;" class="font-mono-num" id="panjar-meta-account-total">Rp 200.000</td>
            </tr>
            <tr>
              <td style="padding: 5px 0; color: var(--text-muted); font-weight: 600;">Jumlah Pemegang (Terisi)</td>
              <td style="padding: 5px 0; font-weight: 800; color: #ea580c;" id="panjar-meta-account-count">1 Orang</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- SIAKAD-style Data Table with Horizontal Scroll Support -->
      <div class="table-scroll-x" style="border: 1px solid var(--border-primary); border-radius: 8px; overflow-x: auto; background: var(--bg-card, #ffffff); width: 100%; -webkit-overflow-scrolling: touch;">
        <table class="data-table" style="width: 100%; min-width: 820px; border-collapse: collapse; font-size: 0.84rem;">
          <thead>
            <tr style="background: rgba(0,0,0,0.03); border-bottom: 1px solid var(--border-primary); text-align: left;">
              <th style="padding: 10px 12px; width: 45px; text-align: center;">No</th>
              <th style="padding: 10px 12px; min-width: 90px;">Tanggal</th>
              <th style="padding: 10px 12px; min-width: 100px;">No. Bukti / Ref</th>
              <th style="padding: 10px 12px; min-width: 140px;">Nama Pemegang ("Siapa")</th>
              <th style="padding: 10px 12px; min-width: 120px;">Unit / Prodi</th>
              <th style="padding: 10px 12px; min-width: 180px;">Keperluan / Keterangan</th>
              <th style="padding: 10px 12px; min-width: 140px; text-align: center;">Umur Panjar</th>
              <th style="padding: 10px 12px; text-align: right; min-width: 130px;">Saldo Panjar</th>
            </tr>
          </thead>
          <tbody id="panjar-popup-tbody">
            <!-- Dynamic rows populated via JS -->
          </tbody>
          <tfoot style="background: rgba(0,0,0,0.02); border-top: 2px solid var(--border-primary); font-weight: bold;">
            <tr>
              <td colspan="7" style="padding: 10px 12px; text-align: right; color: var(--text-muted); white-space: nowrap;">Total Panjar Akun:</td>
              <td id="panjar-popup-tfoot-total" style="padding: 10px 12px; text-align: right; color: #b45309; font-size: 0.95rem; white-space: nowrap;" class="money-cell font-mono-num">
                Rp 0
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Footer -->
    <div class="modal-footer" style="padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-primary); background: rgba(0,0,0,0.02);">
      <div style="font-size: 0.76rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px;">
        <x-lucide-info style="width:14px; height:14px; color:#3b82f6;" />
        Panjar akan otomatis berkurang / nihil saat transaksi pertanggungjawaban (LPJ) dicatat.
      </div>
      <button type="button" class="btn btn-secondary btn-sm" id="btn-close-panjar-detail-footer">
        Kembali ke Pilihan Kas
      </button>
    </div>
  </div>
</div>
@endsection
