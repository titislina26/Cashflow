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
          <div class="form-group">
            <label class="form-label">Tanggal Transaksi</label>
            <input type="date" class="form-input" name="date" id="modal-date" value="{{ date('Y-m-d') }}" required />
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
        </div>

        <div class="form-group" id="form-group-voucher">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <label class="form-label" style="margin-bottom:0;">No. Bukti / Dokumen</label>
            <span style="font-size:0.75rem; color:var(--text-muted);">Pilih prefix:</span>
          </div>
          <input type="text" class="form-input" name="voucher_number" id="modal-voucher" 
            placeholder="Contoh: KT. 01 26.001 (Opsional)" />
          <div class="voucher-prefix-chips" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:8px;">
            <button type="button" class="btn-prefix-chip" data-prefix="KT." data-type="expense" title="Pengeluaran Kas"><strong>KT.</strong> Pengeluaran</button>
            <button type="button" class="btn-prefix-chip" data-prefix="TT." data-type="income" title="Pemasukan ke Kas"><strong>TT.</strong> Masuk Kas</button>
            <button type="button" class="btn-prefix-chip" data-prefix="TTN." data-type="income" title="Penerimaan dari Bank / Pencairan Cek"><strong>TTN.</strong> Pencairan Bank/Cek</button>
            <button type="button" class="btn-prefix-chip" data-prefix="P." data-type="expense" title="Panjar Kerja"><strong>P.</strong> Panjar</button>
            <button type="button" class="btn-prefix-chip" data-prefix="PP." data-type="income" title="Pengembalian Panjar"><strong>PP.</strong> Kembali Panjar</button>
            <button type="button" class="btn-prefix-chip" data-prefix="PK." data-type="income" title="Pengembalian Pinjaman Karyawan"><strong>PK.</strong> Pinjam Karyawan</button>
          </div>
        </div>

        <!-- Section Header Rincian Transaksi & Tombol Tambah Baris -->
        <div id="multi-item-header-bar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; margin-bottom: 12px; padding: 10px 14px; background: rgba(59, 130, 246, 0.05); border: 1px dashed rgba(59, 130, 246, 0.3); border-radius: 8px;">
          <div>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
              <x-lucide-layers style="width: 15px; height: 15px; color: var(--color-primary, #3b82f6);" /> Rincian Pos Transaksi
            </div>
            <div style="font-size: 0.73rem; color: var(--text-muted); margin-top: 2px;">
              Bisa mencatat lebih dari 1 pos transaksi/pencairan dalam 1 nomor bukti
            </div>
          </div>
          <button type="button" class="btn btn-secondary btn-sm" id="btn-add-item-row" style="padding: 5px 12px; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 5px; font-weight: 600; cursor: pointer; border-radius: 6px;">
            <x-lucide-plus style="width: 14px; height: 14px;" /> <span id="btn-add-row-text">Tambah Pos Rincian</span>
          </button>
        </div>

        <!-- Pos 1 (Pos Utama) -->
        <div id="pos-1-wrapper" style="padding: 12px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08)); border-radius: 8px; margin-bottom: 10px;">
          <div id="pos-1-badge" style="display: none; font-size: 0.78rem; font-weight: 700; color: var(--color-primary, #3b82f6); margin-bottom: 8px;">
            Pos 1
          </div>

          <div class="form-row">
            <div class="form-group" id="form-group-category" style="margin-bottom: 10px;">
              <label class="form-label" id="modal-category-label">Akun / Kategori</label>
              <div class="cat-search-wrapper" id="modal-category-wrapper">
                <div class="cat-search-input-box">
                  <span class="cat-selected-icon" id="modal-category-selected-icon">
                    <x-lucide-tag style="width:16px; height:16px; color:var(--text-muted);" />
                  </span>
                  <input type="text" class="form-input cat-search-input" id="modal-category-search" 
                    placeholder="Ketik kode atau nama akun..." autocomplete="off" required />
                  <button type="button" class="cat-search-clear" id="modal-category-clear" title="Hapus pilihan" aria-label="Hapus pilihan" style="display: none;">
                    <x-lucide-x style="width:14px; height:14px;" />
                  </button>
                </div>
                <input type="hidden" name="category_id" class="cat-category-id" id="modal-category" />
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

            <div class="form-group" style="margin-bottom: 10px;">
              <label class="form-label">Jumlah (Rp)</label>
              <input type="number" class="form-input" name="amount" id="modal-amount" 
                placeholder="Contoh: 120000" min="0.01" step="0.01" required />
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Uraian / Deskripsi Pos</label>
            <input type="text" class="form-input" name="description" id="modal-desc" 
              placeholder="Contoh: Pembayaran Sampah / Pengiriman JNE..." />
          </div>
        </div>

        <!-- Container untuk Pos Rincian Tambahan (Pos 2, 3, dst.) -->
        <div id="extra-items-container" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 10px;"></div>

        <!-- Total Kalkulasi Multi-Item (Muncul otomatis jika ada lebih dari 1 pos) -->
        <div id="multi-item-summary-box" style="display: none; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 8px; padding: 12px 14px; margin-bottom: 14px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">
              Total Keseluruhan (<span id="multi-item-count">0</span> Pos):
            </span>
            <span id="multi-item-total-display" class="font-mono-num" style="font-size: 1.15rem; font-weight: 800; color: var(--color-expense, #ef4444);">
              Rp 0
            </span>
          </div>
          <div id="multi-item-terbilang-display" style="font-size: 0.78rem; color: var(--text-muted); font-style: italic; margin-top: 4px;"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Bukti Dokumen / Lampiran (Opsional)</label>
          <input type="file" class="form-input" name="attachment" id="modal-attachment" accept=".jpg,.jpeg,.png,.pdf" />
          <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Format: JPG, PNG, PDF (Maks. 5MB)</div>
        </div>

        <div class="form-group">
          <label class="form-label">Yang Mengajukan (Opsional)</label>
          <input type="text" class="form-input" name="paraf" id="modal-paraf" list="paraf-list"
            placeholder="Contoh: Budi Santoso / Titis Marsela..." autocomplete="off" />
          <datalist id="paraf-list">
            <option value="Agung Prasetyo, A.Md"></option>
            <option value="Ai Sri Asih Suherman"></option>
            <option value="Andreas Victor Halomoan Simanungkalit S.Kom., M.S.I"></option>
            <option value="Bambang Triguno S.T., M.T"></option>
            <option value="Benny Kusdinar S.T., M.M."></option>
            <option value="Bobby Pratama"></option>
            <option value="Dedi Iskandar, S.T., M.T.I"></option>
            <option value="Deni Kelana Nurjaya"></option>
            <option value="Dian Sovana, S.T., M.T"></option>
            <option value="Dr. Ir. Arie Setiadi Moerwanto, M.Sc"></option>
            <option value="Dr. Ir. Ismail Widadi, S.T., M.Sc"></option>
            <option value="Dr. Ir. Muktar Napitupulu, M.Sc"></option>
            <option value="Dr. Ir. Slamet Muljono, M.Eng.Sc"></option>
            <option value="Dr. Ir. Timbul P.M Panjaitan, MA"></option>
            <option value="Drs. Gunawan Wibisono, M.T."></option>
            <option value="Eda Loisa Kosapitu S.T., M.T"></option>
            <option value="Edi Pramono S.T., M.T"></option>
            <option value="Egi Ruswandi S.Pd"></option>
            <option value="Eko Nurlita, S.T, M.T"></option>
            <option value="Elly Noriza M.A.,S.T"></option>
            <option value="Elmi Besty Pratiwi S.T., M.T.I"></option>
            <option value="Endah Yunari, ST, MT"></option>
            <option value="Erwinda Firna Safitri, ST"></option>
            <option value="Fajar Lazuardi"></option>
            <option value="Heldy Suherman, ST, Msi"></option>
            <option value="Iis Trisnawati ST., MT"></option>
            <option value="Ir. Amien Sajekti, MT"></option>
            <option value="Ir. Effy Hidayati, MT"></option>
            <option value="Ir. Rina Agustin Indriyani, MURP"></option>
            <option value="Irma Yaniarti"></option>
            <option value="Jamaludin"></option>
            <option value="Kartika Wati, S.T."></option>
            <option value="Kristianti Utomo S.T., M.Si"></option>
            <option value="Kusnadi Efendi, S.M"></option>
            <option value="M. Alif Syahdila Rivian, S.I.Kom"></option>
            <option value="Mahdia Raisa Hanifa Arifin, S.T"></option>
            <option value="Moch. Miftahudin"></option>
            <option value="Mohamad Farhan Ali"></option>
            <option value="Muhamad Armin"></option>
            <option value="Muhammad Ihya Aulia Elfatiha, S.Kom., M.Kom"></option>
            <option value="Nooraini Kartikarini, S.M"></option>
            <option value="Nurul Fitriasih, ST"></option>
            <option value="Partono"></option>
            <option value="Prayoga Adhinugroho S.T, M.Sc."></option>
            <option value="Raga Wahyudi"></option>
            <option value="Ridwan, S.T., M.Kom"></option>
            <option value="Risan Puntaningrum, S.Pd"></option>
            <option value="Rizki Nugraha, S.Kom"></option>
            <option value="Rochman Rosyid, ST, MT"></option>
            <option value="Romdoni"></option>
            <option value="Samsul Ma'rif"></option>
            <option value="Sigit Himawan S.T, M.Sc."></option>
            <option value="Siti Maryam"></option>
            <option value="Sugiyatno, S.Kom, M. Kom"></option>
            <option value="Suma"></option>
            <option value="Titis Marselina Milsy"></option>
            <option value="Tony Purba M.Kom,S.T"></option>
            <option value="Trimo Pamudji Al Djono S.T, M.Si"></option>
            <option value="Yudistira Samudra SE"></option>
          </datalist>
          <div style="font-size:0.75rem; color:var(--text-muted); margin-top:4px;">Nama ini otomatis masuk ke kolom "Yang Menerima" pada cetak bukti pengeluaran.</div>
        </div>

        <div class="form-group">
          <label class="form-label">Keterangan Tambahan (Opsional)</label>
          <textarea class="form-textarea" name="ket" id="modal-ket" rows="2"
            placeholder="Catatan tambahan (tampil di kolom Keterangan pada cetak bukti)..."></textarea>
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
  .cat-selected-icon {
    position: absolute;
    left: 12px;
    pointer-events: none;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .cat-search-input {
    padding-left: 38px !important;
    padding-right: 32px !important;
    width: 100%;
    cursor: text;
  }
  .cat-search-clear {
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
    align-items: center;
    justify-content: center;
  }
  .cat-search-clear:hover {
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
  .btn-prefix-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    font-size: 0.72rem;
    font-weight: 500;
    border-radius: 6px;
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.12));
    background: var(--bg-card, rgba(255, 255, 255, 0.04));
    color: var(--text-secondary, #94a3b8);
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .btn-prefix-chip strong {
    color: var(--text-primary, #f1f5f9);
    font-family: monospace;
    font-size: 0.76rem;
  }
  .btn-prefix-chip:hover {
    border-color: var(--color-primary, #3b82f6);
    color: var(--color-primary, #3b82f6);
    background: rgba(59, 130, 246, 0.12);
    transform: translateY(-1px);
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

  const typeInput = document.getElementById('modal-type');
  const txForm = document.getElementById('tx-modal-form');
  const transferCat = modalCategories.find(c => c.code === '1-1100') || { id: '60' };
  const transferCatId = transferCat ? transferCat.id : '60';

  function getCurrentType() {
    return typeInput ? typeInput.value : 'expense';
  }

  function escapeHtml(str) {
    return (str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
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

  function getCategoryLabel(cat) {
    return cat.code ? `[${cat.code}] ${cat.name}` : cat.name;
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

  function initCategorySearch(wrapper) {
    if (!wrapper) return null;
    const iconSpan = wrapper.querySelector('.cat-selected-icon');
    const searchInput = wrapper.querySelector('.cat-search-input');
    const clearBtn = wrapper.querySelector('.cat-search-clear');
    const hiddenInput = wrapper.querySelector('.cat-category-id');
    const dropdown = wrapper.querySelector('.cat-dropdown-menu');

    let currentFiltered = [];
    let activeIndex = -1;

    function setSelectedCategory(cat) {
      if (!cat) {
        hiddenInput.value = '';
        searchInput.value = '';
        if (iconSpan) {
          iconSpan.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>';
        }
        if (clearBtn) clearBtn.style.display = 'none';
        return;
      }
      hiddenInput.value = cat.id;
      searchInput.value = getCategoryLabel(cat);
      if (iconSpan) {
        iconSpan.innerHTML = `<span style="width:12px; height:12px; border-radius:50%; background:${cat.color || '#6366f1'}; display:inline-block;"></span>`;
      }
      if (clearBtn) clearBtn.style.display = 'flex';
      closeDropdown();
    }

    function setSelectedCategoryById(id) {
      const cat = modalCategories.find(c => c.id == id);
      if (cat) {
        setSelectedCategory(cat);
      } else if (!id) {
        setSelectedCategory(null);
      }
    }

    function renderDropdown(items, query) {
      currentFiltered = items;
      activeIndex = -1;

      if (!items.length) {
        dropdown.innerHTML = `
          <div class="cat-dropdown-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 6px; display:block; color:var(--text-muted);"><circle cx="10" cy="10" r="7"/><path d="m21 21-4.3-4.3"/><path d="m8 8 4 4"/><path d="m12 8-4 4"/></svg>
            Tidak ada akun yang cocok dengan "<strong>${escapeHtml(query)}</strong>"
          </div>
        `;
        dropdown.style.display = 'block';
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
          setSelectedCategoryById(el.dataset.id);
        });
      });
    }

    function filterAndRender(query) {
      const type = getCurrentType();
      const matched = getCategoriesForType(type, query);
      renderDropdown(matched, query);
    }

    function openDropdown() {
      // Close other dropdowns first
      document.querySelectorAll('.cat-dropdown-menu').forEach(d => {
        if (d !== dropdown) {
          d.style.display = 'none';
          d.innerHTML = '';
        }
      });
      filterAndRender(searchInput.value);
    }

    function closeDropdown() {
      dropdown.style.display = 'none';
      dropdown.innerHTML = '';
      activeIndex = -1;
    }

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

    searchInput.addEventListener('input', () => {
      hiddenInput.value = '';
      clearBtn.style.display = searchInput.value ? 'flex' : 'none';
      filterAndRender(searchInput.value);
    });

    searchInput.addEventListener('focus', () => {
      searchInput.select();
      openDropdown();
    });

    clearBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      setSelectedCategory(null);
      searchInput.focus();
      openDropdown();
    });

    document.addEventListener('click', (e) => {
      if (!wrapper.contains(e.target)) {
        closeDropdown();
        if (hiddenInput.value) {
          const cat = modalCategories.find(c => c.id == hiddenInput.value);
          if (cat) {
            searchInput.value = getCategoryLabel(cat);
            if (iconSpan) {
              iconSpan.innerHTML = `<span style="width:12px; height:12px; border-radius:50%; background:${cat.color || '#6366f1'}; display:inline-block;"></span>`;
            }
          }
        }
      }
    });

    return {
      wrapper,
      searchInput,
      hiddenInput,
      setSelectedCategory,
      setSelectedCategoryById,
      openDropdown,
      closeDropdown,
      filterAndRender,
      setDefaultCategory: function(type) {
        const available = getCategoriesForType(type);
        if (available.length > 0) {
          setSelectedCategory(available[0]);
        } else {
          setSelectedCategory(null);
        }
      }
    };
  }

  const modalCategoryWrapper = document.getElementById('modal-category-wrapper');
  const pos1CatSearch = initCategorySearch(modalCategoryWrapper);

  function setSelectedCategoryById(id) {
    if (pos1CatSearch) {
      pos1CatSearch.setSelectedCategoryById(id);
    }
  }

  function setDefaultCategoryForType(type) {
    if (pos1CatSearch) {
      pos1CatSearch.setDefaultCategory(type);
    }
  }

  function closeDropdown() {
    document.querySelectorAll('.cat-dropdown-menu').forEach(d => {
      d.style.display = 'none';
      d.innerHTML = '';
    });
  }

  const toAccountGroup = document.getElementById('form-group-to-account');
  const accountLabel = document.getElementById('modal-account-label');
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

  // Initial state: disable to_account unless transfer
  if (toAccountSelect && getCurrentType() !== 'transfer') {
    toAccountSelect.disabled = true;
  }

  // Multi-Item elements
  const multiItemHeaderBar = document.getElementById('multi-item-header-bar');
  const btnAddItemRow = document.getElementById('btn-add-item-row');
  const pos1Wrapper = document.getElementById('pos-1-wrapper');
  const pos1Badge = document.getElementById('pos-1-badge');
  const extraItemsContainer = document.getElementById('extra-items-container');
  const multiItemCountBadge = document.getElementById('multi-item-count-badge');
  const multiItemSummaryBox = document.getElementById('multi-item-summary-box');
  const multiItemCount = document.getElementById('multi-item-count');
  const multiItemTotalDisplay = document.getElementById('multi-item-total-display');
  const multiItemTerbilangDisplay = document.getElementById('multi-item-terbilang-display');
  const btnAddRowText = document.getElementById('btn-add-row-text');
  const pos1AmountInput = document.getElementById('modal-amount');

  function terbilangNumber(angka) {
    angka = Math.floor(Math.abs(angka));
    const words = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
    let temp = "";
    if (angka < 12) {
      temp = " " + words[angka];
    } else if (angka < 20) {
      temp = terbilangNumber(angka - 10) + " Belas";
    } else if (angka < 100) {
      temp = terbilangNumber(Math.floor(angka / 10)) + " Puluh" + terbilangNumber(angka % 10);
    } else if (angka < 200) {
      temp = " Seratus" + terbilangNumber(angka - 100);
    } else if (angka < 1000) {
      temp = terbilangNumber(Math.floor(angka / 100)) + " Ratus" + terbilangNumber(angka % 100);
    } else if (angka < 2000) {
      temp = " Seribu" + terbilangNumber(angka - 1000);
    } else if (angka < 1000000) {
      temp = terbilangNumber(Math.floor(angka / 1000)) + " Ribu" + terbilangNumber(angka % 1000);
    } else if (angka < 1000000000) {
      temp = terbilangNumber(Math.floor(angka / 1000000)) + " Juta" + terbilangNumber(angka % 1000000);
    } else if (angka < 1000000000000) {
      temp = terbilangNumber(Math.floor(angka / 1000000000)) + " Miliar" + terbilangNumber(angka % 1000000000);
    } else if (angka < 1000000000000000) {
      temp = terbilangNumber(Math.floor(angka / 1000000000000)) + " Triliun" + terbilangNumber(angka % 1000000000000);
    }
    return temp;
  }

  function getSpelledRupiah(angka) {
    if (!angka || angka === 0) return "Nol Rupiah";
    return (terbilangNumber(angka).trim() + " Rupiah").replace(/\s+/g, ' ');
  }

  function updateMultiItemState() {
    const extraCards = extraItemsContainer ? extraItemsContainer.querySelectorAll('.extra-item-card') : [];
    const totalCount = 1 + extraCards.length;
    const type = getCurrentType();

    if (extraCards.length > 0) {
      if (pos1Badge) pos1Badge.style.display = 'block';
      if (multiItemCountBadge) {
        multiItemCountBadge.style.display = 'inline-block';
        multiItemCountBadge.textContent = `${totalCount} Pos`;
      }
      if (multiItemSummaryBox) multiItemSummaryBox.style.display = 'block';
      if (btnAddRowText) btnAddRowText.textContent = 'Tambah Pos Lagi';

      extraCards.forEach((card, idx) => {
        const badge = card.querySelector('.extra-item-badge');
        if (badge) badge.textContent = `Pos ${idx + 2}`;
      });
    } else {
      if (pos1Badge) pos1Badge.style.display = 'none';
      if (multiItemCountBadge) multiItemCountBadge.style.display = 'none';
      if (multiItemSummaryBox) multiItemSummaryBox.style.display = 'none';
      if (btnAddRowText) btnAddRowText.textContent = 'Tambah Pos Rincian';
    }

    let sum = parseFloat(pos1AmountInput ? pos1AmountInput.value : 0) || 0;
    extraCards.forEach(card => {
      const amtInput = card.querySelector('.extra-amount-input');
      if (amtInput) {
        sum += parseFloat(amtInput.value) || 0;
      }
    });

    if (multiItemCount) multiItemCount.textContent = totalCount;
    if (multiItemTotalDisplay) {
      multiItemTotalDisplay.textContent = 'Rp ' + sum.toLocaleString('id-ID');
      if (type === 'income') {
        multiItemTotalDisplay.style.color = 'var(--color-income, #10b981)';
      } else if (type === 'transfer') {
        multiItemTotalDisplay.style.color = 'var(--color-primary, #3b82f6)';
      } else {
        multiItemTotalDisplay.style.color = 'var(--color-expense, #ef4444)';
      }
    }
    if (multiItemTerbilangDisplay) {
      multiItemTerbilangDisplay.textContent = '# ' + getSpelledRupiah(sum) + ' #';
    }
  }

  function addExtraItemRow(catId = null, amount = '', desc = '') {
    if (!extraItemsContainer) return;
    const type = getCurrentType();
    const currentCount = extraItemsContainer.querySelectorAll('.extra-item-card').length;
    const posNum = currentCount + 2;

    const card = document.createElement('div');
    card.className = 'extra-item-card';
    card.style.cssText = 'padding: 12px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08)); border-radius: 8px; margin-bottom: 6px;';
    
    const isTransfer = (type === 'transfer');
    card.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <span class="extra-item-badge" style="font-size: 0.78rem; font-weight: 700; color: var(--color-primary, #3b82f6);">Pos ${posNum}</span>
        <button type="button" class="btn-remove-extra-item" style="background: none; border: none; color: #ef4444; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 4px;" title="Hapus Pos Ini">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
          Hapus Pos
        </button>
      </div>
      <div class="form-row">
        <div class="form-group" style="margin-bottom: 10px;">
          <label class="form-label">Akun / Kategori</label>
          <div class="cat-search-wrapper extra-category-wrapper" style="${isTransfer ? 'display:none;' : ''}">
            <div class="cat-search-input-box">
              <span class="cat-selected-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted);"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/></svg>
              </span>
              <input type="text" class="form-input cat-search-input extra-category-search" 
                placeholder="Ketik kode atau nama akun..." autocomplete="off" ${isTransfer ? '' : 'required'} />
              <button type="button" class="cat-search-clear" title="Hapus pilihan" aria-label="Hapus pilihan" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
              </button>
            </div>
            <input type="hidden" class="cat-category-id extra-category-id" value="${isTransfer ? transferCatId : (catId || '')}" />
            <div class="cat-dropdown-menu"></div>
          </div>
          <div class="extra-transfer-badge" style="${isTransfer ? 'display:flex;' : 'display:none;'} align-items: center; gap: 6px; padding: 10px 14px; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 8px; color: var(--color-primary, #3b82f6); font-size: 0.82rem; font-weight: 700;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/></svg>
            [1-1100] Kas dan Setara Kas
          </div>
        </div>
        <div class="form-group" style="margin-bottom: 10px;">
          <label class="form-label">Jumlah (Rp)</label>
          <input type="number" class="form-input extra-amount-input" placeholder="Contoh: 100000" min="0.01" step="0.01" value="${amount}" required />
        </div>
      </div>
      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Uraian / Deskripsi Pos</label>
        <input type="text" class="form-input extra-desc-input" placeholder="Contoh: Pencairan Kas / Pengiriman..." value="${escapeHtml(desc)}" />
      </div>
    `;

    extraItemsContainer.appendChild(card);

    const extraWrapper = card.querySelector('.extra-category-wrapper');
    const catSearch = initCategorySearch(extraWrapper);
    card._catSearch = catSearch;

    if (isTransfer) {
      catSearch.setSelectedCategoryById(transferCatId);
    } else if (catId) {
      catSearch.setSelectedCategoryById(catId);
    } else {
      catSearch.setDefaultCategory(type);
    }

    const removeBtn = card.querySelector('.btn-remove-extra-item');
    if (removeBtn) {
      removeBtn.addEventListener('click', () => {
        card.remove();
        updateMultiItemState();
      });
    }

    const amtInput = card.querySelector('.extra-amount-input');
    if (amtInput) {
      amtInput.addEventListener('input', updateMultiItemState);
    }

    updateMultiItemState();

    if (isTransfer) {
      const amtEl = card.querySelector('.extra-amount-input');
      if (amtEl) amtEl.focus();
    } else {
      if (catSearch && catSearch.searchInput) catSearch.searchInput.focus();
    }
  }

  function resetExtraRows() {
    if (extraItemsContainer) {
      extraItemsContainer.innerHTML = '';
    }
    if (txForm) {
      txForm.querySelectorAll('input[data-injected-item="1"]').forEach(el => el.remove());
    }
    updateMultiItemState();
  }

  if (btnAddItemRow) {
    btnAddItemRow.addEventListener('click', () => {
      addExtraItemRow();
    });
  }

  if (pos1AmountInput) {
    pos1AmountInput.addEventListener('input', updateMultiItemState);
  }

  window.resetExtraModalItems = resetExtraRows;
  window.addExtraModalItem = addExtraItemRow;
  window.setMultiItemHeaderVisible = function(visible) {
    if (multiItemHeaderBar) {
      multiItemHeaderBar.style.display = visible ? 'flex' : 'none';
    }
  };

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
        if (!pos1CatSearch || !pos1CatSearch.hiddenInput.value) {
          e.preventDefault();
          alert('Silakan pilih salah satu Akun/Kategori (COA) untuk Pos 1 dari daftar pilihan.');
          if (pos1CatSearch && pos1CatSearch.searchInput) {
            pos1CatSearch.searchInput.focus();
            pos1CatSearch.openDropdown();
          }
          return;
        }
      }

      const extraCards = extraItemsContainer ? extraItemsContainer.querySelectorAll('.extra-item-card') : [];
      if (extraCards.length > 0) {
        const pos1Amount = parseFloat(pos1AmountInput ? pos1AmountInput.value : 0) || 0;
        if (pos1Amount <= 0) {
          e.preventDefault();
          alert('Jumlah (Rp) untuk Pos 1 harus lebih besar dari 0.');
          if (pos1AmountInput) pos1AmountInput.focus();
          return;
        }

        for (let i = 0; i < extraCards.length; i++) {
          const card = extraCards[i];
          const catSearch = card._catSearch;
          const amtInput = card.querySelector('.extra-amount-input');
          const posNum = i + 2;

          if (type !== 'transfer' && (!catSearch || !catSearch.hiddenInput.value)) {
            e.preventDefault();
            alert(`Silakan pilih Akun/Kategori untuk Pos ${posNum}.`);
            if (catSearch && catSearch.searchInput) {
              catSearch.searchInput.focus();
              catSearch.openDropdown();
            }
            return;
          }

          const amt = parseFloat(amtInput ? amtInput.value : 0) || 0;
          if (amt <= 0) {
            e.preventDefault();
            alert(`Jumlah (Rp) untuk Pos ${posNum} harus lebih besar dari 0.`);
            if (amtInput) amtInput.focus();
            return;
          }
        }

        // Injected hidden inputs
        txForm.querySelectorAll('input[data-injected-item="1"]').forEach(el => el.remove());

        function appendHidden(name, value) {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = name;
          inp.value = value;
          inp.dataset.injectedItem = '1';
          txForm.appendChild(inp);
        }

        const cat1 = (type === 'transfer') ? transferCatId : pos1CatSearch.hiddenInput.value;
        const desc1 = document.getElementById('modal-desc') ? document.getElementById('modal-desc').value : '';
        appendHidden('items[0][category_id]', cat1);
        appendHidden('items[0][amount]', pos1Amount);
        appendHidden('items[0][description]', desc1);

        extraCards.forEach((card, idx) => {
          const itemIdx = idx + 1;
          const cVal = (type === 'transfer') 
            ? transferCatId 
            : (card._catSearch ? card._catSearch.hiddenInput.value : card.querySelector('.extra-category-id').value);
          const aVal = card.querySelector('.extra-amount-input').value;
          const dVal = card.querySelector('.extra-desc-input') ? card.querySelector('.extra-desc-input').value : '';
          appendHidden(`items[${itemIdx}][category_id]`, cVal);
          appendHidden(`items[${itemIdx}][amount]`, aVal);
          appendHidden(`items[${itemIdx}][description]`, dVal);
        });
      } else {
        txForm.querySelectorAll('input[data-injected-item="1"]').forEach(el => el.remove());
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
      if (toAccountSelect) toAccountSelect.disabled = false;
      if (accountLabel) accountLabel.textContent = 'Dari Rekening (Asal Dana)';
      
      if (modalCategoryWrapper) modalCategoryWrapper.style.display = 'none';
      if (pos1CatSearch) {
        pos1CatSearch.searchInput.removeAttribute('required');
        pos1CatSearch.hiddenInput.value = transferCatId;
      }
      if (transferBadge) transferBadge.style.display = 'block';
      if (saveBtn) saveBtn.textContent = 'Proses Transfer Dana';
      if (modalVoucher) modalVoucher.placeholder = 'Contoh: No. Cek Mandiri / Bukti Transfer';
      if (window.setMultiItemHeaderVisible) window.setMultiItemHeaderVisible(true);

      // Update options in any open extra items for transfer mode
      if (extraItemsContainer) {
        extraItemsContainer.querySelectorAll('.extra-item-card').forEach(card => {
          const wrapper = card.querySelector('.extra-category-wrapper');
          const badge = card.querySelector('.extra-transfer-badge');
          const searchInput = card.querySelector('.extra-category-search');
          const hiddenInput = card.querySelector('.extra-category-id');
          if (wrapper) wrapper.style.display = 'none';
          if (searchInput) searchInput.removeAttribute('required');
          if (hiddenInput) hiddenInput.value = transferCatId;
          if (badge) badge.style.display = 'flex';
        });
      }
      
      // Auto adjust to_account so it is not the same as source account
      if (accountSelect && toAccountSelect) {
        if (toAccountSelect.value === accountSelect.value) {
          const opt = Array.from(toAccountSelect.options).find(o => o.value !== accountSelect.value);
          if (opt) toAccountSelect.value = opt.value;
        }
      }
      closeDropdown();
      updateMultiItemState();
    } else {
      if (toAccountGroup) toAccountGroup.style.display = 'none';
      if (toAccountSelect) toAccountSelect.disabled = true;
      if (accountLabel) accountLabel.textContent = 'Sumber Dana';
      
      if (modalCategoryWrapper) modalCategoryWrapper.style.display = 'block';
      if (transferBadge) transferBadge.style.display = 'none';
      if (pos1CatSearch) {
        pos1CatSearch.searchInput.setAttribute('required', 'required');
        const currentId = pos1CatSearch.hiddenInput.value;
        const currentCat = modalCategories.find(c => c.id == currentId);
        if (!currentCat || currentCat.type !== type) {
          pos1CatSearch.setDefaultCategory(type);
        }
      }
      if (saveBtn) saveBtn.textContent = 'Tambah Transaksi';
      if (modalVoucher) modalVoucher.placeholder = 'Contoh: KT. 01 26.001 (Opsional)';
      if (window.setMultiItemHeaderVisible) window.setMultiItemHeaderVisible(true);

      // Update options in any open extra items for income/expense
      if (extraItemsContainer) {
        extraItemsContainer.querySelectorAll('.extra-item-card').forEach(card => {
          const wrapper = card.querySelector('.extra-category-wrapper');
          const badge = card.querySelector('.extra-transfer-badge');
          const searchInput = card.querySelector('.extra-category-search');
          if (wrapper) wrapper.style.display = 'block';
          if (searchInput) searchInput.setAttribute('required', 'required');
          if (badge) badge.style.display = 'none';
          if (card._catSearch) {
            const currentId = card._catSearch.hiddenInput.value;
            const currentCat = modalCategories.find(c => c.id == currentId);
            if (!currentCat || currentCat.type !== type) {
              card._catSearch.setDefaultCategory(type);
            }
          }
        });
      }
      updateMultiItemState();
    }
  };

  // Initial category setup for Pos 1
  if (pos1CatSearch) {
    pos1CatSearch.setDefaultCategory(getCurrentType());
  }

  // Handle click on voucher prefix chips
  document.querySelectorAll('.btn-prefix-chip').forEach(btn => {
    btn.addEventListener('click', () => {
      const prefix = btn.dataset.prefix;
      const targetType = btn.dataset.type;
      
      if (modalVoucher) {
        const current = modalVoucher.value.trim();
        const prefixes = ['KT.', 'TTN.', 'TT.', 'PP.', 'PK.', 'P.'];
        let rest = current;
        for (const p of prefixes) {
          if (rest.toUpperCase().startsWith(p)) {
            rest = rest.substring(p.length).trim();
            break;
          }
        }
        modalVoucher.value = prefix + (rest ? ' ' + rest : ' ');
        modalVoucher.focus();
        modalVoucher.setSelectionRange(modalVoucher.value.length, modalVoucher.value.length);
      }

      if (targetType) {
        const typeBtn = document.getElementById(targetType === 'income' ? 'btn-select-income' : 'btn-select-expense');
        if (typeBtn && !typeBtn.classList.contains('active')) {
          typeBtn.click();
        }
      }
    });
  });
});
</script>
