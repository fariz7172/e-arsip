<?php

use Livewire\Volt\Component;
use App\Models\Bundle;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Bundle Arsip')] class extends Component {
    public string $search = '';
    public string $filterTahun = '';

    // Edit state
    public ?int $editBundleId = null;
    public string $editNama = '';
    public string $editKode = '';
    public string $editDeskripsi = '';
    public string $editTahun = '';

    public function with(): array
    {
        $query = Bundle::withCount(['kategoris', 'dokumens'])
            ->with(['creator', 'kategoris.dokumens.fileAttachments'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                  ->orWhere('kode', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterTahun) {
            $query->where('tahun', $this->filterTahun);
        }

        $bundles = $query->get();

        // Get unique years for filter
        $years = Bundle::selectRaw('DISTINCT tahun')->orderByDesc('tahun')->pluck('tahun');

        return compact('bundles', 'years');
    }

    public function deleteBundle(int $id)
    {
        $bundle = Bundle::findOrFail($id);

        if (!auth()->user()->isAdmin()) {
            session()->flash('error', 'Hanya admin yang dapat menghapus bundle.');
            return;
        }

        // Hapus seluruh folder fisik arsip milik bundle ini
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory("arsip/{$bundle->id}");

        $bundle->forceDelete();
        session()->flash('success', "Bundle '{$bundle->nama}' beserta seluruh file di dalamnya berhasil dihapus.");
    }

    public function editBundle(int $id)
    {
        if (!auth()->user()->canManage()) return;

        $bundle = Bundle::findOrFail($id);
        $this->editBundleId = $bundle->id;
        $this->editNama = $bundle->nama;
        $this->editKode = $bundle->kode;
        $this->editDeskripsi = $bundle->deskripsi ?? '';
        $this->editTahun = $bundle->tahun;
    }

    public function cancelEdit()
    {
        $this->editBundleId = null;
    }

    public function updateBundle()
    {
        if (!auth()->user()->canManage() || !$this->editBundleId) return;

        $this->validate([
            'editNama' => 'required|string|max:255',
            'editKode' => 'required|string|max:50',
            'editDeskripsi' => 'nullable|string|max:1000',
            'editTahun' => 'required|integer|min:1900|max:' . (date('Y') + 5),
        ]);

        $bundle = Bundle::findOrFail($this->editBundleId);
        $bundle->update([
            'nama' => $this->editNama,
            'kode' => strtoupper($this->editKode),
            'deskripsi' => $this->editDeskripsi ?: null,
            'tahun' => $this->editTahun,
        ]);

        $this->editBundleId = null;
        session()->flash('success', "Bundle '{$this->editNama}' berhasil diperbarui.");
    }
}; ?>
<style>
    /* ===== Bundle Index Page Styles ===== */
    .bundle-index-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 28px;
    }

    .bundle-index-title h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.03em;
        line-height: 1.2;
    }

    .bundle-index-title p {
        color: var(--text-muted);
        font-size: 0.875rem;
        margin-top: 4px;
    }

    .filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-bar .search-wrap {
        flex: 1;
        min-width: 240px;
    }

    .filter-select {
        padding: 10px 14px;
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        font-size: 0.875rem;
        font-family: inherit;
        outline: none;
        min-width: 150px;
        transition: var(--transition-base);
        cursor: pointer;
    }

    .filter-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }

    /* Bundle Grid Card */
    .bundle-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        align-items: flex-start;
    }

    @media (max-width: 1200px) {
        .bundle-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .bundle-grid { grid-template-columns: 1fr; }
    }

    .bundle-card-wrap {
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: var(--transition-base);
        box-shadow: var(--shadow-xs);
        position: relative;
    }

    .bundle-card-wrap:hover {
        border-color: rgba(47,47,228,0.3);
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    .bundle-card-top-stripe {
        height: 3px;
        background: linear-gradient(90deg, #2F2FE4, #162E93);
    }

    .bundle-card-body {
        padding: 20px;
    }

    .bundle-card-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .bundle-card-actions {
        display: flex;
        gap: 4px;
        flex-shrink: 0;
    }

    .bc-action-btn {
        width: 30px;
        height: 30px;
        border-radius: var(--radius-sm);
        border: 1.5px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-fast);
        text-decoration: none;
        font-family: inherit;
    }

    .bc-action-btn svg { width: 13px; height: 13px; }

    .bc-action-btn:hover {
        background: var(--primary-light);
        border-color: var(--primary);
        color: var(--primary);
    }

    .bc-action-btn.danger:hover {
        background: var(--danger-bg);
        border-color: var(--danger);
        color: var(--danger);
    }

    .bundle-icon-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .bundle-card-icon-box {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .bundle-card-icon-box svg { width: 20px; height: 20px; }

    .bc-title { font-weight: 700; font-size: 0.95rem; color: var(--text-primary); line-height: 1.3; }
    .bc-code {
        display: inline-block;
        font-size: 0.68rem;
        color: var(--primary);
        font-family: 'Courier New', monospace;
        font-weight: 700;
        background: var(--primary-light);
        padding: 2px 7px;
        border-radius: var(--radius-xs);
        margin-top: 2px;
        letter-spacing: 0.05em;
    }

    .bc-desc {
        font-size: 0.78rem;
        color: var(--text-muted);
        line-height: 1.5;
        margin-top: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bc-stats-row {
        display: flex;
        gap: 12px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--border-color);
        flex-wrap: wrap;
    }

    .bc-stat {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .bc-stat svg { width: 12px; height: 12px; }
    .bc-stat strong { color: var(--text-primary); font-weight: 700; }

    .bc-qr-wrap {
        padding: 4px;
        background: white;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        display: inline-block;
        flex-shrink: 0;
    }

    /* Bundle card footer */
    .bundle-card-footer {
        padding: 0 20px 16px;
        display: flex;
        gap: 8px;
        flex-direction: column;
    }

    .bc-view-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px;
        background: var(--primary-light);
        border: 1.5px solid rgba(47,47,228,0.2);
        border-radius: var(--radius-md);
        color: var(--primary);
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        transition: var(--transition-base);
        font-family: inherit;
    }

    .bc-view-btn svg { width: 14px; height: 14px; }

    .bc-view-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: var(--shadow-primary);
    }

    /* Bundle content accordion */
    .bc-accordion {
        border-top: 1px solid var(--border-color);
        margin-top: 4px;
    }

    .bc-accordion summary {
        list-style: none;
        cursor: pointer;
        padding: 10px 20px;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 6px;
        transition: var(--transition-fast);
        outline: none;
        user-select: none;
    }

    .bc-accordion summary::-webkit-details-marker { display: none; }
    .bc-accordion summary svg { width: 13px; height: 13px; transition: transform 0.2s; }
    details[open] .bc-accordion summary svg.chevron { transform: rotate(180deg); }

    .bc-accordion summary:hover { background: var(--primary-lighter); }

    .bc-accordion-content {
        padding: 0 20px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-height: 240px;
        overflow-y: auto;
    }

    .bc-kategori-row {
        background: var(--bg-surface);
        padding: 8px 12px;
        border-radius: var(--radius-sm);
        border-left: 3px solid var(--primary);
    }

    .bc-kategori-name {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .bc-kategori-name svg { width: 13px; height: 13px; color: var(--primary); }

    .bc-dokumen-list {
        margin: 5px 0 0 16px;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .bc-dokumen-item {
        font-size: 0.75rem;
        color: var(--text-secondary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 2px 0;
        transition: color 0.15s;
    }

    .bc-dokumen-item svg { width: 11px; height: 11px; flex-shrink: 0; }
    .bc-dokumen-item:hover { color: var(--primary); }
    .bc-file-count { font-size: 0.68rem; color: var(--text-muted); }

    /* Edit form inside card */
    .bc-edit-form {
        padding: 20px;
    }

    .bc-edit-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .bc-edit-title svg { width: 15px; height: 15px; color: var(--primary); }
</style>

<div>
    <!-- Page Header -->
    <div class="bundle-index-header">
        <div class="bundle-index-title">
            <h1>Bundle Arsip</h1>
            <p>Kelola semua bundle arsip digital Anda</p>
        </div>
        @if(auth()->user()->canManage())
            <a href="/bundles/create" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                Buat Bundle
            </a>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="search-wrap">
            <div class="search-container" style="margin-bottom:0;">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       class="search-input" placeholder="Cari nama atau kode bundle...">
            </div>
        </div>
        <select wire:model.live="filterTahun" class="filter-select">
            <option value="">Semua Tahun</option>
            @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <!-- Bundle Grid -->
    @if($bundles->count() > 0)
        <div class="bundle-grid">
            @foreach($bundles as $bundle)
                <div class="bundle-card-wrap">
                    <div class="bundle-card-top-stripe"></div>

                    @if($editBundleId === $bundle->id)
                        {{-- === EDIT FORM === --}}
                        <div class="bc-edit-form">
                            <div class="bc-edit-title">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Bundle
                            </div>
                            <form wire:submit="updateBundle">
                                <div class="form-group mb-2">
                                    <input type="text" wire:model="editNama" class="form-input" placeholder="Nama Bundle" required>
                                    @error('editNama') <div class="form-error">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-row mb-2">
                                    <div class="form-group mb-0">
                                        <input type="text" wire:model="editKode" class="form-input" placeholder="Kode" style="text-transform: uppercase;" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <input type="number" wire:model="editTahun" class="form-input" placeholder="Tahun" required>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <textarea wire:model="editDeskripsi" class="form-textarea" rows="2" placeholder="Deskripsi (opsional)"></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm" style="flex:1; justify-content:center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Simpan
                                    </button>
                                    <button type="button" wire:click="cancelEdit" class="btn btn-secondary btn-sm" style="flex:1; justify-content:center;">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        {{-- === CARD VIEW === --}}
                        <div class="bundle-card-body">
                            <div class="bundle-card-header-row">
                                <a href="/bundles/{{ $bundle->id }}" style="text-decoration:none; flex:1; min-width:0;">
                                    <div class="bundle-icon-title">
                                        <div class="bundle-card-icon-box">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        </div>
                                        <div>
                                            <div class="bc-title">{{ $bundle->nama }}</div>
                                            <span class="bc-code">{{ $bundle->kode }}</span>
                                        </div>
                                    </div>
                                </a>

                                <!-- Action Buttons (top right) -->
                                <div class="bundle-card-actions">
                                    <a href="/bundles/{{ $bundle->id }}/print-label" target="_blank"
                                       class="bc-action-btn" title="Cetak Label">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    </a>
                                    @if(auth()->user()->canManage())
                                        <button wire:click="editBundle({{ $bundle->id }})" class="bc-action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                    @if(auth()->user()->isAdmin())
                                        <button wire:click="deleteBundle({{ $bundle->id }})"
                                                wire:confirm="Yakin ingin menghapus bundle '{{ $bundle->nama }}'?"
                                                class="bc-action-btn danger" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Description -->
                            @if($bundle->deskripsi)
                                <p class="bc-desc">{{ $bundle->deskripsi }}</p>
                            @endif

                            <!-- Stats row + QR -->
                            <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                                <div class="bc-stats-row" style="flex:1; margin-top:12px; padding-top:12px;">
                                    <div class="bc-stat">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        <strong>{{ $bundle->kategoris_count }}</strong> kategori
                                    </div>
                                    <div class="bc-stat">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <strong>{{ $bundle->dokumens_count }}</strong> dokumen
                                    </div>
                                    <div class="bc-stat">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <strong>{{ $bundle->tahun }}</strong>
                                    </div>
                                </div>
                                <div class="bc-qr-wrap" style="margin-left:10px; margin-top:4px;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(url('/bundles/' . $bundle->id . '/detail')) }}"
                                         alt="QR" style="display:block; width:56px; height:56px; object-fit:contain;">
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="bundle-card-footer">
                            <a href="/bundles/{{ $bundle->id }}/detail" class="bc-view-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                Lihat Detail Lengkap
                            </a>
                        </div>

                        <!-- Accordion: Isi Bundle -->
                        <details class="bc-accordion">
                            <summary>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                Isi Bundle ({{ $bundle->kategoris->count() }} Kategori)
                                <svg class="chevron" style="margin-left:auto;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </summary>
                            <div class="bc-accordion-content">
                                @foreach($bundle->kategoris as $kategori)
                                    <div class="bc-kategori-row">
                                        <div class="bc-kategori-name">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                            {{ $kategori->nama }}
                                        </div>
                                        @if($kategori->dokumens->count() > 0)
                                            <div class="bc-dokumen-list">
                                                @foreach($kategori->dokumens as $dokumen)
                                                    <a href="/dokumen/{{ $dokumen->id }}" class="bc-dokumen-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                        {{ Str::limit($dokumen->judul, 38) }}
                                                        <span class="bc-file-count">({{ $dokumen->fileAttachments->count() }} file)</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px; margin-left:18px;">Belum ada dokumen</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="empty-state-text">
                    @if($search || $filterTahun)
                        Tidak ada bundle yang cocok dengan pencarian
                    @else
                        Belum ada bundle arsip
                    @endif
                </div>
                <div class="empty-state-hint">
                    @if($search || $filterTahun)
                        Coba ubah kata kunci atau filter tahun
                    @else
                        Mulai buat bundle untuk mengorganisir arsip Anda
                    @endif
                </div>
                @if(!$search && !$filterTahun && auth()->user()->canManage())
                    <a href="/bundles/create" class="btn btn-primary mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                        Buat Bundle Pertama
                    </a>
                @endif
            </div>
        </div>
    @endif

</div>


