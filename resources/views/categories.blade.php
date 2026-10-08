@extends('layouts.app')

@section('title', 'Kategori — Cashflow Management')

@section('content')
<style>
  .cat-autocomplete-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--bg-secondary, #ffffff);
    border: 1px solid var(--border-primary, rgba(22, 34, 82, 0.12));
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    max-height: 350px;
    overflow-y: auto;
    z-index: 100;
    display: none;
  }
  .cat-autocomplete-header {
    padding: 8px 14px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-primary, rgba(22, 34, 82, 0.06));
    background: var(--bg-glass, rgba(22, 34, 82, 0.02));
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .cat-autocomplete-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    cursor: pointer;
    transition: background 0.15s ease;
    border-bottom: 1px solid var(--border-primary, rgba(22, 34, 82, 0.04));
    text-decoration: none;
    color: inherit;
  }
  .cat-autocomplete-item:last-child {
    border-bottom: none;
  }
  .cat-autocomplete-item:hover,
  .cat-autocomplete-item.active-item {
    background: rgba(22, 34, 82, 0.06);
  }
  .cat-autocomplete-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
  }
  .cat-autocomplete-info {
    flex: 1;
    min-width: 0;
  }
  .cat-autocomplete-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .cat-autocomplete-code {
    font-family: monospace;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    letter-spacing: 0.5px;
    flex-shrink: 0;
  }
  .cat-autocomplete-meta {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .cat-autocomplete-empty {
    padding: 20px 16px;
    text-align: center;
    color: var(--text-muted);
    font-size: 0.85rem;
  }
  .category-card.card-highlight-pulse {
    animation: pulseCard 1.8s ease;
    outline: 2px solid var(--accent-secondary, #F3A712);
  }
  @keyframes pulseCard {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(243, 167, 18, 0.7); }
    30% { transform: scale(1.03); box-shadow: 0 0 0 10px rgba(243, 167, 18, 0); }
    100% { transform: scale(1); }
  }
</style>
<div class="page-content">
  <div class="page-header">
    <div>
      <h2>Kategori</h2>
      <p>Kelola bagan akun dan kategori pemasukan & pengeluaran kampus</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
      <a href="{{ route('categories.import-template') }}" class="btn btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <x-lucide-file-down style="width:16px; height:16px;" /> Template CSV
      </a>
      <button class="btn btn-secondary" id="btn-import-category" style="display: inline-flex; align-items: center; gap: 6px;">
        <x-lucide-upload style="width:16px; height:16px;" /> Import Excel
      </button>
      <button class="btn btn-primary" id="btn-add-category" style="display: inline-flex; align-items: center; gap: 6px;">
        <x-lucide-plus style="width:16px; height:16px;" /> Kategori Baru
      </button>
    </div>
  </div>

  <!-- Search & Filter Controls -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
    <!-- Tabs -->
    <div class="tabs" style="margin-bottom: 0;">
      <button class="tab-btn active" data-tab="all">Semua ({{ $categories->count() }})</button>
      <button class="tab-btn" data-tab="asset">Aset ({{ $categories->where('type', 'asset')->count() }})</button>
      <button class="tab-btn" data-tab="liability">Kewajiban ({{ $categories->where('type', 'liability')->count() }})</button>
      <button class="tab-btn" data-tab="equity">Ekuitas ({{ $categories->where('type', 'equity')->count() }})</button>
      <button class="tab-btn" data-tab="income">Pendapatan ({{ $categories->where('type', 'income')->count() }})</button>
      <button class="tab-btn" data-tab="expense">Beban ({{ $categories->where('type', 'expense')->count() }})</button>
    </div>

    <!-- Quick Search with Autocomplete List -->
    <div class="cat-search-container" style="position: relative; min-width: 280px; flex: 1; max-width: 380px;">
      <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); display:flex; align-items:center; pointer-events: none; z-index: 2; color: var(--text-muted);">
        <x-lucide-search style="width:15px; height:15px;" />
      </span>
      <input type="text" class="form-input" id="cat-search-input" 
        placeholder="Cari kode akun atau nama kategori..." 
        autocomplete="off"
        style="width: 100%; padding-left: 36px; padding-right: 32px;" />
      <button type="button" id="cat-search-clear" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; display: none; padding: 4px; border-radius: 50%;" title="Hapus pencarian">
        <x-lucide-x style="width:14px; height:14px;" />
      </button>

      <!-- Autocomplete Dropdown List -->
      <div id="cat-autocomplete-list" class="cat-autocomplete-dropdown"></div>
    </div>
  </div>

  <!-- Category Grid -->
  <div class="category-grid" id="category-grid">
    @if($categories->isEmpty())
      <div class="empty-state" style="grid-column: 1/-1">
        <div class="empty-state-icon" style="display:flex; justify-content:center; margin-bottom:12px;">
          <x-lucide-folder-tree style="width:44px; height:44px; color:var(--text-muted); stroke-width:1.5;" />
        </div>
        <div class="empty-state-title">Belum ada kategori</div>
        <div class="empty-state-desc">Buat kategori baru untuk mengorganisir transaksi keuangan kampus.</div>
      </div>
    @else
      @foreach($categories as $cat)
        @php
          $txCount = $cat->transactions_count ?? 0;
          $txTotal = $cat->transactions_sum_amount ?? 0;
        @endphp
        <div class="category-card" 
          id="cat-card-{{ $cat->id }}"
          data-id="{{ $cat->id }}" 
          data-type="{{ $cat->type }}"
          data-code="{{ strtolower($cat->code ?? '') }}"
          data-name="{{ strtolower($cat->name) }}">
          <div class="category-card-header">
            <div class="category-card-icon" style="background:{{ $cat->color }}18; border: 1px solid {{ $cat->color }}35">
              @if(preg_match('/^[a-z0-9\-]+$/', $cat->icon ?? ''))
                <x-dynamic-component :component="'lucide-' . $cat->icon" style="width:18px; height:18px; color:{{ $cat->color }};" />
              @else
                <x-dynamic-component :component="'lucide-' . ($cat->type === 'income' ? 'arrow-down-left' : 'arrow-up-right')" style="width:18px; height:18px; color:{{ $cat->color }};" />
              @endif
            </div>
            <div class="category-card-actions">
              <button class="btn btn-icon btn-sm btn-secondary btn-edit-cat" 
                data-id="{{ $cat->id }}"
                data-code="{{ $cat->code }}"
                data-name="{{ $cat->name }}"
                data-type="{{ $cat->type }}"
                data-icon="{{ $cat->icon }}"
                data-color="{{ $cat->color }}"
                title="Edit"><x-lucide-edit-3 style="width:13px; height:13px;" /></button>
              <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus kategori ini? Semua transaksi dengan kategori ini harus dipindahkan terlebih dahulu.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-icon btn-sm btn-danger btn-delete-cat" title="Hapus"><x-lucide-trash-2 style="width:13px; height:13px;" /></button>
              </form>
            </div>
          </div>

          <!-- Code Badge -->
          <div style="margin-top: 10px; margin-bottom: 4px;">
            @if($cat->code)
              <span class="badge font-mono-num" style="font-size: 0.8rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: {{ $cat->color }}18; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40; letter-spacing: 0.5px;">
                {{ $cat->code }}
              </span>
            @else
              <span class="badge" style="font-size: 0.75rem; color: var(--text-muted); background: rgba(255,255,255,0.05);">
                Tanpa Kode
              </span>
            @endif
          </div>

          <div class="category-card-name" style="font-size: 1.02rem; font-weight: 600; line-height: 1.35; margin-top: 4px;">
            {{ $cat->name }}
          </div>

          <div class="category-card-meta mt-2" style="display: flex; align-items: center; justify-content: space-between;">
            @php
              $badgeClass = match($cat->type) {
                'asset' => 'badge-asset',
                'liability' => 'badge-liability',
                'equity' => 'badge-equity',
                'income' => 'badge-income',
                'expense' => 'badge-expense',
                default => 'badge-secondary',
              };
              $badgeLabel = match($cat->type) {
                'asset' => 'Aset',
                'liability' => 'Kewajiban',
                'equity' => 'Ekuitas',
                'income' => 'Pendapatan',
                'expense' => 'Beban',
                default => ucfirst($cat->type),
              };
            @endphp
            <span class="badge {{ $badgeClass }}">
              {{ $badgeLabel }}
            </span>
            <span style="font-size: 0.8rem; color: var(--text-muted);" class="font-mono-num">
              {{ $txCount }} transaksi
            </span>
          </div>

          <div class="category-card-meta mt-2 font-mono-num tabular-nums" style="font-weight:700; color:{{ $cat->color }}; font-size: 0.95rem;">
            Rp {{ number_format($txTotal, 0, ',', '.') }}
          </div>
        </div>
      @endforeach
    @endif
  </div>
