<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Cashflow SIK-Flow — Sistem Informasi Keuangan dan Manajemen Arus Kas Kampus. Pantau mutasi kas, jurnal umum, buku besar, dan analisis keuangan real-time." />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#162252" />
  <title>@yield('title', 'Cashflow SIK-Flow — Arus Kas Kampus')</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Buka menu navigasi">
    <x-lucide-menu />
  </button>

  <div class="app-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <div class="sidebar-logo-icon" style="background: #ffffff; padding: 2px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-primary);">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo STT PU" style="width: 100%; height: 100%; object-fit: contain; border-radius: var(--radius-sm);" />
          </div>
          <div class="sidebar-logo-text">
            <h1>SIK-Flow</h1>
            <span>STT Pekerjaan Umum</span>
          </div>
        </div>
      </div>

      <!-- Account Switcher -->
      <div class="account-switcher">
        <div class="sidebar-nav-label">Sumber Rekening Kas</div>
        <div class="account-tabs">
          <a href="{{ route('switch-account', 'petty_cash') }}" class="account-tab {{ $activeAccount === 'petty_cash' ? 'active' : '' }}" data-account="petty_cash">
            <span class="account-tab-icon"><x-lucide-wallet /></span>
            <span class="account-tab-label">Kas Kecil</span>
          </a>
          <a href="{{ route('switch-account', 'bank_mandiri_1') }}" class="account-tab {{ $activeAccount === 'bank_mandiri_1' ? 'active' : '' }}" data-account="bank">
            <span class="account-tab-icon"><x-lucide-landmark /></span>
            <span class="account-tab-label">Mandiri 1</span>
          </a>
          <a href="{{ route('switch-account', 'bank_mandiri_2') }}" class="account-tab {{ $activeAccount === 'bank_mandiri_2' ? 'active' : '' }}" data-account="bank">
            <span class="account-tab-icon"><x-lucide-building-2 /></span>
            <span class="account-tab-label">Mandiri 2</span>
          </a>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Menu Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-layout-dashboard /></span>
          <span>Dashboard</span>
        </a>
        <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-receipt /></span>
          <span>Buku Kas & Bank</span>
        </a>
        <a href="{{ route('reports.general-ledger') }}" class="nav-item {{ request()->routeIs('reports.general-ledger*') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-scroll-text /></span>
          <span>Buku Besar</span>
        </a>
        <a href="{{ route('reports.lsd') }}" class="nav-item {{ request()->routeIs('reports.lsd') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-pie-chart /></span>
          <span>Laporan LSD</span>
        </a>
        <a href="{{ route('reports.trial-balance') }}" class="nav-item {{ request()->routeIs('reports.trial-balance*') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-file-spreadsheet /></span>
          <span>Neraca Saldo</span>
        </a>

        <div class="sidebar-nav-label">Laporan Akuntansi</div>
        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-trending-up /></span>
          <span>Arus Kas Bulanan</span>
        </a>
        <a href="{{ route('journal-entries.index') }}" class="nav-item {{ request()->routeIs('journal-entries.*') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-scale /></span>
          <span>Jurnal Umum</span>
        </a>
        <a href="{{ route('projections.index') }}" class="nav-item {{ request()->routeIs('projections.*') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-sparkles /></span>
          <span>Proyeksi Kas</span>
        </a>
        <a href="{{ route('categories.index') }}" class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-folder-tree /></span>
          <span>Bagan Akun</span>
        </a>
        <a href="{{ route('jobs.index') }}" class="nav-item {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
          <span class="nav-item-icon"><x-lucide-briefcase /></span>
          <span>Unit Kerja</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <form action="{{ route('load-sample-data') }}" method="POST" id="form-load-sample" style="width:100%">
          @csrf
          <button type="submit" class="nav-item btn-action-sidebar" title="Muat data transaksi sampel STT Pekerjaan Umum">
            <span class="nav-item-icon"><x-lucide-database-backup /></span>
            <span>Muat Data Contoh</span>
          </button>
        </form>
        <form action="{{ route('reset-data') }}" method="POST" id="form-reset-data" style="width:100%" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan semua data transaksi dan jurnal umum?')">
          @csrf
          <button type="submit" class="nav-item btn-action-sidebar danger" title="Bersihkan seluruh data transaksi untuk trial">
            <span class="nav-item-icon"><x-lucide-trash-2 /></span>
            <span>Reset Data Kas</span>
          </button>
        </form>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content" id="main-content">
      @yield('content')
    </main>
  </div>

  <!-- Toast Notifications Container -->
  <div id="toast-container">
    @if(session('success'))
      <div class="toast success show">
        <span class="toast-icon"><x-lucide-check-circle-2 /></span>
        <span class="toast-message">{{ session('success') }}</span>
      </div>
    @endif
    @if(session('error'))
      <div class="toast error show">
        <span class="toast-icon"><x-lucide-alert-circle /></span>
        <span class="toast-message">{{ session('error') }}</span>
      </div>
    @endif
  </div>

  <!-- Mobile Sidebar Menu Toggle Script & Lucide Init -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (window.lucide) {
        lucide.createIcons();
      }
    });

    document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
      document.getElementById('sidebar')?.classList.toggle('open');
    });

    // Auto-hide toast notifications after 4 seconds
    setTimeout(() => {
      document.querySelectorAll('.toast').forEach(toast => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 500);
      });
    }, 4000);
  </script>

  @yield('scripts')
</body>
</html>
