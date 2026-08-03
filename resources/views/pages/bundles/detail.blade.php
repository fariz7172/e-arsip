<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Bundle;
use Livewire\Attributes\Url;

new #[\Livewire\Attributes\Layout('layouts.app')] class extends Component {
    use WithPagination;

    public Bundle $bundle;

    #[Url]
    public string $searchKategori = '';

    public function title(): string
    {
        return 'Detail: ' . $this->bundle->nama;
    }

    public function mount(Bundle $bundle)
    {
        $this->bundle = $bundle->load([
            'creator',
            'kategoris' => function ($q) {
                $q->withCount('dokumens')->orderBy('urutan');
            },
            'kategoris.dokumens.fileAttachments',
            'kategoris.dokumens.uploader',
            'suratMasuks',
            'suratKeluars',
        ]);
    }

    public function hapusDokumen($id)
    {
        if (!auth()->user()->isAdmin()) {
            session()->flash('error', 'Hanya admin yang dapat menghapus dokumen.');
            return;
        }

        $dokumen = \App\Models\Dokumen::find($id);
        if ($dokumen) {
            $dokumen->delete(); // Soft delete
            session()->flash('success', 'Dokumen berhasil dihapus.');
        }
    }

    public function hapusKategori($id)
    {
        if (!auth()->user()->isAdmin()) {
            session()->flash('error', 'Hanya admin yang dapat menghapus kategori.');
            return;
        }

        $kategori = \App\Models\Kategori::find($id);
        if ($kategori) {
            foreach ($kategori->dokumens as $dok) {
                $dok->delete();
            }
            $kategori->delete();
            session()->flash('success', 'Kategori dan isinya berhasil dihapus.');
        }
    }

    public function with(): array
    {
        $kategorisQuery = $this->bundle->kategoris()
            ->whereNotIn('kode', ['SM', 'SK'])
            ->withCount('dokumens')
            ->with(['dokumens.fileAttachments', 'dokumens.uploader'])
            ->orderBy('urutan');

        $suratMasuksQuery = $this->bundle->suratMasuks();
        $suratKeluarsQuery = $this->bundle->suratKeluars();

        if (!empty($this->searchKategori)) {
            $kategorisQuery->where(function($q) {
                $q->where('nama', 'like', '%' . $this->searchKategori . '%')
                  ->orWhere('kode', 'like', '%' . $this->searchKategori . '%');
            });

            $suratMasuksQuery->where(function($q) {
                $q->where('perihal', 'like', '%' . $this->searchKategori . '%')
                  ->orWhere('no_surat', 'like', '%' . $this->searchKategori . '%')
                  ->orWhere('asal_surat', 'like', '%' . $this->searchKategori . '%');
            });

            $suratKeluarsQuery->where(function($q) {
                $q->where('perihal', 'like', '%' . $this->searchKategori . '%')
                  ->orWhere('no_surat', 'like', '%' . $this->searchKategori . '%')
                  ->orWhere('tujuan_surat', 'like', '%' . $this->searchKategori . '%');
            });
        }

        return [
            'filteredKategoris' => $kategorisQuery->paginate(10, ['*'], 'catPage'),
            'filteredSuratMasuk' => $suratMasuksQuery->paginate(12, ['*'], 'smPage'),
            'filteredSuratKeluar' => $suratKeluarsQuery->paginate(12, ['*'], 'skPage'),
        ];
    }
}; ?>
<div>
    <div class="breadcrumb">
        <a href="/dashboard">Dashboard</a>
        <span class="separator">/</span>
        <a href="/bundles">Bundle Arsip</a>
        <span class="separator">/</span>
        <span class="current">Detail: {{ $bundle->nama }}</span>
    </div>

    <style>
        .detail-hero {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-card) 100%);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 32px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }
        .detail-hero::before {
            content: '📦';
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8rem;
            opacity: 0.05;
            pointer-events: none;
        }
        .detail-hero-code {
            display: inline-block;
            font-family: monospace;
            font-size: 1rem;
            background: var(--bg-primary);
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            color: var(--accent);
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 16px;
        }
        .detail-stats-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .detail-stat-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .detail-stat-pill .stat-num {
            color: var(--accent);
            font-size: 1.1rem;
            font-weight: 800;
        }
        .kategori-section {
            margin-bottom: 24px;
        }
        .kategori-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 14px 20px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 4px solid var(--accent);
        }
        .kategori-header:hover {
            box-shadow: var(--shadow-glow);
        }
        .kategori-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .kategori-icon-box {
            width: 36px;
            height: 36px;
            background: var(--accent-glow);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .kategori-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        @media (max-width: 768px) {
            .kategori-grid {
                grid-template-columns: 1fr;
            }
        }
        .dokumen-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 8px 4px 16px 4px;
        }
        .dokumen-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            transition: all 0.2s;
            position: relative;
        }
        .dokumen-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .dokumen-card-title {
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 6px;
            color: var(--text-primary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .dokumen-card-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .file-mini-list {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 8px;
        }
        .file-mini-chip {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        .file-mini-chip.pdf { border-color: #ef444480; color: #ef4444; background: #ef444410; }
        .file-mini-chip.img { border-color: #10b98180; color: #10b981; background: #10b98110; }
        .qr-box {
            padding: 6px;
            background: white;
            border-radius: 8px;
            display: inline-block;
        }
        .empty-kategori {
            padding: 24px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
    </style>

    <!-- Hero Section -->
    <div class="detail-hero">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 24px;">
            <div style="flex: 1; min-width: 280px;">
                <div class="detail-hero-code">{{ $bundle->kode }}</div>
                <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 8px; line-height: 1.2;">{{ $bundle->nama }}</h1>
                @if($bundle->deskripsi)
                    <p style="color: var(--text-secondary); font-size: 0.95rem; max-width: 600px; line-height: 1.7;">{{ $bundle->deskripsi }}</p>
                @endif
                <div class="detail-stats-row">
                    <div class="detail-stat-pill">📂 <span class="stat-num">{{ $bundle->kategoris->count() }}</span> Kategori</div>
                    <div class="detail-stat-pill">📄 <span class="stat-num">{{ $bundle->kategoris->sum(fn($k) => $k->dokumens->count()) }}</span> Dokumen</div>
                    <div class="detail-stat-pill">📎 <span class="stat-num">{{ $bundle->kategoris->sum(fn($k) => $k->dokumens->sum(fn($d) => $d->fileAttachments->count())) }}</span> File</div>
                    <div class="detail-stat-pill">📅 <span class="stat-num">{{ $bundle->tahun }}</span></div>
                    @if($bundle->creator)
                        <div class="detail-stat-pill">👤 <span style="color: var(--text-secondary);">{{ $bundle->creator->name }}</span></div>
                    @endif
                </div>
            </div>
            <div style="text-align: center;">
                <div class="qr-box" style="margin-bottom: 8px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(url('/bundles/' . $bundle->id . '/detail')) }}" alt="QR Code {{ $bundle->kode }}" style="width: 130px; height: 130px; object-fit: contain;">
                </div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">Scan untuk akses bundle</div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="flex items-center justify-between mb-6" style="flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <h2 style="font-size: 1.2rem; font-weight: 800; margin: 0;">
                🗂️ Daftar Isi Bundle
            </h2>
            <!-- Pencarian Kategori -->
            <div style="position: relative; width: 260px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" wire:model.live.debounce.300ms="searchKategori" placeholder="Cari nama atau kode kategori..." style="width: 100%; padding: 6px 10px 6px 32px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem; outline: none; transition: all 0.2s;">
            </div>
        </div>
        <div class="flex gap-2">
            <a href="/bundles/{{ $bundle->id }}" class="btn btn-secondary">Kelola Bundle →</a>
            <a href="/bundles" class="btn btn-secondary">← Kembali</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #d1fae5; color: #047857; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
            {{ session('error') }}
        </div>
    @endif

    @if($filteredKategoris->count() > 0)
        <div class="kategori-grid">
            @foreach($filteredKategoris as $index => $kategori)
                <div class="kategori-section" style="margin-bottom: 0;">
                <details open>
                    <summary class="kategori-header" style="list-style: none;">
                        <div class="kategori-header-left">
                            <div class="kategori-icon-box">📂</div>
                            <div>
                                <div style="font-weight: 700; font-size: 1rem;">{{ $kategori->nama }}</div>
                                @if($kategori->kode)
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">{{ $kategori->kode }}</div>
                                @endif
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge badge-info">{{ $kategori->dokumens->count() }} dokumen</span>
                            <a href="/bundles/{{ $bundle->id }}/kategori/{{ $kategori->id }}" class="btn btn-sm btn-secondary" onclick="event.stopPropagation()">Buka →</a>
                            @if(auth()->user()->isAdmin())
                                <button wire:click.prevent="hapusKategori({{ $kategori->id }})" 
                                        wire:confirm="Yakin ingin menghapus kategori '{{ $kategori->nama }}' beserta seluruh dokumen di dalamnya?"
                                        class="btn btn-sm" style="background:#fef2f2; color:#ef4444; border:1px solid #fecaca; padding:4px 8px;" onclick="event.stopPropagation()">Hapus</button>
                            @endif
                            <span style="color: var(--text-muted); font-size: 0.9rem;">▾</span>
                        </div>
                    </summary>

                    @if($kategori->dokumens->count() > 0)
                        <div class="dokumen-grid">
                            @foreach($kategori->dokumens as $dokumen)
                                <div class="dokumen-card" x-data="{ showModal: false }">
                                    <div style="position:relative;">
                                        <div>
                                            <div class="dokumen-card-title" style="padding-right:30px;">{{ $dokumen->judul }}</div>
                                            <div class="dokumen-card-meta">

                                            @if($dokumen->nomor_dokumen)
                                                <span>📋 {{ $dokumen->nomor_dokumen }}</span>
                                            @endif
                                            @if($dokumen->tanggal_dokumen)
                                                <span>📅 {{ $dokumen->tanggal_dokumen->format('d M Y') }}</span>
                                            @endif
                                            <span>👤 {{ $dokumen->uploader?->name }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div style="display: flex; gap: 8px; margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                                            <a href="/dokumen/{{ $dokumen->id }}" class="btn btn-sm btn-secondary" style="flex: 1; text-align: center; justify-content: center; padding: 6px;">Halaman Detail</a>
                                            <button @click="showModal = true" class="btn btn-sm btn-primary" style="flex: 1; text-align: center; justify-content: center; padding: 6px;">Info & File ({{ $dokumen->fileAttachments->count() }})</button>
                                        </div>
                                    
                                        @if(auth()->user()->isAdmin())
                                            <button wire:click.prevent="hapusDokumen({{ $dokumen->id }})" 
                                                    wire:confirm="Yakin ingin menghapus dokumen '{{ $dokumen->judul }}'?"
                                                    style="position:absolute; top:0px; right:0px; background:white; border:1px solid #fecaca; color:#ef4444; border-radius:6px; padding:6px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:10; box-shadow:0 1px 2px rgba(0,0,0,0.05);"
                                                    title="Hapus Dokumen">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Modal Detail Dokumen -->
                                    <template x-teleport="body">
                                        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;" x-transition>
                                        <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden flex flex-col" style="background: white; border-radius: 16px; max-width: 800px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;">
                                            
                                            <!-- Modal Header -->
                                            <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between" style="padding: 24px 32px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                                                <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 16px;">
                                                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center" style="width: 48px; height: 48px; background: var(--accent-glow); color: var(--accent); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                    </div>
                                                    <div>
                                                        <h2 class="text-xl font-black text-slate-800" style="margin: 0; font-size: 1.25rem; font-weight: 900; color: #1e293b;">Detail Dokumen</h2>
                                                        <p class="text-xs text-slate-400 uppercase tracking-widest font-bold" style="margin: 0; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700;">Informasi Lengkap & File</p>
                                                    </div>
                                                </div>
                                                <button @click="showModal = false" class="p-2 hover:bg-rose-50 text-slate-400 hover:text-rose-500 rounded-xl transition-all" style="background: transparent; border: none; cursor: pointer; color: #94a3b8; padding: 8px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                </button>
                                            </div>

                                            <!-- Modal Content -->
                                            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar" style="padding: 32px; overflow-y: auto; flex: 1;">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                                                    
                                                    @php $payment = $bundle->payments->first(); @endphp
                                                    @if($payment)
                                                    <!-- Section I: Anggaran -->
                                                    <div class="space-y-4" style="display: flex; flex-direction: column; gap: 16px;">
                                                        <div class="flex items-center gap-2 text-primary" style="display: flex; align-items: center; gap: 8px; color: var(--accent);">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                                                            <h4 class="font-black text-sm uppercase tracking-wider" style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">I. Data Anggaran</h4>
                                                        </div>
                                                        <div class="bg-slate-50 rounded-2xl p-5 space-y-3 border border-slate-100" style="background: #f8fafc; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px; border: 1px solid #f1f5f9;">
                                                            <div class="flex justify-between border-b border-slate-200/50 pb-2" style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                                                                <span class="text-xs text-slate-400 font-bold" style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">No. SPD</span>
                                                                <span class="text-xs font-black text-slate-700" style="font-size: 0.75rem; font-weight: 900; color: #334155;">{{ $payment->no_spd ?: '-' }}</span>
                                                            </div>
                                                            <div class="flex flex-col gap-1">
                                                                <span class="text-xs text-slate-400 font-bold" style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">Program</span>
                                                                <span class="text-sm font-bold text-slate-800" style="font-size: 0.95rem; font-weight: 700; color: #1e293b;">{{ $payment->program ?: '-' }}</span>
                                                            </div>
                                                            <div class="flex flex-col gap-1">
                                                                <span class="text-xs text-slate-400 font-bold" style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">Kegiatan</span>
                                                                <span class="text-xs text-slate-600 leading-relaxed" style="font-size: 0.75rem; color: #475569;">{{ $payment->kegiatan ?: '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Section II: Kontrak -->
                                                    <div class="space-y-4" style="display: flex; flex-direction: column; gap: 16px;">
                                                        <div class="flex items-center gap-2 text-emerald-500" style="display: flex; align-items: center; gap: 8px; color: #10b981;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                            <h4 class="font-black text-sm uppercase tracking-wider" style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">II. Kontrak & Nilai</h4>
                                                        </div>
                                                        <div class="bg-emerald-50/30 rounded-2xl p-5 space-y-3 border border-emerald-100/50" style="background: rgba(236, 253, 245, 0.3); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px; border: 1px solid rgba(209, 250, 229, 0.5);">
                                                            <div class="flex justify-between border-b border-emerald-200/30 pb-2" style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(167, 243, 208, 0.3); padding-bottom: 8px;">
                                                                <span class="text-xs text-emerald-600/70 font-bold" style="font-size: 0.75rem; color: rgba(5, 150, 105, 0.7); font-weight: 700;">No. Kontrak</span>
                                                                <span class="text-xs font-black text-emerald-700" style="font-size: 0.75rem; font-weight: 900; color: #047857;">{{ optional($payment->contract)->nomor_kontrak ?: '-' }}</span>
                                                            </div>
                                                            <div class="flex justify-between">
                                                                <span class="text-xs text-emerald-600/70 font-bold" style="font-size: 0.75rem; color: rgba(5, 150, 105, 0.7); font-weight: 700;">Nilai Kontrak</span>
                                                                <span class="text-sm font-black text-emerald-600" style="font-size: 0.95rem; font-weight: 900; color: #059669;">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Section III: Uraian Keperluan -->
                                                    <div class="space-y-4 md:col-span-2" style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                                        <div class="flex items-center gap-2 text-blue-500" style="display: flex; align-items: center; gap: 8px; color: #3b82f6;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                            <h4 class="font-black text-sm uppercase tracking-wider" style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">III. Uraian Pembayaran</h4>
                                                        </div>
                                                        <div class="bg-blue-50/30 rounded-2xl p-5 border border-blue-100/50" style="background: rgba(239, 246, 255, 0.3); border-radius: 16px; padding: 20px; border: 1px solid rgba(219, 234, 254, 0.5);">
                                                            <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line" style="font-size: 0.85rem; color: #334155; line-height: 1.6; white-space: pre-line; margin: 0;">{{ $payment->keperluan ?: '-' }}</p>
                                                        </div>
                                                    </div>

                                                    <!-- Section IV: Dokumen Pembayaran -->
                                                    <div class="space-y-4 md:col-span-2" style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                                        <div class="flex items-center gap-2 text-indigo-500" style="display: flex; align-items: center; gap: 8px; color: #6366f1;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                                            <h4 class="font-black text-sm uppercase tracking-wider" style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">IV. Dokumen Pembayaran</h4>
                                                        </div>
                                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px;">
                                                            <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm" style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1" style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">No. SPM</p>
                                                                <p class="text-xs font-black text-slate-700" style="font-size: 0.8rem; font-weight: 900; color: #334155;">{{ $payment->no_spm ?: '-' }}</p>
                                                            </div>
                                                            <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm" style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1" style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">No. SPP</p>
                                                                <p class="text-xs font-black text-slate-700" style="font-size: 0.8rem; font-weight: 900; color: #334155;">{{ $payment->no_spp ?: '-' }}</p>
                                                            </div>
                                                            <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm" style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1" style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">No. KWI</p>
                                                                <p class="text-xs font-black text-slate-700" style="font-size: 0.8rem; font-weight: 900; color: #334155;">{{ $payment->no_kwi ?: '-' }}</p>
                                                            </div>
                                                            <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm" style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1" style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">No. SP2D</p>
                                                                <p class="text-xs font-black text-slate-700" style="font-size: 0.8rem; font-weight: 900; color: #334155;">{{ $payment->no_sp2d ?: '-' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Section V: Vendor -->
                                                    <div class="space-y-4 md:col-span-2" style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                                        <div class="flex items-center gap-2 text-purple-500" style="display: flex; align-items: center; gap: 8px; color: #a855f7;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                                            <h4 class="font-black text-sm uppercase tracking-wider" style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">V. Informasi Vendor</h4>
                                                        </div>
                                                        <div class="bg-purple-50/30 rounded-2xl p-5 border border-purple-100/50" style="background: rgba(250, 245, 255, 0.3); border-radius: 16px; padding: 20px; border: 1px solid rgba(233, 213, 255, 0.5);">
                                                            <div class="flex flex-col gap-1">
                                                                <span class="text-xs text-purple-400 font-bold" style="font-size: 0.75rem; color: #c084fc; font-weight: 700;">Perusahaan / Vendor</span>
                                                                <span class="text-lg font-black text-purple-900" style="font-size: 1.1rem; font-weight: 900; color: #581c87;">{{ optional($payment->vendor)->nama_perusahaan ?: '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @else
                                                    <!-- Optional Data Dokumen when no payment linked -->
                                                    <div class="space-y-4 md:col-span-2" style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                                        <div class="bg-slate-50 rounded-2xl p-5 space-y-3 border border-slate-100" style="background: #f8fafc; border-radius: 16px; padding: 20px; border: 1px solid #f1f5f9;">
                                                            <p class="text-xs text-slate-400 text-center">Data pembayaran tidak tersedia untuk bundle ini.</p>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <!-- Section VI: Dokumen File -->
                                                    <div class="space-y-4 md:col-span-2" style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                                        <div class="flex items-center gap-2 text-amber-500" style="display: flex; align-items: center; gap: 8px; color: #f59e0b;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                            <h4 class="font-black text-sm uppercase tracking-wider" style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">VI. File Lampiran</h4>
                                                        </div>
                                                        
                                                        @if($dokumen->fileAttachments->count() > 0)
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                                                                @foreach($dokumen->fileAttachments as $file)
                                                                    <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-sm hover:bg-amber-50 hover:border-amber-200 transition-all group" style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; gap: 12px;">
                                                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                                <span style="font-size: 1.25rem;">{{ $file->is_pdf ? '📄' : ($file->is_image ? '🖼️' : '📎') }}</span>
                                                                                <p class="text-[10px] text-slate-400 font-bold uppercase" style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">{{ $file->is_pdf ? 'PDF' : ($file->is_image ? 'Gambar' : 'File') }}</p>
                                                                            </div>
                                                                            <span style="font-size: 0.65rem; font-weight: 700; color: #94a3b8;">{{ $file->ukuran_file ? number_format($file->ukuran_file / 1024, 2) . ' KB' : '' }}</span>
                                                                        </div>
                                                                        <p class="text-sm font-black text-slate-700" style="font-size: 0.875rem; font-weight: 900; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $file->nama_file }}">{{ $file->nama_file }}</p>
                                                                        <div style="display: flex; gap: 8px; margin-top: auto;">
                                                                            @if($file->is_image || $file->is_pdf)
                                                                                <a href="{{ route('file.preview', $file->id) }}" target="_blank" class="btn btn-sm btn-secondary" style="flex: 1; text-align: center; padding: 6px; font-size: 0.75rem;">Lihat</a>
                                                                            @endif
                                                                            <a href="{{ route('file.download', $file->id) }}" class="btn btn-sm btn-primary" style="flex: 1; text-align: center; padding: 6px; font-size: 0.75rem;">Unduh</a>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="bg-white border border-slate-100 p-8 rounded-2xl shadow-sm text-center" style="background: white; border: 1px solid #f1f5f9; padding: 32px; border-radius: 16px; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                                                Tidak ada file lampiran.
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </template>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-kategori">
                            <span style="font-size: 2rem;">📭</span><br>
                            Belum ada dokumen dalam kategori ini
                        </div>
                    @endif
                </details>
            </div>
        @endforeach
        </div>

        @if($filteredKategoris->hasPages())
            <div style="margin-top: 16px; padding: 16px; border: 1px solid var(--border-color); background: var(--bg-card); border-radius: var(--radius-md); margin-bottom: 24px;">
                {{ $filteredKategoris->links('vendor.pagination.custom', data: ['scrollTo' => false]) }}
            </div>
        @endif
    @else
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">🗂️</div>
                <div class="empty-state-text">Bundle ini belum memiliki kategori</div>
                <a href="/bundles/{{ $bundle->id }}" class="btn btn-primary mt-4">Tambah Kategori →</a>
            </div>
        </div>
    @endif

    <!-- Surat Masuk Section -->
    @if($filteredSuratMasuk && $filteredSuratMasuk->count() > 0)
        <div class="kategori-section" style="margin-top: 24px;">
            <details open>
                <summary class="kategori-header" style="list-style: none; background: #f0f9ff; border: 1px solid #bae6fd;">
                    <div class="kategori-header-left">
                        <div class="kategori-icon-box" style="background: #e0f2fe;">📥</div>
                        <div>
                            <div style="font-weight: 700; font-size: 1rem; color: #0369a1;">Surat Masuk</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge" style="background: #bae6fd; color: #0369a1;">{{ $filteredSuratMasuk->count() }} surat</span>
                        <span style="color: var(--text-muted); font-size: 0.9rem;">▾</span>
                    </div>
                </summary>

                <div class="dokumen-grid">
                    @foreach($filteredSuratMasuk as $surat)
                        <div class="dokumen-card">
                            <a href="{{ $surat->dokumen_id ? '/dokumen/' . $surat->dokumen_id : '/surat-masuk/' . $surat->id . '/edit' }}" style="text-decoration: none; color: inherit; display: block;">
                                <div class="dokumen-card-title">{{ $surat->perihal ?? 'Tanpa Perihal' }}</div>
                                <div class="dokumen-card-meta">
                                    <span>📋 {{ $surat->no_surat ?? '-' }}</span>
                                    <span>🏢 {{ $surat->asal_surat ?? '-' }}</span>
                                    <span>📅 {{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('d M Y') : '-' }}</span>
                                </div>
                                @if($surat->scan_file && is_array($surat->scan_file) && count($surat->scan_file) > 0)
                                    <div class="file-mini-list">
                                        <span class="file-mini-chip pdf">📄 {{ count($surat->scan_file) }} File</span>
                                    </div>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
                @if($filteredSuratMasuk->hasPages())
                    <div style="margin-top: 16px; padding: 16px; border-top: 1px solid var(--border-color); background: var(--bg-card); border-radius: var(--radius-md);">
                        {{ $filteredSuratMasuk->links('vendor.pagination.custom', data: ['scrollTo' => false]) }}
                    </div>
                @endif
            </details>
        </div>
    @endif

    <!-- Surat Keluar Section -->
    @if($filteredSuratKeluar && $filteredSuratKeluar->count() > 0)
        <div class="kategori-section" style="margin-top: 24px;">
            <details open>
                <summary class="kategori-header" style="list-style: none; background: #fdf4ff; border: 1px solid #fbcfe8;">
                    <div class="kategori-header-left">
                        <div class="kategori-icon-box" style="background: #fae8ff;">📤</div>
                        <div>
                            <div style="font-weight: 700; font-size: 1rem; color: #a21caf;">Surat Keluar</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge" style="background: #fbcfe8; color: #a21caf;">{{ $filteredSuratKeluar->count() }} surat</span>
                        <span style="color: var(--text-muted); font-size: 0.9rem;">▾</span>
                    </div>
                </summary>

                <div class="dokumen-grid">
                    @foreach($filteredSuratKeluar as $surat)
                        <div class="dokumen-card">
                            <a href="{{ $surat->dokumen_id ? '/dokumen/' . $surat->dokumen_id : '/surat-keluar/' . $surat->id . '/edit' }}" style="text-decoration: none; color: inherit; display: block;">
                                <div class="dokumen-card-title">{{ $surat->perihal ?? 'Tanpa Perihal' }}</div>
                                <div class="dokumen-card-meta">
                                    <span>📋 {{ $surat->no_surat ?? '-' }}</span>
                                    <span>🎯 {{ $surat->tujuan_surat ?? '-' }}</span>
                                    <span>📅 {{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('d M Y') : '-' }}</span>
                                </div>
                                @if($surat->scan_file && is_array($surat->scan_file) && count($surat->scan_file) > 0)
                                    <div class="file-mini-list">
                                        <span class="file-mini-chip pdf">📄 {{ count($surat->scan_file) }} File</span>
                                    </div>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
                @if($filteredSuratKeluar->hasPages())
                    <div style="margin-top: 16px; padding: 16px; border-top: 1px solid var(--border-color); background: var(--bg-card); border-radius: var(--radius-md);">
                        {{ $filteredSuratKeluar->links('vendor.pagination.custom', data: ['scrollTo' => false]) }}
                    </div>
                @endif
            </details>
        </div>
    @endif

    <div style="margin-top: 32px;">
        <a href="/bundles" class="btn btn-secondary">← Kembali ke Daftar Bundle</a>
    </div>
</div>
