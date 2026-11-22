<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\SuratKeluar;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Buku Agenda - Surat Keluar')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public $perPage = 10;

    #[Url]
    public ?string $tanggal_surat_dari = '';

    #[Url]
    public ?string $tanggal_surat_sampai = '';

    #[Url]
    public ?string $tgl_masuk_tu_dari = '';

    #[Url]
    public ?string $tgl_masuk_tu_sampai = '';

    public $selectedSurat = [];
    public $selectAll = false;
    public bool $showExportDibDbModal = false;

    public function openExportDibDbModal()
    {
        $this->showExportDibDbModal = true;
    }

    public function closeExportDibDbModal()
    {
        $this->showExportDibDbModal = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedSurat = $this->with()['surats']->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedSurat = [];
        }
    }

    public function updatedSelectedSurat()
    {
        $currentPageIds = $this->with()['surats']->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->selectAll = count($currentPageIds) > 0 && count(array_diff($currentPageIds, $this->selectedSurat)) === 0;
    }

    public function updatingPage()
    {
        $this->selectAll = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTanggalSuratDari()
    {
        $this->resetPage();
    }

    public function updatingTanggalSuratSampai()
    {
        $this->resetPage();
    }

    public function updatingTglMasukTuDari()
    {
        $this->resetPage();
    }

    public function updatingTglMasukTuSampai()
    {
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset(['search', 'tanggal_surat_dari', 'tanggal_surat_sampai', 'tgl_masuk_tu_dari', 'tgl_masuk_tu_sampai', 'selectedSurat', 'selectAll']);
        $this->resetPage();
    }

    public function delete(int $id)
    {
        $surat = SuratKeluar::findOrFail($id);
        if ($surat->scan_file) {
            $files = is_array($surat->scan_file) ? $surat->scan_file : [];
            foreach ($files as $file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
            }
        }
        $surat->delete();
        session()->flash('success', 'Surat keluar berhasil dihapus.');
    }

    public function with(): array
    {
        $query = SuratKeluar::with('dokumen.fileAttachments')
            ->orderByRaw('CASE WHEN tgl_masuk_tu IS NULL THEN 1 ELSE 0 END ASC, tgl_masuk_tu DESC, tanggal DESC, CAST(no_urut AS UNSIGNED) DESC');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('no_surat', 'like', '%' . $this->search . '%')
                  ->orWhere('no_urut', 'like', '%' . $this->search . '%')
                  ->orWhere('perihal', 'like', '%' . $this->search . '%')
                  ->orWhere('tujuan_surat', 'like', '%' . $this->search . '%')
                  ->orWhere('tembusan', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->tanggal_surat_dari)) {
            $query->whereDate('tanggal', '>=', $this->tanggal_surat_dari);
        }

        if (!empty($this->tanggal_surat_sampai)) {
            $query->whereDate('tanggal', '<=', $this->tanggal_surat_sampai);
        }

        if (!empty($this->tgl_masuk_tu_dari)) {
            $query->whereDate('tgl_masuk_tu', '>=', $this->tgl_masuk_tu_dari);
        }

        if (!empty($this->tgl_masuk_tu_sampai)) {
            $query->whereDate('tgl_masuk_tu', '<=', $this->tgl_masuk_tu_sampai);
        }

        return [
            'surats' => $query->paginate($this->perPage),
            'totalAllSurat' => SuratKeluar::count(),
        ];
    }
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">
                Buku Agenda: Surat Keluar
            </h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Kelola dan lacak riwayat pembuatan surat keluar serta persetujuannya.</p>
        </div>
        
        <div style="display:flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('surat-keluar.print-batch') }}{{ count($selectedSurat) > 0 ? '?ids='.implode(',', $selectedSurat) : '' }}" target="_blank" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; color: #4f46e5;" title="Cetak Laporan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                {{ count($selectedSurat) > 0 ? 'Cetak (' . count($selectedSurat) . ')' : 'Cetak' }}
            </a>

            <a href="{{ asset('assets/surat-keluar-template.xlsx') }}" download class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;" title="Download Template Excel">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Template
            </a>
            
            <a href="{{ route('surat-keluar.export', array_filter(['search' => $search, 'tanggal_surat_dari' => $tanggal_surat_dari, 'tanggal_surat_sampai' => $tanggal_surat_sampai])) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; color: #10b981;" title="Export ke Excel">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                Export
            </a>

            @if(count($selectedSurat) > 0)
                <button type="button" wire:click="openExportDibDbModal" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; background: #059669; border-color: #059669; color: white; font-weight: 600; cursor: pointer;" title="Export Format Arsip Gabung (DIB & DB)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Export DIB & DB ({{ count($selectedSurat) }})
                </button>
            @else
                <button type="button" wire:click="openExportDibDbModal" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; color: #059669; border-color: #a7f3d0; background: #ecfdf5; font-weight: 600; cursor: pointer;" title="Export Format Arsip Gabung (DIB & DB)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                    Export DIB & DB
                </button>
            @endif

            <form action="{{ route('surat-keluar.import') }}" method="POST" id="import_form" enctype="multipart/form-data" style="display:inline;">
                @csrf
                <input type="file" name="file" id="import_file" style="display: none;" onchange="if(confirm('Apakah Anda yakin ingin mengimpor data ini?')) { document.getElementById('import-overlay').style.display='flex'; this.form.submit(); }" accept=".xlsx,.xls,.csv">
                <button type="button" onclick="document.getElementById('import_file').click()" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; color: #f59e0b;" title="Import dari Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="12" x2="12" y2="18"/><polyline points="9 15 12 12 15 15"/></svg>
                    Import
                </button>
            </form>

            <a href="/surat-keluar/create" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah
            </a>
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

    <div class="card">
        <div class="card-header" style="display:flex; flex-direction:column; gap:14px; padding: 18px 20px;">
            <!-- Baris 1: Per Page, Search & Reset Button -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; width: 100%;">
                <div style="display: flex; gap: 12px; align-items: center; flex: 1; max-width: 500px;">
                    <select wire:model.live="perPage" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size:0.9rem; background: white; cursor: pointer;">
                        <option value="10">10 Data</option>
                        <option value="25">25 Data</option>
                        <option value="50">50 Data</option>
                        <option value="100">100 Data</option>
                    </select>
                    <div style="flex: 1; position: relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No Urut, No Surat, Tujuan, Perihal..." style="padding: 8px 12px 8px 32px; border-radius: 6px; border: 1px solid var(--border-color); width: 100%; font-size:0.9rem; outline: none;">
                    </div>
                </div>

                @if($search || $tanggal_surat_dari || $tanggal_surat_sampai || $tgl_masuk_tu_dari || $tgl_masuk_tu_sampai)
                    <button type="button" wire:click="resetFilter" class="btn btn-sm btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; color: #ef4444; border-color: #fca5a5; background: #fef2f2; font-weight: 600;" title="Kosongkan Semua Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Reset Filter
                    </button>
                @endif
            </div>

            <!-- Baris 2: Filter TGL Masuk TU & Tanggal Surat -->
            <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; padding-top: 12px; border-top: 1px dashed var(--border-color);">
                <!-- Filter TGL Masuk TU -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; background: #ecfdf5; padding: 6px 12px; border-radius: 6px; border: 1px solid #a7f3d0;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #047857; display: flex; align-items: center; gap: 4px;">
                        <span>📥</span> TGL Masuk TU:
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="date" wire:model.live="tgl_masuk_tu_dari" title="Dari TGL Masuk TU" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #a7f3d0; font-size: 0.82rem; background: white;">
                        <span style="font-size: 0.8rem; color: #047857;">s/d</span>
                        <input type="date" wire:model.live="tgl_masuk_tu_sampai" title="Sampai TGL Masuk TU" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #a7f3d0; font-size: 0.82rem; background: white;">
                    </div>
                </div>

                <!-- Filter Tanggal Surat -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-color);">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 4px;">
                        <span>📄</span> Tanggal Surat:
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <input type="date" wire:model.live="tanggal_surat_dari" title="Dari Tanggal Surat" style="padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 0.82rem; background: white;">
                        <span style="font-size: 0.8rem; color: var(--text-muted);">s/d</span>
                        <input type="date" wire:model.live="tanggal_surat_sampai" title="Sampai Tanggal Surat" style="padding: 5px 8px; border-radius: 5px; border: 1px solid var(--border-color); font-size: 0.82rem; background: white;">
                    </div>
                </div>

                @if($tanggal_surat_dari || $tanggal_surat_sampai || $tgl_masuk_tu_dari || $tgl_masuk_tu_sampai)
                    <span style="font-size: 0.78rem; color: #059669; background: #ecfdf5; padding: 4px 8px; border-radius: 4px; font-weight: 600; border: 1px solid #a7f3d0;">
                        ✓ Filter Aktif
                    </span>
                @endif
            </div>
        </div>

        @if(count($selectedSurat) > 0)
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 8px; color: #065f46; font-size: 0.9rem;">
                    <span style="font-weight: 700;">✓ {{ count($selectedSurat) }} surat dipilih</span>
                    <span style="color: #64748b;">(Siap di-export ke format Arsip DIB & DB Excel atau dicetak)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" wire:click="openExportDibDbModal" class="btn btn-sm btn-primary" style="background: #059669; border-color: #059669; color: white; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Export Excel Terpilih ({{ count($selectedSurat) }})
                    </button>
                    <button type="button" wire:click="$set('selectedSurat', [])" class="btn btn-sm btn-secondary" style="color: #64748b; font-size: 0.8rem;">
                        Batal Pilih
                    </button>
                </div>
            </div>
        @endif

        @if($surats->count() > 0)
            <div class="table-container" style="overflow-x: auto;">
                <table style="min-width: 1400px;">
                    <thead>
                        <tr>
                            <th style="width: 44px; text-align: center;">
                                <input type="checkbox" wire:model.live="selectAll" style="cursor: pointer; width: 16px; height: 16px; accent-color: #059669;" title="Pilih Semua di Halaman Ini">
                            </th>
                            <th style="width: 60px; text-align: center;">No Urut</th>
                            <th style="width: 110px;">TGL Masuk TU</th>
                            <th style="width: 100px;">Tanggal</th>
                            <th style="width: 120px;">No. Surat</th>
                            <th style="width: 150px;">Tujuan</th>
                            <th style="width: 250px;">Perihal</th>
                          

                            <th style="width: 80px; text-align: center;">Scan</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surats as $surat)
                            <tr wire:key="surat-{{ $surat->id }}" style="{{ in_array((string)$surat->id, array_map('strval', $selectedSurat)) ? 'background-color: #ecfdf5;' : '' }}">
                                <td style="text-align: center;">
                                    <input type="checkbox" wire:model.live="selectedSurat" value="{{ (string)$surat->id }}" style="cursor: pointer; width: 16px; height: 16px; accent-color: #059669;">
                                </td>
                                <td style="text-align: center; font-weight: 700; color: var(--text-secondary);">{{ $surat->no_urut }}</td>
                                <td>
                                    @if($surat->tgl_masuk_tu)
                                        <span style="color: #047857; font-weight: 600;">{{ \Carbon\Carbon::parse($surat->tgl_masuk_tu)->format('d M Y') }}</span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                                    @endif
                                </td>
                                <td>{{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('d M Y') : '-' }}</td>
                                <td style="font-family: monospace; font-size: 0.85rem; font-weight: 600;">{{ $surat->no_surat ?? '-' }}</td>
                                <td style="font-weight: 600; color: var(--text-primary);">{{ $surat->tujuan_surat ?? '-' }}</td>
                                <td>
                                    <div style="font-size:0.9rem; font-weight: 600; color:var(--text-primary); max-width:230px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $surat->perihal }}">{{ $surat->perihal ?? '-' }}</div>
                                </td>
                              
                                <td style="text-align: center;">
                                    @if($surat->dokumen_id)
                                        @php
                                            $attachCount = $surat->dokumen ? $surat->dokumen->fileAttachments->count() : 0;
                                        @endphp
                                        <a href="{{ route('dokumen.show', \Illuminate\Support\Facades\Crypt::encryptString($surat->dokumen_id)) }}" title="Lihat Dokumen & Upload File" style="display:inline-flex; align-items:center; justify-content:center; gap: 4px; padding: 4px 10px; border-radius: 6px; text-decoration:none; font-weight:600; {{ $attachCount > 0 ? 'background: #d1fae5; color: #10b981;' : 'background: #fef2f2; color: #ef4444;' }}">
                                            <i data-lucide="file-text" style="width: 14px; height: 14px;"></i>
                                            @if($attachCount > 0)
                                                <span style="font-size: 0.75rem;">{{ $attachCount }}</span>
                                            @endif
                                        </a>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        @if($surat->dokumen_id)
                                            <a href="{{ route('dokumen.show', \Illuminate\Support\Facades\Crypt::encryptString($surat->dokumen_id)) }}" class="btn btn-sm btn-secondary" style="padding: 4px 8px; color: #10b981;" title="Detail Dokumen">
                                                👁️
                                            </a>
                                        @endif
                                        <a href="/surat-keluar/{{ \Illuminate\Support\Facades\Crypt::encryptString($surat->id) }}/edit" class="btn btn-sm btn-secondary" style="padding: 4px 8px;" title="Edit">
                                            ✏️
                                        </a>
                                        <button wire:click="delete({{ $surat->id }})" wire:confirm="Yakin ingin menghapus data surat ini?" class="btn btn-sm btn-secondary" style="padding: 4px 8px; color: var(--danger);" title="Hapus">
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
                {{ $surats->links('vendor.pagination.custom', data: ['scrollTo' => false]) }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M12 18v-6"/><path d="M9 15l3-3 3 3"/></svg>
                </div>
                <div class="empty-state-text">Belum ada data Surat Keluar</div>
                <div class="empty-state-hint">Silakan klik tombol Tambah Surat Keluar</div>
            </div>
        @endif
    </div>

    <!-- Modal Pilihan Export DIB & DB -->
    @if($showExportDibDbModal)
        @php
            $hasActiveFilter = $search || $tanggal_surat_dari || $tanggal_surat_sampai || $tgl_masuk_tu_dari || $tgl_masuk_tu_sampai;
            $selectedCount = count($selectedSurat);
            $filteredCount = $surats->total();
        @endphp
        <div wire:click.self="closeExportDibDbModal" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px;">
            <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 500px; box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25); overflow: hidden; animation: fadeInModal 0.15s ease-out;">
                <!-- Modal Header -->
                <div style="padding: 18px 22px; background: #ecfdf5; border-bottom: 1px solid #a7f3d0; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #059669; color: white; display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #065f46;">Export Arsip DIB & DB</h3>
                            <p style="margin: 2px 0 0; font-size: 0.78rem; color: #047857;">Pilih cakupan data Surat Keluar yang ingin diunduh</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeExportDibDbModal" style="background: transparent; border: none; color: #065f46; cursor: pointer; padding: 4px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;" title="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div style="padding: 20px 22px; display: flex; flex-direction: column; gap: 12px;">
                    <p style="margin: 0 0 4px; font-size: 0.88rem; color: #475569; line-height: 1.5;">
                        Silakan pilih apakah ingin mengunduh file Excel <strong>DIB & DB</strong> berdasarkan data yang <strong>di-checklist</strong> atau <strong>full semua data</strong>:
                    </p>

                    <!-- Opsi 1: Data Checklist -->
                    @if($selectedCount > 0)
                        <a href="{{ route('surat-keluar.export-dib-db', ['ids' => implode(',', $selectedSurat)]) }}" wire:click="closeExportDibDbModal" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 10px; border: 1.5px solid #059669; background: #ecfdf5; text-decoration: none; transition: all 0.15s;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #059669; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.92rem; font-weight: 700; color: #065f46;">Download Data Checklist</div>
                                    <div style="font-size: 0.8rem; color: #047857; margin-top: 2px;">Hanya mengunduh <strong>{{ $selectedCount }} surat</strong> yang Anda centang</div>
                                </div>
                            </div>
                            <span style="background: #059669; color: white; font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; white-space: nowrap;">{{ $selectedCount }} Data</span>
                        </a>
                    @else
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 10px; border: 1.5px dashed #cbd5e1; background: #f8fafc; opacity: 0.85;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.92rem; font-weight: 700; color: #64748b;">Download Data Checklist</div>
                                    <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">Belum ada data yang di-checklist pada tabel</div>
                                </div>
                            </div>
                            <span style="background: #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 999px; white-space: nowrap;">0 Data</span>
                        </div>
                    @endif

                    <!-- Opsi 2 (Jika ada filter aktif): Sesuai Filter -->
                    @if($hasActiveFilter)
                        <a href="{{ route('surat-keluar.export-dib-db', array_filter(['search' => $search, 'tanggal_surat_dari' => $tanggal_surat_dari, 'tanggal_surat_sampai' => $tanggal_surat_sampai, 'tgl_masuk_tu_dari' => $tgl_masuk_tu_dari, 'tgl_masuk_tu_sampai' => $tgl_masuk_tu_sampai])) }}" wire:click="closeExportDibDbModal" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 10px; border: 1.5px solid #bae6fd; background: #f0f9ff; text-decoration: none; transition: all 0.15s;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 8px; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                </div>
                                <div>
                                    <div style="font-size: 0.92rem; font-weight: 700; color: #0369a1;">Download Sesuai Filter Aktif</div>
                                    <div style="font-size: 0.8rem; color: #0284c7; margin-top: 2px;">Mengunduh seluruh hasil pencarian / filter tanggal saat ini</div>
                                </div>
                            </div>
                            <span style="background: #0284c7; color: white; font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; white-space: nowrap;">{{ number_format($filteredCount) }} Data</span>
                        </a>
                    @endif

                    <!-- Opsi 3: Full Semua Data -->
                    <a href="{{ route('surat-keluar.export-dib-db') }}" wire:click="closeExportDibDbModal" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 10px; border: 1.5px solid #cbd5e1; background: #ffffff; text-decoration: none; transition: all 0.15s;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 8px; background: #1e293b; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.92rem; font-weight: 700; color: #0f172a;">Download Full Semua Data</div>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Mengunduh keseluruhan data Surat Keluar di sistem</div>
                            </div>
                        </div>
                        <span style="background: #1e293b; color: white; font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; white-space: nowrap;">{{ number_format($totalAllSurat) }} Data</span>
                    </a>
                </div>

                <!-- Modal Footer -->
                <div style="padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                    <button type="button" wire:click="closeExportDibDbModal" class="btn btn-secondary" style="font-size: 0.85rem; padding: 7px 16px; cursor: pointer;">
                        Batal
                    </button>
                </div>
            </div>
        </div>
        <style>
            @keyframes fadeInModal {
                from { opacity: 0; transform: scale(0.96); }
                to { opacity: 1; transform: scale(1); }
            }
        </style>
    @endif
</div>
