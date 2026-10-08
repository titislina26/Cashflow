@extends('layouts.app')

@section('title', 'Proyek (Jobs) — Cashflow Management')

@section('content')
<div class="page-content">
  <div class="page-header">
    <div>
      <h2>Proyek (Jobs)</h2>
      <p>Kelola proyek atau kegiatan pelacakan arus kas</p>
    </div>
    <div style="display: flex; gap: 8px;">
      <button class="btn btn-primary" id="btn-add-job" style="display: inline-flex; align-items: center; gap: 6px;">
        <x-lucide-plus style="width:16px; height:16px;" /> Proyek Baru
      </button>
    </div>
  </div>

  <!-- Jobs List Table -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
        <x-lucide-briefcase style="width:18px; height:18px; color:var(--accent-primary);" />
        Daftar Proyek / Kegiatan
      </h3>
    </div>
    <div class="table-container">
      <table class="data-table" id="jobs-table">
        <thead>
          <tr>
            <th style="width: 120px">Kode Proyek</th>
            <th>Nama Proyek</th>
            <th>Deskripsi</th>
            <th style="width: 120px">Status</th>
            <th style="width: 100px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @if($jobs->isEmpty())
            <tr>
              <td colspan="5" style="text-align:center; padding: 40px 0;">
                <div style="display:flex; justify-content:center; margin-bottom:10px;">
                  <x-lucide-briefcase style="width:40px; height:40px; color:var(--text-muted); stroke-width:1.5;" />
                </div>
                <div class="empty-state-title" style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 5px;">Belum ada data proyek</div>
                <div class="empty-state-desc" style="font-size: 0.85rem; color: var(--text-muted);">Tambahkan proyek baru untuk melacak transaksi kas secara spesifik.</div>
              </td>
            </tr>
          @else
            @foreach($jobs as $job)
              <tr>
                <td class="font-mono-num" style="font-weight: 700; color: var(--accent-primary)">{{ $job->code }}</td>
                <td style="font-weight: 600">{{ $job->name }}</td>
                <td>{{ $job->description ?? '—' }}</td>
                <td>
                  <span class="badge" style="background: {{ $job->status === 'active' ? 'var(--color-income-bg)' : 'rgba(255,255,255,0.05)' }}; color: {{ $job->status === 'active' ? 'var(--color-income)' : 'var(--text-muted)' }}; border: 1px solid {{ $job->status === 'active' ? 'var(--color-income-border)' : 'var(--border-primary)' }}; display:inline-flex; align-items:center; gap:4px;">
                    <x-dynamic-component :component="'lucide-' . ($job->status === 'active' ? 'check-circle' : 'circle-off')" style="width:12px; height:12px;" />
                    {{ $job->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td>
                  <div style="display:flex; gap:6px">
                    <button class="btn btn-secondary btn-sm btn-edit-job" 
                      data-id="{{ $job->id }}"
                      data-code="{{ $job->code }}"
                      data-name="{{ $job->name }}"
                      data-status="{{ $job->status }}"
                      data-description="{{ $job->description }}"
                      title="Edit Proyek"
                      style="display:inline-flex; align-items:center; justify-content:center; padding:5px 8px;">
                      <x-lucide-edit-3 style="width:13px; height:13px;" />
                    </button>
                    <form action="{{ route('jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Hapus proyek ini? Transaksi yang terkait akan dilepas hubungannya tetapi tidak dihapus.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" title="Hapus Proyek" style="display:inline-flex; align-items:center; justify-content:center; padding:5px 8px;">
                        <x-lucide-trash-2 style="width:13px; height:13px;" />
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add/Edit Job Modal Overlay -->
<div class="modal-overlay" id="job-modal-overlay">
  <div class="modal">
    <form action="{{ route('jobs.store') }}" method="POST" id="job-modal-form">
      @csrf
      <input type="hidden" name="_method" id="job-form-method" value="POST">
      
      <div class="modal-header">
        <h3 id="job-modal-title" style="display:flex; align-items:center; gap:8px;">
          <x-lucide-briefcase style="width:20px; height:20px; color:var(--accent-primary);" />
          Tambah Proyek Baru
        </h3>
        <button type="button" class="modal-close" id="btn-modal-close"><x-lucide-x style="width:18px; height:18px;" /></button>
      </div>
      
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group" style="flex:1">
            <label class="form-label">Kode Proyek</label>
            <input type="text" class="form-input font-mono-num" name="code" id="modal-job-code" 
              placeholder="Contoh: J01" required />
          </div>
          <div class="form-group" style="flex:2">
            <label class="form-label">Nama Proyek</label>
            <input type="text" class="form-input" name="name" id="modal-job-name" 
              placeholder="Contoh: Pembangunan Lab Komputer" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Status</label>
          <div class="type-selector">
            <input type="hidden" name="status" id="modal-job-status" value="active">
            <button type="button" class="type-selector-btn active" id="btn-select-active" data-status="active" style="display:inline-flex; align-items:center; justify-content:center; gap:6px;">
              <x-lucide-check-circle style="width:14px; height:14px;" /> Aktif
            </button>
            <button type="button" class="type-selector-btn" id="btn-select-inactive" data-status="inactive" style="display:inline-flex; align-items:center; justify-content:center; gap:6px;">
              <x-lucide-circle-off style="width:14px; height:14px;" /> Nonaktif
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Deskripsi (Opsional)</label>
          <textarea class="form-textarea" name="description" id="modal-job-desc" 
            placeholder="Keterangan atau rincian mengenai proyek ini..."></textarea>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="btn-modal-cancel">Batal</button>
        <button type="submit" class="btn btn-primary" id="btn-modal-save">Tambah Proyek</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalOverlay = document.getElementById('job-modal-overlay');
    const modalForm = document.getElementById('job-modal-form');
    const formMethod = document.getElementById('job-form-method');
    const modalTitle = document.getElementById('job-modal-title');
    const modalSaveBtn = document.getElementById('btn-modal-save');
    
    // Form Inputs
    const jobCodeInput = document.getElementById('modal-job-code');
    const jobNameInput = document.getElementById('modal-job-name');
    const jobStatusInput = document.getElementById('modal-job-status');
    const jobDescInput = document.getElementById('modal-job-desc');
    
    // Status Buttons
    const btnActive = document.getElementById('btn-select-active');
    const btnInactive = document.getElementById('btn-select-inactive');
    
    // Open Modal for Add
    document.getElementById('btn-add-job').addEventListener('click', function() {
        modalTitle.innerHTML = '<i data-lucide="briefcase" style="width:20px; height:20px; color:var(--accent-primary);"></i> Tambah Proyek Baru';
        modalForm.setAttribute('action', "{{ route('jobs.store') }}");
        formMethod.value = 'POST';
        modalSaveBtn.textContent = 'Tambah Proyek';
        
        // Reset inputs
        jobCodeInput.value = '';
        jobNameInput.value = '';
        jobDescInput.value = '';
        jobCodeInput.removeAttribute('readonly');
        
        setStatus('active');
        
        modalOverlay.classList.add('active');
        if (window.lucide) { lucide.createIcons(); }
        setTimeout(() => jobCodeInput.focus(), 100);
    });
    
    // Open Modal for Edit
    document.querySelectorAll('.btn-edit-job').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const code = this.dataset.code;
            const name = this.dataset.name;
            const status = this.dataset.status;
            const desc = this.dataset.description;
            
            modalTitle.innerHTML = '<i data-lucide="edit-3" style="width:20px; height:20px; color:var(--accent-primary);"></i> Edit Proyek';
            modalForm.setAttribute('action', `/jobs/${id}`);
            formMethod.value = 'PUT';
            modalSaveBtn.textContent = 'Simpan Perubahan';
            
            // Populate inputs
            jobCodeInput.value = code;
            jobNameInput.value = name;
            jobDescInput.value = desc || '';
            
            setStatus(status);
            
            modalOverlay.classList.add('active');
            if (window.lucide) { lucide.createIcons(); }
            setTimeout(() => jobNameInput.focus(), 100);
        });
    });
    
    // Close Modal
    function closeModal() {
        modalOverlay.classList.remove('active');
    }
    
    document.getElementById('btn-modal-close').addEventListener('click', closeModal);
    document.getElementById('btn-modal-cancel').addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) closeModal();
    });
    
    // Status selection
    function setStatus(status) {
        jobStatusInput.value = status;
        if (status === 'active') {
            btnActive.classList.add('active');
            btnInactive.classList.remove('active');
        } else {
            btnActive.classList.remove('active');
            btnInactive.classList.add('active');
        }
    }
    
    btnActive.addEventListener('click', () => setStatus('active'));
    btnInactive.addEventListener('click', () => setStatus('inactive'));
});
</script>
@endsection
