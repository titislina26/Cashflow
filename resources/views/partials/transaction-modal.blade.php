<!-- Add/Edit Transaction Modal Overlay -->
<div class="modal-overlay" id="tx-modal-overlay">
  <div class="modal">
    <form action="{{ route('transactions.store') }}" method="POST" id="tx-modal-form" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="_method" id="tx-form-method" value="POST">
      
      <div class="modal-header">
        <h3 id="tx-modal-title">Tambah Transaksi Baru</h3>
        <button type="button" class="modal-close" id="btn-modal-close" aria-label="Tutup modal">
          <x-lucide-x />
        </button>
      </div>
      
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Tipe Transaksi</label>
          <div class="type-selector">
            <input type="hidden" name="type" id="modal-type" value="expense">
            <button type="button" class="type-selector-btn" id="btn-select-income" data-type="income">
              <x-lucide-arrow-down-left /> Pemasukan
            </button>
            <button type="button" class="type-selector-btn active" id="btn-select-expense" data-type="expense">
              <x-lucide-arrow-up-right /> Pengeluaran
            </button>
            <button type="button" class="type-selector-btn" id="btn-select-transfer" data-type="transfer">
              <x-lucide-arrow-left-right /> Transfer Kas
            </button>
          </div>
        </div>

        <div class="form-group" id="form-group-from-account">
          <label class="form-label" id="modal-account-label">Sumber Rekening Kas</label>
          <select class="form-select" name="account" id="modal-account" required>
            <option value="petty_cash">Kas Kecil (Petty Cash)</option>
            <option value="bank_mandiri_1">Bank Mandiri Giro 1</option>
            <option value="bank_mandiri_2">Bank Mandiri Giro 2</option>
          </select>
          <div id="modal-account-tip" style="font-size:0.75rem; color:var(--text-muted); margin-top:4px; line-height:1.3;">
            Dikhususkan untuk pendanaan operasional ringan dan rutin harian (di bawah Rp 5.000.000).
          </div>
          <div id="modal-account-warning" style="display:none; font-size:0.75rem; color:var(--color-warning); margin-top:4px; font-weight:600; align-items:center; gap:6px;">
            <x-lucide-alert-triangle style="width:14px; height:14px;" /> Peringatan: Pengeluaran Kas Kecil disarankan di bawah Rp 5.000.000.
          </div>
        </div>

        <div class="form-group" id="form-group-to-account" style="display: none;">
          <label class="form-label">Ke Rekening Tujuan Transfer</label>
          <select class="form-select" name="to_account" id="modal-to-account">
            <option value="petty_cash">Kas Kecil (Petty Cash)</option>
            <option value="bank_mandiri_1">Bank Mandiri Giro 1</option>
            <option value="bank_mandiri_2">Bank Mandiri Giro 2</option>
          </select>
          <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
            Dana akan otomatis dipindahkan dari rekening asal ke rekening tujuan secara bersamaan.
          </div>
        </div>

        <div class="form-row">
          <div class="form-group" id="form-group-category">
            <label class="form-label" id="modal-category-label">Akun / Kategori</label>
            <div class="cat-search-wrapper" id="modal-category-wrapper">
              <div class="cat-search-input-box">
                <span id="modal-category-selected-icon">
                  <x-lucide-tag style="width:16px; height:16px; color:var(--text-muted);" />
                </span>
                <input type="text" class="form-input" id="modal-category-search" 
                  placeholder="Ketik kode atau nama akun..." autocomplete="off" required />
                <button type="button" id="modal-category-clear" title="Hapus pilihan" aria-label="Hapus pilihan">
                  <x-lucide-x style="width:14px; height:14px;" />
                </button>
              </div>
              <input type="hidden" name="category_id" id="modal-category" required />
              <div id="modal-category-dropdown" class="cat-dropdown-menu"></div>
            </div>
            <div id="modal-transfer-badge" style="display: none; padding: 10px 14px; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 10px; color: var(--color-primary, #3b82f6); line-height: 1.35;">
              <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 0.85rem;">
                <x-lucide-arrow-left-right style="width:16px; height:16px;" /> [1-1100] Kas dan Setara Kas
              </div>
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                Mutasi internal kas & bank (tidak mempengaruhi Laporan Surplus Defisit).
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal Transaksi</label>
            <input type="date" class="form-input" name="date" id="modal-date" value="{{ date('Y-m-d') }}" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Kode Pemasukan/Pengeluaran (No. Bukti)</label>
          <input type="text" class="form-input" name="voucher_number" id="modal-voucher" 
            placeholder="Contoh: KT. 03 26.001 (Opsional)" />
        </div>

        <div class="form-group">
          <label class="form-label">Proyek/Job (Opsional)</label>
          <select class="form-select" name="job_id" id="modal-job">
            <option value="">— Pilih Proyek (Tidak Ada) —</option>
            @foreach($jobs as $j)
              <option value="{{ $j->id }}">[{{ $j->code }}] {{ $j->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Jumlah (Rp)</label>
          <input type="number" class="form-input" name="amount" id="modal-amount" 
            placeholder="Contoh: 5000000" min="0.01" step="0.01" required />
        </div>

        <div class="form-group">
          <label class="form-label">Bukti Dokumen (Opsional)</label>
          <input type="file" class="form-input" name="attachment" id="modal-attachment" accept=".jpg,.jpeg,.png,.pdf" />
          <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Format: JPG, PNG, PDF (Maks. 5MB)</div>
        </div>

        <div class="form-group">
          <label class="form-label">Deskripsi (Opsional)</label>
          <textarea class="form-textarea" name="description" id="modal-desc" 
            placeholder="Catatan tambahan (tampil di Uraian)..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Keterangan (Opsional)</label>
          <textarea class="form-textarea" name="ket" id="modal-ket" 
            placeholder="Tampil di kolom Keterangan pada cetak bukti..."></textarea>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-modal-cancel">Batal</button>
        <button type="submit" class="btn btn-primary" id="btn-modal-save">Tambah Transaksi</button>
      </div>
    </form>
  </div>
</div>

<style>
  .cat-search-wrapper {
    position: relative;
    width: 100%;
  }
  .cat-search-input-box {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
  }
  #modal-category-selected-icon {
    position: absolute;
    left: 12px;
    font-size: 1.15rem;
    pointer-events: none;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  #modal-category-search {
    padding-left: 38px;
    padding-right: 32px;
    width: 100%;
    cursor: text;
  }
  #modal-category-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    font-size: 0.85rem;
    color: var(--text-muted, #64748b);
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    line-height: 1;
    display: none;
    z-index: 2;
  }
  #modal-category-clear:hover {
    color: var(--text-primary, #0f172a);
    background: var(--bg-glass-hover, rgba(0, 0, 0, 0.05));
  }
  .cat-dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: var(--bg-secondary, #ffffff);
    border: 1px solid var(--border-primary, rgba(22, 34, 82, 0.12));
    border-radius: 10px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    max-height: 230px;
    overflow-y: auto;
    z-index: 1050;
    display: none;
  }
  .cat-dropdown-header {
    padding: 8px 12px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted, #64748b);
    border-bottom: 1px solid var(--border-primary, rgba(22, 34, 82, 0.06));
    background: var(--bg-glass, rgba(22, 34, 82, 0.02));
    display: flex;
    justify-content: space-between;
  }
  .cat-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    cursor: pointer;
    transition: background 0.15s ease;
    border-bottom: 1px solid var(--border-primary, rgba(22, 34, 82, 0.04));
  }
  .cat-dropdown-item:last-child {
    border-bottom: none;
  }
  .cat-dropdown-item:hover,
  .cat-dropdown-item.active-item {
    background: rgba(99, 102, 241, 0.09);
  }
  .cat-dropdown-item-code {
    font-family: monospace;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    letter-spacing: 0.5px;
    background: rgba(99, 102, 241, 0.12);
    color: var(--color-primary, #4f46e5);
    flex-shrink: 0;
  }
  .cat-dropdown-item-name {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-primary, #0f172a);
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .cat-dropdown-empty {
    padding: 16px 12px;
    text-align: center;
    color: var(--text-muted, #64748b);
    font-size: 0.85rem;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalCategories = [
    @foreach($categories as $cat)
    {
      id: {{ $cat->id }},
      type: '{{ $cat->type }}',
      code: '{{ addslashes($cat->code ?? '') }}',
      name: '{{ addslashes($cat->name) }}',
      icon: '{{ addslashes($cat->icon ?? "") }}',
      color: '{{ $cat->color ?? "#6366f1" }}'
    },
    @endforeach
  ];

  const searchInput = document.getElementById('modal-category-search');
  const hiddenInput = document.getElementById('modal-category');
  const iconSpan = document.getElementById('modal-category-selected-icon');
  const clearBtn = document.getElementById('modal-category-clear');
  const dropdown = document.getElementById('modal-category-dropdown');
  const typeInput = document.getElementById('modal-type');
  const txForm = document.getElementById('tx-modal-form');
  let activeIndex = -1;
  let currentFiltered = [];

  function getCurrentType() {
    return typeInput ? typeInput.value : 'expense';
  }

  function getCategoryLabel(cat) {
    return cat.code ? `[${cat.code}] ${cat.name}` : cat.name;
  }

  function setSelectedCategory(cat) {
    if (!cat) {
      hiddenInput.value = '';
      searchInput.value = '';
      if (iconSpan) {
        iconSpan.innerHTML = '<i data-lucide="tag" style="width:16px; height:16px; color:var(--text-muted);"></i>';
        if (window.lucide) lucide.createIcons();
      }
      if (clearBtn) clearBtn.style.display = 'none';
      return;
    }
    hiddenInput.value = cat.id;
    searchInput.value = getCategoryLabel(cat);
    if (iconSpan) {
      iconSpan.innerHTML = `<span style="width:12px; height:12px; border-radius:50%; background:${cat.color || '#6366f1'}; display:inline-block;"></span>`;
    }
    if (clearBtn) clearBtn.style.display = 'block';
    closeDropdown();
  }

  function setSelectedCategoryById(id) {
    const cat = modalCategories.find(c => c.id == id);
    if (cat) {
      setSelectedCategory(cat);
    }
  }

  function getCategoriesForType(type, query = '') {
    const q = query ? query.toLowerCase().trim() : '';
    const primaryTypes = (type === 'income') ? ['income'] : ['expense'];
    
    if (!q) {
      return modalCategories.filter(c => primaryTypes.includes(c.type))
                            .sort((a, b) => (a.code || '').localeCompare(b.code || ''));
    }
    
    return modalCategories.filter(c => {
      return (c.code && c.code.toLowerCase().includes(q)) || c.name.toLowerCase().includes(q);
    }).sort((a, b) => {
      const aPrimary = primaryTypes.includes(a.type) ? 0 : 1;
      const bPrimary = primaryTypes.includes(b.type) ? 0 : 1;
      if (aPrimary !== bPrimary) return aPrimary - bPrimary;
      return (a.code || '').localeCompare(b.code || '');
    });
  }

  function setDefaultCategoryForType(type) {
    const available = getCategoriesForType(type);
    if (available.length > 0) {
      setSelectedCategory(available[0]);
    } else {
      setSelectedCategory(null);
    }
  }

  function escapeHtml(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function highlightMatch(text, query) {
    if (!query || !text) return escapeHtml(text);
    const q = query.trim();
    if (!q) return escapeHtml(text);
    const idx = text.toLowerCase().indexOf(q.toLowerCase());
    if (idx === -1) return escapeHtml(text);
    const before = escapeHtml(text.substring(0, idx));
    const match = escapeHtml(text.substring(idx, idx + q.length));
    const after = escapeHtml(text.substring(idx + q.length));
    return `${before}<mark style="background: rgba(245, 158, 11, 0.35); color: inherit; padding: 0 1px; border-radius: 2px;">${match}</mark>${after}`;
  }

  function renderDropdown(items, query) {
    currentFiltered = items;
    activeIndex = -1;

    if (!items.length) {
      dropdown.innerHTML = `
        <div class="cat-dropdown-empty">
          <i data-lucide="search-x" style="width:24px; height:24px; margin: 0 auto 6px; display:block; color:var(--text-muted);"></i>
          Tidak ada akun yang cocok dengan "<strong>${escapeHtml(query)}</strong>"
        </div>
      `;
      dropdown.style.display = 'block';
      if (window.lucide) lucide.createIcons();
      return;
    }

    let html = `
      <div class="cat-dropdown-header">
        <span>Daftar Akun (${items.length})</span>
        <span>Gunakan ↑↓ Enter</span>
      </div>
    `;

    items.forEach((cat, idx) => {
      const isSelected = (hiddenInput.value == cat.id);
      const codeBadge = cat.code 
        ? `<span class="cat-dropdown-item-code">${highlightMatch(cat.code, query)}</span>`
        : '';
      html += `
        <div class="cat-dropdown-item ${isSelected ? 'active-item' : ''}" data-index="${idx}" data-id="${cat.id}">
          <span style="width: 10px; height: 10px; border-radius: 50%; background: ${cat.color || '#6366f1'}; display: inline-block; flex-shrink: 0;"></span>
          ${codeBadge}
          <span class="cat-dropdown-item-name">${highlightMatch(cat.name, query)}</span>
        </div>
      `;
    });

    dropdown.innerHTML = html;
    dropdown.style.display = 'block';

    dropdown.querySelectorAll('.cat-dropdown-item').forEach(el => {
      el.addEventListener('click', (e) => {
        e.stopPropagation();
        const id = el.dataset.id;
        setSelectedCategoryById(id);
      });
    });
  }

  function filterAndRender(query) {
    const type = getCurrentType();
    const matched = getCategoriesForType(type, query);
    renderDropdown(matched, query);
  }

  function openDropdown() {
    filterAndRender(searchInput.value);
  }

  function closeDropdown() {
    dropdown.style.display = 'none';
    dropdown.innerHTML = '';
    activeIndex = -1;
  }

  // Keyboard navigation
  searchInput.addEventListener('keydown', (e) => {
    if (dropdown.style.display === 'block') {
      const items = dropdown.querySelectorAll('.cat-dropdown-item');
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (items.length > 0) {
          activeIndex = (activeIndex + 1) % items.length;
          updateActiveItem(items);
        }
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (items.length > 0) {
          activeIndex = (activeIndex - 1 + items.length) % items.length;
          updateActiveItem(items);
        }
      } else if (e.key === 'Enter') {
        if (activeIndex >= 0 && items[activeIndex]) {
          e.preventDefault();
          items[activeIndex].click();
        } else if (currentFiltered.length > 0) {
          e.preventDefault();
          setSelectedCategory(currentFiltered[0]);
        }
      } else if (e.key === 'Escape') {
        closeDropdown();
      }
    } else {
      if (e.key === 'ArrowDown' || e.key === 'Enter') {
        openDropdown();
      }
    }
  });

  function updateActiveItem(items) {
    items.forEach((item, idx) => {
      if (idx === activeIndex) {
        item.classList.add('active-item');
        item.scrollIntoView({ block: 'nearest' });
      } else {
        item.classList.remove('active-item');
      }
    });
  }

  searchInput.addEventListener('input', () => {
    hiddenInput.value = '';
    clearBtn.style.display = searchInput.value ? 'block' : 'none';
    filterAndRender(searchInput.value);
  });

  searchInput.addEventListener('focus', () => {
    searchInput.select();
    openDropdown();
  });

  clearBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    searchInput.value = '';
    hiddenInput.value = '';
    clearBtn.style.display = 'none';
    searchInput.focus();
    filterAndRender('');
  });

  document.addEventListener('click', (e) => {
    const wrapper = document.getElementById('modal-category-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
      closeDropdown();
      if (hiddenInput.value) {
        const cat = modalCategories.find(c => c.id == hiddenInput.value);
        if (cat) {
          searchInput.value = getCategoryLabel(cat);
          if (iconSpan) {
            iconSpan.innerHTML = `<span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${cat.color};"></span>`;
          }
        }
      }
    }
  });

  const toAccountGroup = document.getElementById('form-group-to-account');
  const accountLabel = document.getElementById('modal-account-label');
  const categoryWrapper = document.getElementById('modal-category-wrapper');
  const transferBadge = document.getElementById('modal-transfer-badge');
  const toAccountSelect = document.getElementById('modal-to-account');
  const accountSelect = document.getElementById('modal-account');
  const saveBtn = document.getElementById('btn-modal-save');
  const modalVoucher = document.getElementById('modal-voucher');

  if (accountSelect && toAccountSelect) {
    accountSelect.addEventListener('change', () => {
      if (getCurrentType() === 'transfer' && toAccountSelect.value === accountSelect.value) {
        const opt = Array.from(toAccountSelect.options).find(o => o.value !== accountSelect.value);
        if (opt) toAccountSelect.value = opt.value;
      }
    });
  }

  if (txForm) {
    txForm.addEventListener('submit', (e) => {
      const type = getCurrentType();
      if (type === 'transfer') {
        if (accountSelect.value === toAccountSelect.value) {
          e.preventDefault();
          alert('Rekening asal dan tujuan transfer tidak boleh sama.');
          toAccountSelect.focus();
          return;
        }
      } else {
        if (!hiddenInput.value) {
          e.preventDefault();
          searchInput.focus();
          openDropdown();
        }
      }
    });
  }

  // Register globals
  window.selectModalCategoryById = setSelectedCategoryById;
  window.selectModalCategoryDefault = setDefaultCategoryForType;
  window.closeCategoryDropdown = closeDropdown;
  window.onTransactionTypeChange = function(type) {
    if (type === 'transfer') {
      if (toAccountGroup) toAccountGroup.style.display = 'block';
      if (accountLabel) accountLabel.textContent = 'Dari Rekening (Asal Dana)';
      if (categoryWrapper) categoryWrapper.style.display = 'none';
      if (transferBadge) transferBadge.style.display = 'block';
      if (searchInput) searchInput.removeAttribute('required');
      if (hiddenInput) {
        hiddenInput.removeAttribute('required');
        hiddenInput.value = '60'; // id for [1-1100] Kas dan Setara Kas
      }
      if (saveBtn) saveBtn.textContent = 'Proses Transfer Dana';
      if (modalVoucher) modalVoucher.placeholder = 'Contoh: No. Cek Mandiri / Bukti Transfer';
      
      // Auto adjust to_account so it is not the same as source account
      if (accountSelect && toAccountSelect) {
        if (toAccountSelect.value === accountSelect.value) {
          const opt = Array.from(toAccountSelect.options).find(o => o.value !== accountSelect.value);
          if (opt) toAccountSelect.value = opt.value;
        }
      }
      closeDropdown();
    } else {
      if (toAccountGroup) toAccountGroup.style.display = 'none';
      if (accountLabel) accountLabel.textContent = 'Sumber Dana';
      if (categoryWrapper) categoryWrapper.style.display = 'block';
      if (transferBadge) transferBadge.style.display = 'none';
      if (searchInput) searchInput.setAttribute('required', 'required');
      if (hiddenInput) hiddenInput.setAttribute('required', 'required');
      if (saveBtn) saveBtn.textContent = 'Tambah Transaksi';
      if (modalVoucher) modalVoucher.placeholder = 'Contoh: KT. 03 26.001 (Opsional)';

      const currentId = hiddenInput.value;
      const currentCat = modalCategories.find(c => c.id == currentId);
      if (!currentCat || currentCat.type !== type) {
        setDefaultCategoryForType(type);
      }
      if (dropdown.style.display === 'block') {
        filterAndRender(searchInput.value);
      }
    }
  };
});
</script>