</div>

<!-- Add/Edit Category Modal Overlay -->
<div class="modal-overlay" id="cat-modal-overlay">
  <div class="modal">
    <form action="{{ route('categories.store') }}" method="POST" id="cat-modal-form">
      @csrf
      <input type="hidden" name="_method" id="cat-form-method" value="POST">
      
      <div class="modal-header">
        <h3 id="cat-modal-title">Tambah Kategori Baru</h3>
        <button type="button" class="modal-close" id="btn-modal-close"><x-lucide-x style="width:18px; height:18px;" /></button>
      </div>
      
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Kode Akun / COA Kampus (Unik) <span style="color:#ef4444">*</span></label>
          <input type="text" class="form-input font-mono-num" name="code" id="modal-cat-code" 
            placeholder="Contoh: 4-1010, 5-1100..." required 
            style="letter-spacing: 0.5px;" />
          <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; display: block;">
            Kode akun unik dari bagan perkiraan / Chart of Accounts kampus.
          </span>
        </div>

        <div class="form-group">
          <label class="form-label">Nama Kategori <span style="color:#ef4444">*</span></label>
          <input type="text" class="form-input" name="name" id="modal-cat-name" 
            placeholder="Contoh: SPP Mahasiswa, Beban Listrik & Internet..." required />
        </div>

        <div class="form-group">
          <label class="form-label">Tipe Akun / Kategori</label>
          <div class="type-selector" style="display:flex; gap:6px; flex-wrap:wrap;">
            <input type="hidden" name="type" id="modal-cat-type" value="income">
            <button type="button" class="type-selector-btn" id="btn-cat-asset" data-type="asset" style="flex:1; min-width:85px; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:0.8rem; padding:8px 10px;">
              <x-lucide-wallet style="width:14px; height:14px;" /> Aset
            </button>
            <button type="button" class="type-selector-btn" id="btn-cat-liability" data-type="liability" style="flex:1; min-width:85px; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:0.8rem; padding:8px 10px;">
              <x-lucide-credit-card style="width:14px; height:14px;" /> Kewajiban
            </button>
            <button type="button" class="type-selector-btn" id="btn-cat-equity" data-type="equity" style="flex:1; min-width:85px; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:0.8rem; padding:8px 10px;">
              <x-lucide-scale style="width:14px; height:14px;" /> Ekuitas
            </button>
            <button type="button" class="type-selector-btn active" id="btn-cat-income" data-type="income" style="flex:1; min-width:85px; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:0.8rem; padding:8px 10px;">
              <x-lucide-arrow-down-left style="width:14px; height:14px;" /> Pendapatan
            </button>
            <button type="button" class="type-selector-btn" id="btn-cat-expense" data-type="expense" style="flex:1; min-width:85px; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:0.8rem; padding:8px 10px;">
              <x-lucide-arrow-up-right style="width:14px; height:14px;" /> Beban
            </button>
          </div>
        </div>

        <!-- Vector Icon Selector -->
        <div class="form-group">
          <label class="form-label">Ikon Kategori</label>
          <input type="hidden" name="icon" id="modal-cat-icon" value="tag">
          <div class="category-icon-picker" style="display:flex; flex-wrap:wrap; gap:8px; max-height: 125px; overflow-y: auto; padding: 8px; border: 1px solid var(--border-primary); border-radius: 8px; background: rgba(0,0,0,0.18)">
            @php
              $icons = ['tag', 'receipt', 'wallet', 'coins', 'landmark', 'building-2', 'briefcase', 'file-text', 'credit-card', 'graduation-cap', 'wrench', 'zap', 'car', 'laptop', 'activity', 'phone', 'coffee', 'shield-check', 'truck', 'layers'];
            @endphp
            @foreach($icons as $iconName)
              <button type="button" class="btn btn-icon btn-secondary icon-select-btn {{ $iconName === 'tag' ? 'active' : '' }}" 
                data-icon="{{ $iconName }}" 
                style="min-width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; border-radius:8px; transition: all 0.15s ease;"
                title="{{ $iconName }}"
              ><x-dynamic-component :component="'lucide-' . $iconName" style="width:16px; height:16px;" /></button>
            @endforeach
          </div>
        </div>

        <!-- Color Picker -->
        <div class="form-group">
          <label class="form-label">Warna Aksen</label>
          <input type="hidden" name="color" id="modal-cat-color" value="#10b981">
          <div class="color-container" style="display:flex; flex-wrap:wrap; gap:8px">
            @php
              $colors = ['#10b981', '#6366f1', '#8b5cf6', '#f43f5e', '#ef4444', '#f97316', '#ec4899', '#e11d48', '#14b8a6', '#06b6d4', '#3b82f6', '#d946ef', '#f59e0b', '#dc2626', '#84cc16'];
            @endphp
            @foreach($colors as $color)
              <button type="button" class="btn btn-icon color-btn" 
                data-color="{{ $color }}" 
                style="background:{{ $color }}; width:32px; height:32px; border-radius:8px; border: 2px solid transparent; cursor:pointer"
              ></button>
            @endforeach
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-modal-cancel">Batal</button>
        <button type="submit" class="btn btn-primary" id="btn-modal-save">Tambah Kategori</button>
      </div>
    </form>
  </div>
