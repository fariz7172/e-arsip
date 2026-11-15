<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\KodeKlasifikasi;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Master Data - Kode Klasifikasi')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public $perPage = 25;

    // Form Modal state
    public bool $showModal = false;
    public ?int $editingId = null;
    public $nomor = null;
    public $kode = '';
    public $deskripsi = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['editingId', 'nomor', 'kode', 'deskripsi']);
        $maxNomor = KodeKlasifikasi::max('nomor');
        $this->nomor = $maxNomor ? $maxNomor + 1 : 1;
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $item = KodeKlasifikasi::findOrFail($id);
        $this->editingId = $item->id;
        $this->nomor = $item->nomor;
        $this->kode = $item->kode;
        $this->deskripsi = $item->deskripsi ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nomor' => 'nullable|integer',
            'kode' => 'required|string|max:50',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        if ($this->editingId) {
            $item = KodeKlasifikasi::findOrFail($this->editingId);
            $item->update([
                'nomor' => $this->nomor,
                'kode' => trim($this->kode),
                'deskripsi' => trim($this->deskripsi) ?: null,
            ]);
            session()->flash('success', 'Kode Klasifikasi berhasil diperbarui.');
        } else {
            KodeKlasifikasi::create([
                'nomor' => $this->nomor,
                'kode' => trim($this->kode),
                'deskripsi' => trim($this->deskripsi) ?: null,
            ]);
            session()->flash('success', 'Kode Klasifikasi baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'nomor', 'kode', 'deskripsi']);
    }

    public function delete(int $id)
    {
        $item = KodeKlasifikasi::findOrFail($id);
        $item->delete();
        session()->flash('success', 'Kode Klasifikasi berhasil dihapus.');
    }

    public function with(): array
    {
        $query = KodeKlasifikasi::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('kode', 'like', '%' . $this->search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $this->search . '%');
            });
        }

        $totalCount = KodeKlasifikasi::count();

        return [
            'items' => $query->orderBy('nomor', 'asc')->orderBy('id', 'asc')->paginate($this->perPage),
            'totalCount' => $totalCount,
        ];
    }
}; ?>

