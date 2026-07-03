<?php

use Livewire\Volt\Component;
use App\Models\Bundle;
use App\Models\Dokumen;
use App\Models\FileAttachment;
use App\Models\User;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Dashboard')] class extends Component {
    public function with(): array
    {
        $totalBundles = Bundle::count();
        $totalDokumens = Dokumen::count();
        $totalFiles = FileAttachment::count();
        $totalUsers = User::count();

        // Storage used
        $totalSize = FileAttachment::sum('ukuran');
        if ($totalSize >= 1073741824) {
            $storageUsed = number_format($totalSize / 1073741824, 2) . ' GB';
        } elseif ($totalSize >= 1048576) {
            $storageUsed = number_format($totalSize / 1048576, 2) . ' MB';
        } elseif ($totalSize >= 1024) {
            $storageUsed = number_format($totalSize / 1024, 2) . ' KB';
        } else {
            $storageUsed = $totalSize . ' B';
        }

        // Recent documents
        $recentDokumens = Dokumen::with(['kategori.bundle', 'uploader', 'fileAttachments'])
            ->latest()
            ->take(10)
            ->get();

        // Bundles with counts
        $bundles = Bundle::withCount('kategoris')
            ->latest()
            ->take(6)
            ->get();

        return compact('totalBundles', 'totalDokumens', 'totalFiles', 'totalUsers', 'storageUsed', 'recentDokumens', 'bundles');
    }
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px;">
        <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">Dashboard</h1>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Selamat datang, <strong style="color:var(--primary);">{{ auth()->user()->name }}</strong>! Berikut ringkasan sistem arsip Anda.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Total Bundle</div>
                <div class="stat-icon-wrap stat-icon-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
            </div>
            <div class="stat-value">{{ $totalBundles }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Total Dokumen</div>
                <div class="stat-icon-wrap stat-icon-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
            </div>
            <div class="stat-value">{{ $totalDokumens }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Total File</div>
                <div class="stat-icon-wrap stat-icon-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                </div>
            </div>
            <div class="stat-value">{{ $totalFiles }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Penyimpanan</div>
                <div class="stat-icon-wrap stat-icon-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </div>
            </div>
            <div class="stat-value" style="font-size:1.5rem;">{{ $storageUsed }}</div>
        </div>
    </div>

    <!-- Recent Bundles -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title" style="display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--primary);"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                Bundle Terbaru
            </h2>
            <a href="/bundles" class="btn btn-sm btn-secondary">
                Lihat Semua
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>

        @if($bundles->count() > 0)
            <div class="grid-3">
                @foreach($bundles as $bundle)
                    <a href="/bundles/{{ $bundle->id }}" class="bundle-card">
                        <div class="bundle-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <div class="bundle-card-title">{{ $bundle->nama }}</div>
                        <span class="bundle-card-code">{{ $bundle->kode }}</span>
                        <div class="bundle-card-stats">
                            <div class="bundle-card-stat"><strong>{{ $bundle->kategoris_count }}</strong> kategori</div>
                            <div class="bundle-card-stat"><strong>{{ $bundle->tahun }}</strong></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="empty-state-text">Belum ada bundle arsip</div>
                <div class="empty-state-hint">Buat bundle pertama Anda untuk mulai mengarsipkan dokumen</div>
                @if(auth()->user()->canManage())
                    <a href="/bundles/create" class="btn btn-primary mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                        Buat Bundle
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Recent Documents -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title" style="display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Dokumen Terbaru
            </h2>
        </div>

        @if($recentDokumens->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Dokumen</th>
                            <th>Bundle / Kategori</th>
                            <th>No. Dokumen</th>
                            <th>Tanggal</th>
                            <th>File</th>
                            <th>Diupload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentDokumens as $dok)
                            <tr>
                                <td>
                                    <a href="/dokumen/{{ $dok->id }}" style="color:var(--primary); text-decoration:none; font-weight:600; display:flex; align-items:center; gap:6px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        {{ $dok->judul }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-accent">{{ $dok->kategori?->bundle?->nama ?? '-' }}</span>
                                    <span class="text-muted text-xs" style="display:block;margin-top:2px;">{{ $dok->kategori?->nama ?? '-' }}</span>
                                </td>
                                <td style="color:var(--text-muted); font-family:'Courier New',monospace; font-size:0.8rem;">
                                    {{ $dok->nomor_dokumen ?? '-' }}
                                </td>
                                <td style="color:var(--text-secondary); font-size:0.85rem;">
                                    {{ $dok->tanggal_dokumen?->format('d M Y') ?? '-' }}
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $dok->fileAttachments->count() }} file</span>
                                </td>
                                <td style="color:var(--text-muted); font-size:0.8rem;">
                                    {{ $dok->uploader?->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="empty-state-text">Belum ada dokumen yang diupload</div>
            </div>
        @endif
    </div>

</div>

