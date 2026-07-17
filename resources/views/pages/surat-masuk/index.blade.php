<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\SuratMasuk;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Buku Agenda - Surat Masuk')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete(int $id)
    {
        $surat = SuratMasuk::findOrFail($id);
        if ($surat->scan_file) {
            $files = is_array($surat->scan_file) ? $surat->scan_file : [];
            foreach ($files as $file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
            }
        }
        $surat->delete();
        session()->flash('success', 'Surat masuk berhasil dihapus.');
    }

    public function with(): array
    {
        $query = SuratMasuk::with('dokumen.fileAttachments')->orderBy('no_urut', 'desc');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('no_surat', 'like', '%' . $this->search . '%')
                  ->orWhere('perihal', 'like', '%' . $this->search . '%')
                  ->orWhere('asal_surat', 'like', '%' . $this->search . '%')
                  ->orWhere('distribusi', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'surats' => $query->paginate($this->perPage)
        ];
    }
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">Buku Agenda: Surat Masuk</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Kelola dan lacak riwayat surat masuk beserta disposisinya.</p>
        </div>
        
        <div style="display:flex; gap: 8px;">
            <a href="/surat-masuk/create" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Surat Masuk
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #d1fae5; color: #047857; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="display: flex; gap: 12px; align-items: center; width: 100%; max-width: 500px;">
                <select wire:model.live="perPage" style="padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size:0.9rem; background: white; cursor: pointer;">
                    <option value="10">10 Data</option>
                    <option value="25">25 Data</option>
                    <option value="50">50 Data</option>
                    <option value="100">100 Data</option>
                </select>
                <div style="flex: 1; position: relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--text-muted);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No Surat, Perihal, Asal..." style="padding: 8px 12px 8px 32px; border-radius: 6px; border: 1px solid var(--border-color); width: 100%; font-size:0.9rem; outline: none;">
                </div>
            </div>
        </div>

        @if($surats->count() > 0)
            <div class="table-container" style="overflow-x: auto;">
                <table style="min-width: 1500px;">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">No Urut</th>
                            <th style="width: 100px;">Tanggal</th>
                            <th style="width: 150px;">No. Surat</th>
                            <th style="width: 250px;">Perihal</th>
                            <th style="width: 150px;">Asal Surat</th>
                            <th style="width: 100px;">Sifat</th>
                            <th style="width: 100px;">Tgl Masuk</th>
                            <th style="width: 150px;">Distribusi</th>
                            <th style="width: 150px;">Disposisi</th>
                            <th style="width: 80px; text-align: center;">Scan</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($surats as $surat)
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: var(--text-secondary);">{{ $surat->no_urut }}</td>
                                <td>{{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('d M Y') : '-' }}</td>
                                <td style="font-family: monospace; font-size: 0.85rem; font-weight: 600;">{{ $surat->no_surat ?? '-' }}</td>
                                <td>
                                    <div style="font-size:0.9rem; font-weight: 600; color:var(--text-primary); max-width:230px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $surat->perihal }}">{{ $surat->perihal ?? '-' }}</div>
                                </td>
                                <td>{{ $surat->asal_surat ?? '-' }}</td>
                                <td>
                                    @if($surat->sifat_surat)
                                        <span class="badge" style="background: #e0f2fe; color: #0284c7;">{{ $surat->sifat_surat }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($surat->tgl_masuk)
                                        <span style="color:var(--success); font-weight: 600;">{{ \Carbon\Carbon::parse($surat->tgl_masuk)->format('d/m/Y') }}</span>
                                    @else
                                        <span style="color:var(--danger); font-size: 0.8rem;">Belum Masuk</span>
                                    @endif
                                </td>
                                <td>{{ $surat->distribusi ?? '-' }}</td>
                                <td>
                                    <div style="max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size: 0.85rem;" title="{{ $surat->formatted_disposisi }}">{{ $surat->formatted_disposisi }}</div>
                                </td>
                                <td style="text-align: center;">
                                    @if($surat->dokumen_id)
                                        @php
                                            $attachCount = $surat->dokumen ? $surat->dokumen->fileAttachments->count() : 0;
                                        @endphp
                                        <a href="{{ route('dokumen.show', $surat->dokumen_id) }}" title="Lihat Dokumen & Upload File" style="display:inline-flex; align-items:center; justify-content:center; gap: 4px; padding: 4px 10px; border-radius: 6px; text-decoration:none; font-weight:600; {{ $attachCount > 0 ? 'background: #d1fae5; color: #10b981;' : 'background: #fef2f2; color: #ef4444;' }}">
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
                                            <a href="{{ route('dokumen.show', $surat->dokumen_id) }}" class="btn btn-sm btn-secondary" style="padding: 4px 8px; color: #10b981;" title="Detail Dokumen">
                                                👁️
                                            </a>
                                        @endif
                                        <a href="/surat-masuk/{{ $surat->id }}/disposisi" target="_blank" class="btn btn-sm btn-secondary" style="padding: 4px 8px; color: #0284c7;" title="Cetak Disposisi">
                                            🖨️
                                        </a>
                                        <a href="/surat-masuk/{{ $surat->id }}/edit" class="btn btn-sm btn-secondary" style="padding: 4px 8px;" title="Edit">
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="empty-state-text">Belum ada data Surat Masuk</div>
                <div class="empty-state-hint">Silakan klik tombol Tambah Surat Masuk</div>
            </div>
        @endif
    </div>
</div>