<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">
                Master Data: Kode Klasifikasi
            </h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">
                Daftar kode klasifikasi arsip dan naskah dinas resmi.
            </p>
        </div>
        
        <div style="display:flex; gap: 8px; flex-wrap: wrap;">
            <!-- Download Template -->
            <a href="{{ route('kode-klasifikasi.template') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;" title="Download Template Excel">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Template
            </a>
            
            <!-- Export Excel -->
            <a href="{{ route('kode-klasifikasi.export', array_filter(['search' => $search])) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; color: #10b981;" title="Export ke Excel">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                Export
            </a>

            <!-- Import Excel -->
            <form action="{{ route('kode-klasifikasi.import') }}" method="POST" id="import_form" enctype="multipart/form-data" style="display:inline;">
                @csrf
                <input type="file" name="file" id="import_file" style="display: none;" onchange="if(confirm('Apakah Anda yakin ingin mengimpor data Kode Klasifikasi dari file ini?')) { document.getElementById('import-overlay').style.display='flex'; this.form.submit(); }" accept=".xlsx,.xls,.csv">
                <button type="button" onclick="document.getElementById('import_file').click()" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; color: #f59e0b;" title="Import dari Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="12" x2="12" y2="18"/><polyline points="9 15 12 12 15 15"/></svg>
                    Import
                </button>
            </form>

            <!-- Tambah Kode Baru -->
            <button type="button" wire:click="openCreateModal" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Kode
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="import-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.75); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; color: white;">
        <div style="width: 50px; height: 50px; border: 4px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; margin-bottom: 20px; animation: spin 1s linear infinite;"></div>
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px;">Sedang Mengimpor Data...</h2>
        <p style="font-size: 1rem; color: #d1d5db;">Mohon jangan tutup atau muat ulang halaman ini.</p>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </div>

    @if(session('success'))
        <div style="background: #d1fae5; color: #047857; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; border: 1px solid #fecaca;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Content Card -->
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding: 18px 20px;">
            <div style="display: flex; gap: 12px; align-items: center; width: 100%; max-width: 500px;">
                <select wire:model.live="perPage" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size:0.9rem; background: white; cursor: pointer;">
                    <option value="10">10 Data</option>
                    <option value="25">25 Data</option>
                    <option value="50">50 Data</option>
                    <option value="100">100 Data</option>
                    <option value="250">250 Data</option>
                    <option value="500">500 Data</option>
                </select>
                <div style="flex: 1; position: relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Kode atau Deskripsi..." style="padding: 8px 12px 8px 32px; border-radius: 6px; border: 1px solid var(--border-color); width: 100%; font-size:0.9rem; outline: none;">
                </div>
            </div>

            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Total Kode: <strong style="color: var(--text-primary);">{{ number_format($totalCount) }}</strong>
            </div>
        </div>

        @if($items->count() > 0)
            <div class="table-container" style="overflow-x: auto; width: 100%; border: 1px solid var(--border-color); border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 70px; text-align: center;">No</th>
                            <th style="width: 200px;">Kode Klasifikasi</th>
                            <th>Deskripsi / Penjelasan</th>
                            <th style="width: 120px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr wire:key="kode-{{ $item->id }}">
                                <td style="text-align: center; color: var(--text-muted); font-weight: 500;">
                                    {{ $item->nomor ?? $loop->iteration }}
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size: 0.95rem; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 3px 8px; border-radius: 4px; border: 1px solid #bae6fd;">
                                        {{ $item->kode }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem; color: var(--text-primary);">
                                        {{ $item->deskripsi ?: '-' }}
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <button type="button" wire:click="edit({{ $item->id }})" class="btn btn-sm btn-secondary" style="padding: 4px 8px;" title="Edit">
                                            ✏️
                                        </button>
                                        <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus kode '{{ $item->kode }}'?" class="btn btn-sm btn-secondary" style="padding: 4px 8px; color: var(--danger);" title="Hapus">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 16px; border-top: 1px solid var(--border-color);">
                {{ $items->links('vendor.pagination.custom', data: ['scrollTo' => false]) }}
            </div>
        @else
            <div class="empty-state" style="padding: 40px; text-align: center;">
                <div class="empty-state-icon" style="font-size: 2.5rem; margin-bottom: 8px;">🏷️</div>
                <div class="empty-state-text" style="font-weight: 600; font-size: 1.1rem; color: var(--text-primary); margin-bottom: 4px;">
                    Belum ada data Kode Klasifikasi
                </div>
                <div class="empty-state-hint" style="color: var(--text-muted); font-size: 0.85rem;">
                    Silakan klik tombol <strong>Import</strong> untuk memuat data dari file Excel atau klik <strong>Tambah Kode</strong>.
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Form (Tambah / Edit) -->
    @if($showModal)
        <div style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 999; display: flex; align-items: center; justify-content: center; padding: 16px;">
            <div style="background: white; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden;" @click.away="$set('showModal', false)">
                <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                        {{ $editingId ? 'Edit Kode Klasifikasi' : 'Tambah Kode Klasifikasi' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)" style="background: transparent; border: none; font-size: 1.25rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                
                <form wire:submit="save" style="padding: 24px;">
                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; display: block;">No. Urut (Opsional)</label>
                        <input type="number" wire:model="nomor" class="form-input" style="width: 100%;" placeholder="Contoh: 1">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; display: block;">Kode Klasifikasi *</label>
                        <input type="text" wire:model="kode" class="form-input" style="width: 100%; font-family: monospace;" placeholder="Contoh: BM.00.01 atau KR.01" required>
                        @error('kode') <span style="color: var(--danger); font-size: 0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label class="form-label" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; display: block;">Deskripsi / Penjelasan (Opsional)</label>
                        <textarea wire:model="deskripsi" class="form-input" style="width: 100%; min-height: 80px;" placeholder="Tuliskan keterangan mengenai kode klasifikasi ini"></textarea>
                        @error('deskripsi') <span style="color: var(--danger); font-size: 0.75rem;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" wire:click="$set('showModal', false)" class="btn btn-secondary">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Kode' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