</div>

<!-- Import Category Modal Overlay -->
<div class="modal-overlay" id="import-modal-overlay">
  <div class="modal">
    <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
          <x-lucide-upload style="width:20px; height:20px; color:var(--accent-primary);" />
          Import Bagan Akun / Kategori
        </h3>
        <button type="button" class="modal-close" id="btn-import-close"><x-lucide-x style="width:18px; height:18px;" /></button>
      </div>
      <div class="modal-body">
        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label">Berkas Excel atau CSV</label>
          <input type="file" name="file" class="form-input" accept=".xlsx,.xls,.csv" required style="padding: 12px 10px; background: rgba(255,255,255,0.05); border: 1px dashed var(--border-primary); cursor: pointer; width: 100%;">
          <p style="font-size: 0.8rem; color: #a0aec0; margin-top: 8px;">
            Pilih file CSV/Excel yang berisi kolom <strong>NP</strong> (Kode Kampus) dan <strong>Account</strong> (Nama Kategori).
            Anda juga dapat mengunduh <a href="{{ route('categories.import-template') }}" style="color: #818cf8; text-decoration: underline; font-weight: 500;">Template Kategori (CSV)</a> sebagai acuan.
          </p>
        </div>
        <div style="background: rgba(16, 24, 48, 0.5); padding: 16px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.05); font-size: 0.85rem;">
          <span style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px; color: #818cf8;">
            <x-lucide-info style="width:16px; height:16px;" /> Aturan Import Cerdas:
          </span>
          <ul style="list-style-type: disc; padding-left: 20px; color: #cbd5e1; display: flex; flex-direction: column; gap: 4px; margin: 0;">
            <li>Kolom NP: <strong>Kode Akun Kampus (mis. 4-1010, 5-1100)</strong>.</li>
            <li>Kolom Account: <strong>Nama Kategori</strong>.</li>
            <li>Tipe otomatis dideteksi: awalan <strong>4 atau 7</strong> adalah Pemasukan, awalan lainnya Pengeluaran.</li>
            <li>Jika kode sudah terdaftar, data nama kategori akan diperbarui.</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-import-cancel">Batal</button>
        <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
          <x-lucide-upload style="width:16px; height:16px;" /> Mulai Import
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // --- Tabs & Search Filtering Logic ---
    const tabs = document.querySelectorAll('.tab-btn');
    const cards = document.querySelectorAll('.category-card');
    const grid = document.getElementById('category-grid');
    const searchInput = document.getElementById('cat-search-input');

    let currentTab = 'all';
    let currentSearch = '';

    function filterCards() {
      let visibleCount = 0;

      cards.forEach(card => {
        const typeMatch = (currentTab === 'all' || card.dataset.type === currentTab);
        const code = card.dataset.code || '';
        const name = card.dataset.name || '';
        const searchMatch = !currentSearch || code.includes(currentSearch) || name.includes(currentSearch);

        if (typeMatch && searchMatch) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      // Show empty state if no category cards visible
      let emptyState = grid.querySelector('.empty-state-filter');
      if (visibleCount === 0) {
        if (!emptyState) {
          emptyState = document.createElement('div');
          emptyState.className = 'empty-state empty-state-filter';
          emptyState.style.gridColumn = '1/-1';
          emptyState.innerHTML = `
            <div class="empty-state-icon" style="display:flex; justify-content:center; margin-bottom:12px;">
              <i data-lucide="search" style="width:40px; height:40px; color:var(--text-muted); stroke-width:1.5;"></i>
            </div>
            <div class="empty-state-title">Tidak ada kategori yang cocok</div>
            <div class="empty-state-desc">Coba gunakan kata kunci pencarian atau filter tipe lain.</div>
          `;
          grid.appendChild(emptyState);
          if (window.lucide) { lucide.createIcons(); }
        } else {
          emptyState.style.display = '';
        }
      } else if (emptyState) {
        emptyState.style.display = 'none';
      }
    }

    // Categories data for search autocomplete list
    const categoriesData = [
      @foreach($categories as $cat)
      {
        id: {{ $cat->id }},
        type: '{{ $cat->type }}',
        code: '{{ addslashes($cat->code ?? '') }}',
        name: '{{ addslashes($cat->name) }}',
        icon: '{{ addslashes($cat->icon ?? "") }}',
        color: '{{ $cat->color }}',
        txCount: {{ $cat->transactions_count ?? 0 }},
        txTotalFormatted: '{{ number_format($cat->transactions_sum_amount ?? 0, 0, ',', '.') }}'
      },
      @endforeach
    ];

    const autocompleteDropdown = document.getElementById('cat-autocomplete-list');
    const searchClearBtn = document.getElementById('cat-search-clear');
    let selectedItemIndex = -1;

    function renderAutocomplete(query) {
      if (!query) {
        autocompleteDropdown.style.display = 'none';
        autocompleteDropdown.innerHTML = '';
        selectedItemIndex = -1;
        return;
      }

      const q = query.toLowerCase();
      // Match by code or name
      const matches = categoriesData.filter(c => {
        const matchesTab = (currentTab === 'all' || c.type === currentTab);
        const matchesQuery = (c.code && c.code.toLowerCase().includes(q)) || c.name.toLowerCase().includes(q);
        return matchesTab && matchesQuery;
      });

      if (matches.length === 0) {
        autocompleteDropdown.innerHTML = `
          <div class="cat-autocomplete-empty">
            <div style="display:flex; justify-content:center; margin-bottom: 6px;">
              <i data-lucide="search" style="width:24px; height:24px; stroke-width:1.5; color:var(--text-muted);"></i>
            </div>
            Tidak ada kategori yang cocok dengan "<strong>${escapeHtml(query)}</strong>"
          </div>
        `;
        autocompleteDropdown.style.display = 'block';
        if (window.lucide) { lucide.createIcons(); }
        selectedItemIndex = -1;
        return;
      }

      let html = `
        <div class="cat-autocomplete-header">
          <span>Hasil Pencarian (${matches.length})</span>
          <span>Tekan ↵ atau klik</span>
        </div>
      `;

      matches.forEach((cat, idx) => {
        let typeLabel = 'Beban';
        let typeBadgeClass = 'badge-expense';
        if (cat.type === 'income') {
          typeLabel = 'Pendapatan';
          typeBadgeClass = 'badge-income';
        } else if (cat.type === 'asset') {
          typeLabel = 'Aset';
          typeBadgeClass = 'badge-asset';
        } else if (cat.type === 'liability') {
          typeLabel = 'Kewajiban';
          typeBadgeClass = 'badge-liability';
        } else if (cat.type === 'equity') {
          typeLabel = 'Ekuitas';
          typeBadgeClass = 'badge-equity';
        }
        const codeBadge = cat.code 
          ? `<span class="cat-autocomplete-code font-mono-num" style="background:${cat.color}20; color:${cat.color}; border: 1px solid ${cat.color}45;">${highlightMatch(cat.code, q)}</span>`
          : '';
        const isLucide = cat.icon && /^[a-z0-9\-]+$/.test(cat.icon);
        const iconName = isLucide ? cat.icon : (cat.type === 'income' ? 'arrow-down-left' : 'arrow-up-right');

        html += `
          <div class="cat-autocomplete-item" data-index="${idx}" data-id="${cat.id}">
            <div class="cat-autocomplete-icon" style="background:${cat.color}20; border: 1px solid ${cat.color}40; color:${cat.color};">
              <i data-lucide="${iconName}" style="width:16px; height:16px;"></i>
            </div>
            <div class="cat-autocomplete-info">
              <div class="cat-autocomplete-title">
                ${codeBadge}
                <span>${highlightMatch(cat.name, q)}</span>
              </div>
              <div class="cat-autocomplete-meta">
                <span class="badge ${typeBadgeClass}" style="font-size: 0.7rem; padding: 1px 6px;">${typeLabel}</span>
                <span>•</span>
                <span class="font-mono-num">${cat.txCount} transaksi (Rp ${cat.txTotalFormatted})</span>
              </div>
            </div>
          </div>
        `;
      });

      autocompleteDropdown.innerHTML = html;
      autocompleteDropdown.style.display = 'block';
      if (window.lucide) { lucide.createIcons(); }
      selectedItemIndex = -1;

      autocompleteDropdown.querySelectorAll('.cat-autocomplete-item').forEach(item => {
        item.addEventListener('click', () => {
          selectCategory(item.dataset.id);
        });
      });
    }

    function selectCategory(catId) {
      const cat = categoriesData.find(c => c.id == catId);
      if (!cat) return;

      // Update input text with category name
      searchInput.value = cat.name;
      currentSearch = cat.name.toLowerCase();
      if (searchClearBtn) searchClearBtn.style.display = 'block';

      // Ensure appropriate tab is active
      if (currentTab !== 'all' && currentTab !== cat.type) {
        tabs.forEach(t => {
          if (t.dataset.tab === 'all') {
            t.classList.add('active');
            currentTab = 'all';
          } else {
            t.classList.remove('active');
          }
        });
      }

      filterCards();
      autocompleteDropdown.style.display = 'none';

      // Scroll to card & pulse animation
      const card = document.getElementById(`cat-card-${cat.id}`);
      if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.classList.remove('card-highlight-pulse');
        void card.offsetWidth; // trigger reflow
        card.classList.add('card-highlight-pulse');
        setTimeout(() => card.classList.remove('card-highlight-pulse'), 2000);
      }
    }

    function highlightMatch(text, query) {
      if (!query || !text) return escapeHtml(text);
      const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const regex = new RegExp(`(${escapedQuery})`, 'gi');
      return escapeHtml(text).replace(regex, '<mark style="background: rgba(243, 167, 18, 0.35); color: inherit; padding: 0 2px; border-radius: 2px;">$1</mark>');
    }

    function escapeHtml(str) {
      return String(str || '').replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[m]));
    }

    function updateItemSelection(items) {
      items.forEach((item, idx) => {
        if (idx === selectedItemIndex) {
          item.classList.add('active-item');
          item.scrollIntoView({ block: 'nearest' });
        } else {
          item.classList.remove('active-item');
        }
      });
    }

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentTab = tab.dataset.tab;
        filterCards();
        if (currentSearch) {
          renderAutocomplete(currentSearch);
        }
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        currentSearch = e.target.value.trim().toLowerCase();
        if (searchClearBtn) {
          searchClearBtn.style.display = currentSearch ? 'block' : 'none';
        }
        renderAutocomplete(currentSearch);
        filterCards();
      });

      searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim()) {
          renderAutocomplete(searchInput.value.trim());
        }
      });

      searchInput.addEventListener('keydown', (e) => {
        const items = autocompleteDropdown.querySelectorAll('.cat-autocomplete-item');
        if (!items.length || autocompleteDropdown.style.display === 'none') return;

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          selectedItemIndex = (selectedItemIndex + 1) % items.length;
          updateItemSelection(items);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          selectedItemIndex = (selectedItemIndex - 1 + items.length) % items.length;
          updateItemSelection(items);
        } else if (e.key === 'Enter') {
          if (selectedItemIndex >= 0 && items[selectedItemIndex]) {
            e.preventDefault();
            items[selectedItemIndex].click();
          }
        } else if (e.key === 'Escape') {
          autocompleteDropdown.style.display = 'none';
        }
      });
    }

    if (searchClearBtn) {
      searchClearBtn.addEventListener('click', () => {
        searchInput.value = '';
        currentSearch = '';
        searchClearBtn.style.display = 'none';
        autocompleteDropdown.style.display = 'none';
        filterCards();
        searchInput.focus();
      });
    }

    document.addEventListener('click', (e) => {
      if (searchInput && autocompleteDropdown) {
        if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
          autocompleteDropdown.style.display = 'none';
        }
      }
    });

    // --- Modal Create/Edit Logic ---
    const overlay = document.getElementById('cat-modal-overlay');
    const form = document.getElementById('cat-modal-form');
    const title = document.getElementById('cat-modal-title');
    const methodInput = document.getElementById('cat-form-method');

    const codeInput = document.getElementById('modal-cat-code');
    const nameInput = document.getElementById('modal-cat-name');
    const typeInput = document.getElementById('modal-cat-type');
    const iconInput = document.getElementById('modal-cat-icon');
    const colorInput = document.getElementById('modal-cat-color');
    
    const btnIncome = document.getElementById('btn-cat-income');
    const btnExpense = document.getElementById('btn-cat-expense');
    
    const saveBtn = document.getElementById('btn-modal-save');

    // Toggle Type Selection
    function setFormType(type) {
      typeInput.value = type;
      document.querySelectorAll('.type-selector-btn').forEach(btn => {
        if (btn.dataset.type === type) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
    }

    document.querySelectorAll('.type-selector-btn').forEach(btn => {
      btn.addEventListener('click', () => setFormType(btn.dataset.type));
    });

    // Vector Icon Select helper
    const iconBtns = document.querySelectorAll('.icon-select-btn');
    iconBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        iconBtns.forEach(b => {
          b.classList.remove('active');
          b.style.borderColor = 'transparent';
        });
        btn.classList.add('active');
        btn.style.borderColor = 'var(--accent-primary)';
        iconInput.value = btn.dataset.icon;
      });
    });

    // Color Select helper
    const colorBtns = document.querySelectorAll('.color-btn');
    function selectColor(color) {
      colorBtns.forEach(btn => {
        if (btn.dataset.color === color) {
          btn.style.borderColor = 'white';
        } else {
          btn.style.borderColor = 'transparent';
        }
      });
      colorInput.value = color;
    }

    colorBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        selectColor(btn.dataset.color);
      });
    });

    // Initial setup colors
    selectColor('#10b981');

    // Open Modal for Create
    document.getElementById('btn-add-category').addEventListener('click', () => {
      form.action = "{{ route('categories.store') }}";
      methodInput.value = "POST";
      title.textContent = "Tambah Kategori Baru";
      
      codeInput.value = '';
      nameInput.value = '';
      setFormType('income');
      
      // select default icon 'tag'
      iconBtns.forEach(b => {
        if (b.dataset.icon === 'tag') {
          b.classList.add('active');
          b.style.borderColor = 'var(--accent-primary)';
        } else {
          b.classList.remove('active');
          b.style.borderColor = 'transparent';
        }
      });
      iconInput.value = 'tag';
      
      selectColor('#10b981');
      
      saveBtn.textContent = 'Tambah Kategori';
      overlay.classList.add('active');
      if (window.lucide) { lucide.createIcons(); }
      setTimeout(() => codeInput.focus(), 100);
    });

    // Open Modal for Edit
    document.querySelectorAll('.btn-edit-cat').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        const code = btn.dataset.code;
        const type = btn.dataset.type;
        const icon = btn.dataset.icon;
        const color = btn.dataset.color;

        form.action = "{{ url('/categories') }}/" + id;
        methodInput.value = "PUT";
        title.innerHTML = `Edit Kategori`;
        
        codeInput.value = code || '';
        nameInput.value = name || '';
        setFormType(type);

        // Select vector icon
        const isLucide = icon && /^[a-z0-9\-]+$/.test(icon);
        const targetIcon = isLucide ? icon : 'tag';
        iconBtns.forEach(b => {
          if (b.dataset.icon === targetIcon) {
            b.classList.add('active');
            b.style.borderColor = 'var(--accent-primary)';
          } else {
            b.classList.remove('active');
            b.style.borderColor = 'transparent';
          }
        });
        iconInput.value = targetIcon;

        selectColor(color);

        saveBtn.textContent = 'Simpan Perubahan';
        overlay.classList.add('active');
        if (window.lucide) { lucide.createIcons(); }
        setTimeout(() => codeInput.focus(), 100);
      });
    });

    // Close Modal helpers
    function closeModal() {
      overlay.classList.remove('active');
    }

    document.getElementById('btn-modal-close').addEventListener('click', closeModal);
    document.getElementById('btn-modal-cancel').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal();
    });

    // --- Import Modal Logic ---
    const importOverlay = document.getElementById('import-modal-overlay');
    const btnImport = document.getElementById('btn-import-category');
    const btnImportClose = document.getElementById('btn-import-close');
    const btnImportCancel = document.getElementById('btn-import-cancel');

    if (btnImport) {
      btnImport.addEventListener('click', () => {
        importOverlay.classList.add('active');
        if (window.lucide) { lucide.createIcons(); }
      });
    }

    function closeImportModal() {
      importOverlay.classList.remove('active');
    }

    if (btnImportClose) btnImportClose.addEventListener('click', closeImportModal);
    if (btnImportCancel) btnImportCancel.addEventListener('click', closeImportModal);
    if (importOverlay) {
      importOverlay.addEventListener('click', (e) => {
        if (e.target === importOverlay) closeImportModal();
      });
    }
  });
</script>
@endsection
