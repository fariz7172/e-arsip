<?php

use Livewire\Volt\Component;
use App\Models\Bundle;
use Livewire\Attributes\Url;

new #[\Livewire\Attributes\Layout('layouts.app')] class extends Component {
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

    public function with(): array
    {
        $kategorisQuery = $this->bundle->kategoris()
            ->withCount('dokumens')
            ->with(['dokumens.fileAttachments', 'dokumens.uploader'])
            ->orderBy('urutan');

        if (!empty($this->searchKategori)) {
            $kategorisQuery->where(function($q) {
                $q->where('nama', 'like', '%' . $this->searchKategori . '%')
                  ->orWhere('kode', 'like', '%' . $this->searchKategori . '%');
            });
        }

        return [
            'filteredKategoris' => $kategorisQuery->get()
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
        .dokumen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 12px;
            padding: 4px 4px 16px 4px;
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
            flex-direction: column;
            gap: 2px;
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

    @if($filteredKategoris->count() > 0)
        @foreach($filteredKategoris as $index => $kategori)
            <div class="kategori-section">
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
                            <span style="color: var(--text-muted); font-size: 0.9rem;">▾</span>
                        </div>
                    </summary>

                    @if($kategori->dokumens->count() > 0)
                        <div class="dokumen-grid">
                            @foreach($kategori->dokumens as $dokumen)
                                <div class="dokumen-card">
                                    <a href="/dokumen/{{ $dokumen->id }}" style="text-decoration: none; color: inherit; display: block;">
                                        <div class="dokumen-card-title">{{ $dokumen->judul }}</div>
                                        <div class="dokumen-card-meta">
                                            @if($dokumen->nomor_dokumen)
                                                <span>📋 {{ $dokumen->nomor_dokumen }}</span>
                                            @endif
                                            @if($dokumen->tanggal_dokumen)
                                                <span>📅 {{ $dokumen->tanggal_dokumen->format('d M Y') }}</span>
                                            @endif
                                            <span>👤 {{ $dokumen->uploader?->name }}</span>
                                        </div>
                                        <div class="file-mini-list">
                                            @foreach($dokumen->fileAttachments as $file)
                                                <span class="file-mini-chip {{ $file->is_pdf ? 'pdf' : ($file->is_image ? 'img' : '') }}">
                                                    {{ $file->is_pdf ? '📄' : ($file->is_image ? '🖼️' : '📎') }}
                                                    {{ Str::limit($file->nama_file, 18) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </a>
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
    @if($bundle->suratMasuks && $bundle->suratMasuks->count() > 0)
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
                        <span class="badge" style="background: #bae6fd; color: #0369a1;">{{ $bundle->suratMasuks->count() }} surat</span>
                        <span style="color: var(--text-muted); font-size: 0.9rem;">▾</span>
                    </div>
                </summary>

                <div class="dokumen-grid">
                    @foreach($bundle->suratMasuks as $surat)
                        <div class="dokumen-card">
                            <a href="/surat-masuk/{{ $surat->id }}/edit" style="text-decoration: none; color: inherit; display: block;">
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
            </details>
        </div>
    @endif

    <!-- Surat Keluar Section -->
    @if($bundle->suratKeluars && $bundle->suratKeluars->count() > 0)
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
                        <span class="badge" style="background: #fbcfe8; color: #a21caf;">{{ $bundle->suratKeluars->count() }} surat</span>
                        <span style="color: var(--text-muted); font-size: 0.9rem;">▾</span>
                    </div>
                </summary>

                <div class="dokumen-grid">
                    @foreach($bundle->suratKeluars as $surat)
                        <div class="dokumen-card">
                            <a href="/surat-keluar/{{ $surat->id }}/edit" style="text-decoration: none; color: inherit; display: block;">
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
            </details>
        </div>
    @endif

    <div style="margin-top: 32px;">
        <a href="/bundles" class="btn btn-secondary">← Kembali ke Daftar Bundle</a>
    </div>
</div>
