<?php

use Livewire\Volt\Component;
use App\Models\Bundle;
use App\Models\Kategori;

new #[\Livewire\Attributes\Layout('layouts.app')] class extends Component {
    public Bundle $bundle;

    public function title(): string
    {
        return $this->bundle->nama;
    }

    // Form for new kategori
    public bool $showKategoriForm = false;
    public string $kategoriNama = '';
    public string $kategoriKode = '';
    public string $kategoriDeskripsi = '';

    // Form for editing bundle
    public bool $editMode = false;
    public string $editNama = '';
    public string $editKode = '';
    public string $editDeskripsi = '';
    public string $editTahun = '';

    public function mount(Bundle $bundle)
    {
        $this->bundle = $bundle->load(['kategoris' => function ($q) {
            $q->withCount('dokumens')->orderBy('urutan');
        }, 'creator']);
    }

    public function toggleEditMode()
    {
        $this->editMode = !$this->editMode;
        if ($this->editMode) {
            $this->editNama = $this->bundle->nama;
            $this->editKode = $this->bundle->kode;
            $this->editDeskripsi = $this->bundle->deskripsi ?? '';
            $this->editTahun = $this->bundle->tahun;
        }
    }

    public function updateBundle()
    {
        if (!auth()->user()->canManage()) {
            session()->flash('error', 'Anda tidak memiliki akses.');
            return;
        }

        $this->validate([
            'editNama' => 'required|string|max:255',
            'editKode' => 'required|string|max:50',
            'editDeskripsi' => 'nullable|string|max:1000',
            'editTahun' => 'required|integer|min:1900|max:' . (date('Y') + 5),
        ]);

        $this->bundle->update([
            'nama' => $this->editNama,
            'kode' => strtoupper($this->editKode),
            'deskripsi' => $this->editDeskripsi ?: null,
            'tahun' => $this->editTahun,
        ]);

        $this->editMode = false;
        session()->flash('success', "Data bundle '{$this->editNama}' berhasil diperbarui!");
    }

    public function toggleKategoriForm()
    {
        $this->showKategoriForm = !$this->showKategoriForm;
        $this->resetKategoriForm();
    }

    public function simpanKategori()
    {
        if (!auth()->user()->canManage()) {
            session()->flash('error', 'Anda tidak memiliki akses.');
            return;
        }

        $this->validate([
            'kategoriNama' => 'required|string|max:255',
            'kategoriKode' => 'nullable|string|max:50',
            'kategoriDeskripsi' => 'nullable|string|max:500',
        ]);

        $maxUrutan = $this->bundle->kategoris()->max('urutan') ?? 0;

        $this->bundle->kategoris()->create([
            'nama' => $this->kategoriNama,
            'kode' => $this->kategoriKode ? strtoupper($this->kategoriKode) : null,
            'deskripsi' => $this->kategoriDeskripsi,
            'urutan' => $maxUrutan + 1,
        ]);

        $this->resetKategoriForm();
        $this->showKategoriForm = false;
        $this->bundle->load(['kategoris' => function ($q) {
            $q->withCount('dokumens')->orderBy('urutan');
        }]);

        session()->flash('success', "Kategori '{$this->kategoriNama}' berhasil ditambahkan!");
    }

    public function hapusKategori(int $id)
    {
        if (!auth()->user()->isAdmin()) {
            session()->flash('error', 'Hanya admin yang dapat menghapus kategori.');
            return;
        }

        $kategori = Kategori::findOrFail($id);
        $nama = $kategori->nama;

        // Hapus folder fisik kategori ini
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory("arsip/{$this->bundle->id}/{$kategori->id}");

        $kategori->delete();

        $this->bundle->load(['kategoris' => function ($q) {
            $q->withCount('dokumens')->orderBy('urutan');
        }]);

        session()->flash('success', "Kategori '{$nama}' berhasil dihapus.");
    }

    private function resetKategoriForm()
    {
        $this->kategoriNama = '';
        $this->kategoriKode = '';
        $this->kategoriDeskripsi = '';
    }
}; ?>
<div>
<div class="breadcrumb">
        <a href="/dashboard">Dashboard</a>
        <span class="separator">/</span>
        <a href="/bundles">Bundle Arsip</a>
        <span class="separator">/</span>
        <span class="current">{{ $bundle->nama }}</span>
    </div>

    <!-- Bundle Header -->
    @if($editMode)
        <div class="card mb-6" style="border-color: var(--accent); animation: slideDown 0.3s ease;">
            <h3 style="font-weight: 700; margin-bottom: 20px;">✏️ Edit Bundle</h3>
            <form wire:submit="updateBundle">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Bundle *</label>
                        <input type="text" wire:model="editNama" class="form-input" required>
                        @error('editNama') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode Bundle *</label>
                        <input type="text" wire:model="editKode" class="form-input" style="text-transform: uppercase;" required>
                        @error('editKode') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tahun *</label>
                    <input type="number" wire:model="editTahun" class="form-input" required>
                    @error('editTahun') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea wire:model="editDeskripsi" class="form-textarea" rows="3"></textarea>
                    @error('editDeskripsi') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <button type="button" wire:click="toggleEditMode" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    @else
        <div class="card mb-6">
            <div class="flex items-center justify-between" style="flex-wrap: wrap; gap: 16px;">
                <div>
                    <div class="flex items-center gap-3">
                        <span style="font-size: 2rem;">📦</span>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 style="font-size: 1.5rem; font-weight: 800;">{{ $bundle->nama }}</h1>
                                @if(auth()->user()->canManage())
                                    <button wire:click="toggleEditMode" class="btn btn-sm btn-secondary" style="padding: 4px 8px; font-size: 0.75rem;" title="Edit Bundle">
                                        ✏️ Edit
                                    </button>
                                @endif
                            </div>
                            <div style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">{{ $bundle->kode }}</div>
                        </div>
                    </div>
                    @if($bundle->deskripsi)
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 12px; line-height: 1.6;">{{ $bundle->deskripsi }}</p>
                    @endif
                </div>
                <div class="flex gap-3" style="flex-wrap: wrap;">
                    <div class="badge badge-accent">📅 {{ $bundle->tahun }}</div>
                    <div class="badge badge-info">📂 {{ $bundle->kategoris->count() }} kategori</div>
                    <div class="badge badge-success">👤 {{ $bundle->creator?->name }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Kategori Section -->
    <div class="flex items-center justify-between mb-4">
        <h2 style="font-size: 1.25rem; font-weight: 700;">Kategori Dokumen</h2>
        @if(auth()->user()->canManage())
            <button wire:click="toggleKategoriForm" class="btn btn-sm btn-primary">
                {{ $showKategoriForm ? '✕ Tutup' : '➕ Tambah Kategori' }}
            </button>
        @endif
    </div>

    <!-- Add Kategori Form -->
    @if($showKategoriForm)
        <div class="card mb-4" style="border-color: var(--accent); animation: slideDown 0.3s ease;">
            <h3 style="font-weight: 700; margin-bottom: 16px;">Tambah Kategori Baru</h3>
            <form wire:submit="simpanKategori">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Kategori *</label>
                        <input type="text" wire:model="kategoriNama" class="form-input"
                               placeholder="contoh: SDA, Kontrak, Kwitansi" autofocus>
                        @error('kategoriNama') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode</label>
                        <input type="text" wire:model="kategoriKode" class="form-input"
                               placeholder="contoh: KTG-SDA" style="text-transform: uppercase;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea wire:model="kategoriDeskripsi" class="form-textarea" rows="2"
                              placeholder="Deskripsi singkat kategori..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary btn-sm">💾 Simpan</button>
                    <button type="button" wire:click="toggleKategoriForm" class="btn btn-secondary btn-sm">Batal</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Kategori List -->
    @if($bundle->kategoris->count() > 0)
        @foreach($bundle->kategoris as $kategori)
            <div class="kategori-item">
                <a href="/bundles/{{ $bundle->id }}/kategori/{{ $kategori->id }}" 
                   style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 14px; flex: 1;">
                    <div class="kategori-icon">📂</div>
                    <div>
                        <div class="kategori-name">{{ $kategori->nama }}</div>
                        @if($kategori->kode)
                            <div class="kategori-desc">{{ $kategori->kode }}</div>
                        @endif
                        @if($kategori->deskripsi)
                            <div class="kategori-desc">{{ Str::limit($kategori->deskripsi, 60) }}</div>
                        @endif
                    </div>
                </a>
                <div class="flex items-center gap-3">
                    <span class="badge badge-info">{{ $kategori->dokumens_count }} dokumen</span>
                    @if(auth()->user()->isAdmin())
                        <button wire:click="hapusKategori({{ $kategori->id }})"
                                wire:confirm="Yakin ingin menghapus kategori '{{ $kategori->nama }}' beserta semua dokumennya?"
                                class="btn btn-icon btn-sm" style="background: none; border: none; cursor: pointer; opacity: 0.5;"
                                onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5">
                            🗑️
                        </button>
                    @endif
                    <span style="color: var(--text-muted);">→</span>
                </div>
            </div>
        @endforeach
    @else
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">📂</div>
                <div class="empty-state-text">Belum ada kategori dalam bundle ini</div>
                <div class="empty-state-hint">Tambahkan kategori seperti SDA, Kontrak, Kwitansi, dll.</div>
            </div>
        </div>
    @endif

    <!-- Back Button -->
    <div style="margin-top: 24px;">
        <a href="/bundles" class="btn btn-secondary">
            ← Kembali ke Daftar Bundle
        </a>
    </div>

</div>
