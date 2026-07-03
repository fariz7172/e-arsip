<?php

use Livewire\Volt\Component;
use App\Models\Dokumen;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Pencarian')] class extends Component {
    public string $search = '';
    public string $filterBundle = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    public function with(): array
    {
        $query = Dokumen::with(['kategori.bundle', 'fileAttachments', 'uploader']);
        $exactBundle = null;

        if ($this->search) {
            // Check for exact bundle code match (e.g. from barcode scanner)
            $exactBundle = \App\Models\Bundle::where('kode', $this->search)
                ->withCount(['kategoris', 'dokumens'])
                ->first();

            if ($exactBundle) {
                // Show all documents from this bundle
                $query->whereHas('kategori', function ($q) use ($exactBundle) {
                    $q->where('bundle_id', $exactBundle->id);
                });
            } else {
                // Normal search
                $query->where(function ($q) {
                    $q->where('judul', 'like', "%{$this->search}%")
                      ->orWhere('nomor_dokumen', 'like', "%{$this->search}%")
                      ->orWhere('keterangan', 'like', "%{$this->search}%")
                      ->orWhereHas('kategori', function ($kq) {
                          $kq->where('nama', 'like', "%{$this->search}%");
                      })
                      ->orWhereHas('kategori.bundle', function ($bq) {
                          $bq->where('nama', 'like', "%{$this->search}%")
                            ->orWhere('kode', 'like', "%{$this->search}%");
                      });
                });
            }
        }

        if ($this->filterBundle) {
            $query->whereHas('kategori', function ($q) {
                $q->where('bundle_id', $this->filterBundle);
            });
        }

        if ($this->filterDateFrom) {
            $query->where('tanggal_dokumen', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->where('tanggal_dokumen', '<=', $this->filterDateTo);
        }

        $results = $query->latest()->take(50)->get();
        $bundles = \App\Models\Bundle::orderBy('nama')->get();

        return compact('results', 'bundles', 'exactBundle');
    }
}; ?>
<div>
<div class="page-header">
        <h1>🔍 Pencarian Arsip</h1>
        <p>Cari dokumen berdasarkan judul, nomor, kategori, atau bundle</p>
    </div>

    <!-- Search Bar -->
    <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" wire:model.live.debounce.300ms="search" 
               class="search-input" placeholder="Ketik untuk mencari dokumen..." autofocus>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
        <div class="flex items-center gap-3" style="flex-wrap: wrap;">
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Filter:</div>
            <select wire:model.live="filterBundle" class="form-select" style="max-width: 250px;">
                <option value="">Semua Bundle</option>
                @foreach($bundles as $bundle)
                    <option value="{{ $bundle->id }}">{{ $bundle->nama }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="filterDateFrom" class="form-input" style="max-width: 180px;" placeholder="Dari tanggal">
            <input type="date" wire:model.live="filterDateTo" class="form-input" style="max-width: 180px;" placeholder="Sampai tanggal">
        </div>
    </div>

    <!-- Results -->
    @if($search || $filterBundle || $filterDateFrom || $filterDateTo)
        @if($exactBundle)
            <div style="margin-bottom: 24px; padding: 20px; background: var(--bg-secondary); border: 2px solid var(--accent); border-radius: var(--radius-md); position: relative; overflow: hidden;">
                <div style="position: absolute; top: -10px; right: -10px; font-size: 8rem; opacity: 0.05; pointer-events: none;">📦</div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--accent); text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; background: var(--accent); border-radius: 50%;"></span>
                    Bundle Ditemukan (Hasil Barcode)
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 4px;">{{ $exactBundle->nama }}</h2>
                        <div style="font-family: monospace; font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 12px; background: var(--bg-primary); padding: 4px 8px; border-radius: 4px; display: inline-block;">
                            {{ $exactBundle->kode }}
                        </div>
                        @if($exactBundle->deskripsi)
                            <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 600px;">{{ $exactBundle->deskripsi }}</p>
                        @endif
                    </div>
                    <div style="text-align: right;">
                        <div style="display: flex; gap: 12px; margin-bottom: 12px; justify-content: flex-end;">
                            <span class="badge badge-info">{{ $exactBundle->kategoris_count }} Kategori</span>
                            <span class="badge badge-success">{{ $exactBundle->dokumens_count }} Dokumen total</span>
                        </div>
                        <a href="/bundles/{{ $exactBundle->id }}" class="btn btn-primary">Buka Halaman Bundle →</a>
                    </div>
                </div>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px;">Dokumen di dalam Bundle ini:</h3>
        @else
            <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 16px;">
                Ditemukan <strong style="color: var(--text-primary);">{{ $results->count() }}</strong> dokumen
            </div>
        @endif

        @if($results->count() > 0)
            @foreach($results as $dokumen)
                <div class="dokumen-item">
                    <div class="dokumen-header">
                        <div>
                            <a href="/dokumen/{{ $dokumen->id }}" style="text-decoration: none; color: inherit;">
                                <div class="dokumen-title">{{ $dokumen->judul }}</div>
                            </a>
                            <div class="dokumen-meta">
                                @if($dokumen->nomor_dokumen)
                                    <span>📋 {{ $dokumen->nomor_dokumen }}</span>
                                @endif
                                @if($dokumen->tanggal_dokumen)
                                    <span>📅 {{ $dokumen->tanggal_dokumen->format('d M Y') }}</span>
                                @endif
                                <span>👤 {{ $dokumen->uploader?->name }}</span>
                            </div>
                        </div>
                        <a href="/dokumen/{{ $dokumen->id }}" class="btn btn-sm btn-secondary">Detail →</a>
                    </div>

                    <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; align-items: center;">
                        <span class="badge badge-accent">{{ $dokumen->kategori?->bundle?->nama }}</span>
                        <span class="badge badge-info">{{ $dokumen->kategori?->nama }}</span>
                        <span class="badge badge-success">{{ $dokumen->fileAttachments->count() }} file</span>
                    </div>

                    @if($dokumen->keterangan)
                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 8px;">
                            {{ Str::limit($dokumen->keterangan, 150) }}
                        </p>
                    @endif
                </div>
            @endforeach
        @else
            <div class="card">
                <div class="empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <div class="empty-state-text">Tidak ada dokumen yang cocok</div>
                    <div class="empty-state-hint">Coba kata kunci atau filter yang berbeda</div>
                </div>
            </div>
        @endif
    @else
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <div class="empty-state-text">Mulai ketik untuk mencari</div>
                <div class="empty-state-hint">Cari berdasarkan judul dokumen, nomor, kategori, atau nama bundle</div>
            </div>
        </div>
    @endif


</div>
