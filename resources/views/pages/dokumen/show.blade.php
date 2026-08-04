<?php

use Livewire\Volt\Component;
use App\Models\Dokumen;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[\Livewire\Attributes\Layout('layouts.app')] class extends Component {
    use WithFileUploads, WithPagination;

    public Dokumen $dokumen;
    public $newFiles = [];
    public bool $showUploadForm = false;

    public function title(): string
    {
        return $this->dokumen->judul;
    }

    public function mount(Dokumen $dokumen)
    {
        $this->dokumen = $dokumen->load(['uploader', 'kategori.bundle']);
    }

    public function with(): array
    {
        return [
            'paginatedPayments' => \App\Models\Payment::where('dokumen_id', $this->dokumen->id)
                                              ->paginate(9, ['*'], 'paymentsPage'),
                                              
            'paginatedFiles' => \App\Models\FileAttachment::where('dokumen_id', $this->dokumen->id)
                                              ->latest()
                                              ->paginate(9, ['*'], 'filesPage'),
        ];
    }

    public function hapusFile(int $fileId)
    {
        if (!auth()->user()->isAdmin()) {
            session()->flash('error', 'Hanya admin yang dapat menghapus file.');
            return;
        }

        $file = \App\Models\FileAttachment::findOrFail($fileId);
        \Illuminate\Support\Facades\Storage::disk($file->disk)->delete($file->path);
        $file->delete();

        session()->flash('success', 'File berhasil dihapus.');
    }

    public function toggleUploadForm()
    {
        $this->showUploadForm = !$this->showUploadForm;
        $this->newFiles = [];
    }

    public function simpanFileTambahan()
    {
        if (!auth()->user()->canManage()) return;

        $this->validate([
            'newFiles' => 'required|array|min:1',
            'newFiles.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,webp|max:51200',
        ]);

        $bundleId = $this->dokumen->kategori->bundle_id;
        $kategoriId = $this->dokumen->kategori_id;

        foreach ($this->newFiles as $file) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            $path = $file->store("arsip/{$bundleId}/{$kategoriId}", 'local');

            \App\Models\FileAttachment::create([
                'dokumen_id' => $this->dokumen->id,
                'nama_file' => $originalName,
                'path' => $path,
                'disk' => 'local',
                'mime_type' => $mimeType,
                'ukuran' => $size,
            ]);
        }

        $this->newFiles = [];
        $this->showUploadForm = false;
        session()->flash('success', 'File tambahan berhasil diupload.');
    }
}; ?>
<div>
<div x-data="{ showPaymentModal: false }">
    <div class="breadcrumb">
        <a href="/dashboard">Dashboard</a>
        <span class="separator">/</span>
        <a href="/bundles">Bundle</a>
        <span class="separator">/</span>
        <a href="/bundles/{{ $dokumen->kategori->bundle->id }}">{{ $dokumen->kategori->bundle->nama }}</a>
        <span class="separator">/</span>
        <a href="/bundles/{{ $dokumen->kategori->bundle->id }}/kategori/{{ $dokumen->kategori->id }}">{{ $dokumen->kategori->nama }}</a>
        <span class="separator">/</span>
        <span class="current">{{ Str::limit($dokumen->judul, 30) }}</span>
    </div>

    <!-- Document Info -->
    <div class="card mb-6">
        <div class="flex items-center justify-between" style="flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
            <div class="flex items-center gap-3">
                <span style="font-size: 2rem;">📄</span>
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 800;">{{ $dokumen->judul }}</h1>
                    <div style="color: var(--text-muted); font-size: 0.85rem; display: flex; gap: 16px; margin-top: 4px; flex-wrap: wrap;">
                        @if($dokumen->nomor_dokumen)
                            <span>📋 {{ $dokumen->nomor_dokumen }}</span>
                        @endif
                        @if($dokumen->tanggal_dokumen)
                            <span>📅 {{ $dokumen->tanggal_dokumen->format('d F Y') }}</span>
                        @endif
                        <span>👤 {{ $dokumen->uploader?->name }}</span>
                        <span>🕐 {{ $dokumen->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('dokumen.print', $dokumen) }}" target="_blank" class="btn btn-secondary">
                    🖨️ Cetak
                </a>
            </div>
        </div>

        @if($dokumen->keterangan)
            <div style="background: var(--bg-primary); border-radius: var(--radius-md); padding: 16px; margin-top: 12px;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 6px;">Keterangan</div>
                <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.7;">{{ $dokumen->keterangan }}</p>
            </div>
        @endif

        <div style="display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap;">
            <span class="badge badge-accent">{{ $dokumen->kategori->bundle->nama }}</span>
            <span class="badge badge-info">{{ $dokumen->kategori->nama }}</span>
            <span class="badge badge-success">{{ $paginatedFiles->total() }} file</span>
        </div>
    </div>

    </div>



    @if($paginatedPayments && $paginatedPayments->count() > 0)
        <div class="card mb-6" style="border-color: var(--primary); animation: slideDown 0.3s ease;">
            <h3 style="font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                💳 Data Pembayaran Terkait ({{ $paginatedPayments->total() }})
            </h3>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($paginatedPayments as $index => $payment)
                    <div x-data="{ showModal: false }" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;">{{ $payment->keperluan ? Str::limit($payment->keperluan, 80) : 'Data Pembayaran #'.($index+1) }}</div>
                            <div style="font-size: 0.8rem; color: #64748b; display: flex; gap: 12px; flex-wrap: wrap;">
                                <span><strong>SPM:</strong> {{ $payment->no_spm ?: '-' }}</span>
                                <span><strong>Nilai:</strong> Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</span>
                                <span><strong>Vendor:</strong> {{ optional($payment->vendor)->nama_perusahaan ?: '-' }}</span>
                            </div>
                        </div>
                        <button type="button" @click="showModal = true" class="btn btn-primary btn-sm" style="white-space: nowrap;">
                            Lihat Detail
                        </button>

                        <!-- Modal (Non-Teleported) -->
                        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;" x-transition>
                            <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden flex flex-col" style="background: white; border-radius: 16px; max-width: 800px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;">
                                <!-- Header -->
                                <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between" style="padding: 24px 32px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                                    <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 16px;">
                                        <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center" style="width: 48px; height: 48px; background: var(--accent-glow); color: var(--accent); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-black text-slate-800" style="margin: 0; font-size: 1.25rem; font-weight: 900; color: #1e293b;">Detail Pembayaran</h2>
                                            <p class="text-xs text-slate-400 uppercase tracking-widest font-bold" style="margin: 0; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700;">Data SPM, Kontrak, & Vendor</p>
                                        </div>
                                    </div>
                                    <button @click="showModal = false" class="p-2 hover:bg-rose-50 text-slate-400 hover:text-rose-500 rounded-xl transition-all" style="background: transparent; border: none; cursor: pointer; color: #94a3b8; padding: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                                <!-- Content -->
                                <div class="flex-1 overflow-y-auto p-8 custom-scrollbar" style="padding: 32px; overflow-y: auto; flex: 1; text-align: left;">
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                                        
                                        <div style="display: flex; flex-direction: column; gap: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; color: var(--primary);">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                                                <h4 style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">I. Data Anggaran</h4>
                                            </div>
                                            <div style="background: #f8fafc; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px; border: 1px solid #f1f5f9;">
                                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                                                    <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">No. SPD</span>
                                                    <span style="font-size: 0.75rem; font-weight: 900; color: #334155;">{{ $payment->no_spd ?: '-' }}</span>
                                                </div>
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">Program</span>
                                                    <span style="font-size: 0.95rem; font-weight: 700; color: #1e293b;">{{ $payment->program ?: '-' }}</span>
                                                </div>
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">Kegiatan</span>
                                                    <span style="font-size: 0.75rem; color: #475569;">{{ $payment->kegiatan ?: '-' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 16px;">
                                            <div style="display: flex; align-items: center; gap: 8px; color: #10b981;">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                <h4 style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">II. Kontrak & Nilai</h4>
                                            </div>
                                            <div style="background: rgba(236, 253, 245, 0.3); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 12px; border: 1px solid rgba(209, 250, 229, 0.5);">
                                                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(167, 243, 208, 0.3); padding-bottom: 8px;">
                                                    <span style="font-size: 0.75rem; color: rgba(5, 150, 105, 0.7); font-weight: 700;">No. Kontrak</span>
                                                    <span style="font-size: 0.75rem; font-weight: 900; color: #047857;">{{ optional($payment->contract)->nomor_kontrak ?: '-' }}</span>
                                                </div>
                                                <div style="display: flex; justify-content: space-between;">
                                                    <span style="font-size: 0.75rem; color: rgba(5, 150, 105, 0.7); font-weight: 700;">Nilai Pembayaran</span>
                                                    <span style="font-size: 0.95rem; font-weight: 900; color: #059669;">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                            <div style="display: flex; align-items: center; gap: 8px; color: #3b82f6;">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                <h4 style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">III. Uraian Pembayaran</h4>
                                            </div>
                                            <div style="background: rgba(239, 246, 255, 0.3); border-radius: 16px; padding: 20px; border: 1px solid rgba(219, 234, 254, 0.5);">
                                                <p style="font-size: 0.85rem; color: #334155; line-height: 1.6; white-space: pre-line; margin: 0;">{{ $payment->keperluan ?: '-' }}</p>
                                            </div>
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                            <div style="display: flex; align-items: center; gap: 8px; color: #6366f1;">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                                <h4 style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">IV. Data SPP/SPM/SP2D</h4>
                                            </div>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px;">
                                                <div style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                    <p style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">No. SPM</p>
                                                    <p style="font-size: 0.8rem; font-weight: 900; color: #334155; margin: 0;">{{ $payment->no_spm ?: '-' }}</p>
                                                </div>
                                                <div style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                    <p style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">No. SPP</p>
                                                    <p style="font-size: 0.8rem; font-weight: 900; color: #334155; margin: 0;">{{ $payment->no_spp ?: '-' }}</p>
                                                </div>
                                                <div style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                    <p style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">No. KWI</p>
                                                    <p style="font-size: 0.8rem; font-weight: 900; color: #334155; margin: 0;">{{ $payment->no_kwi ?: '-' }}</p>
                                                </div>
                                                <div style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px;">
                                                    <p style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">No. SP2D</p>
                                                    <p style="font-size: 0.8rem; font-weight: 900; color: #334155; margin: 0;">{{ $payment->no_sp2d ?: '-' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 16px; grid-column: 1 / -1;">
                                            <div style="display: flex; align-items: center; gap: 8px; color: #a855f7;">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                                <h4 style="margin: 0; font-size: 0.875rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">V. Informasi Vendor</h4>
                                            </div>
                                            <div style="background: rgba(250, 245, 255, 0.3); border-radius: 16px; padding: 20px; border: 1px solid rgba(233, 213, 255, 0.5);">
                                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                                    <span style="font-size: 0.75rem; color: #c084fc; font-weight: 700;">Perusahaan / Vendor</span>
                                                    <span style="font-size: 1.1rem; font-weight: 900; color: #581c87;">{{ optional($payment->vendor)->nama_perusahaan ?: '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($paginatedPayments->hasPages())
                <div class="mt-6">
                    {{ $paginatedPayments->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- File Attachments -->
    <div class="flex items-center justify-between mb-4">
        <h2 style="font-size: 1.25rem; font-weight: 700;">
            📎 File Lampiran ({{ $paginatedFiles->total() }})
        </h2>
        @if(auth()->user()->canManage())
            <button wire:click="toggleUploadForm" class="btn btn-sm btn-primary">
                {{ $showUploadForm ? '✕ Tutup Form' : '➕ Tambah File' }}
            </button>
        @endif
    </div>

    @if($showUploadForm)
        <div class="card mb-6" style="border-color: var(--accent); animation: slideDown 0.3s ease;">
            <h3 style="font-weight: 700; margin-bottom: 16px;">📤 Upload File Tambahan</h3>
            <form wire:submit="simpanFileTambahan">
                <div class="form-group">
                    <label class="form-label">Pilih File (PDF / Gambar, maks. 50MB)</label>
                    <div class="file-upload-area" 
                         x-data="{
                            isCompressing: false,
                            async handleFiles(files) {
                                if (!files || files.length === 0) return;
                                
                                this.isCompressing = true;
                                let processedFiles = [];
                                
                                for (let i = 0; i < files.length; i++) {
                                    let file = files[i];
                                    if (file.type.startsWith('image/') && file.type !== 'image/gif') {
                                        let compressed = await new Promise((resolve) => {
                                            new Compressor(file, {
                                                quality: 0.8,
                                                maxWidth: 1920,
                                                mimeType: 'image/webp',
                                                success(result) {
                                                    let newName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                                                    if (!newName.includes('.')) newName = file.name + '.webp';
                                                    
                                                    resolve(new File([result], newName, { type: 'image/webp' }));
                                                },
                                                error(err) {
                                                    console.error('Compression error:', err);
                                                    resolve(file);
                                                }
                                            });
                                        });
                                        processedFiles.push(compressed);
                                    } else {
                                        processedFiles.push(file);
                                    }
                                }
                                
                                $wire.uploadMultiple('newFiles', processedFiles,
                                    () => { this.isCompressing = false; },
                                    () => { this.isCompressing = false; }
                                );
                            }
                         }"
                         @dragover.prevent=""
                         @drop.prevent="handleFiles($event.dataTransfer.files)">
                        
                        <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" 
                               @change="handleFiles($event.target.files)" 
                               style="display: none;" x-ref="fileInput">
                        
                        <div class="file-upload-icon">📎</div>
                        <div class="file-upload-text">Klik atau drag file ke area ini</div>
                        
                        <button type="button" @click="$refs.fileInput.click()" class="btn btn-secondary mt-3" style="width: 100%; justify-content: center;">
                            Pilih File
                        </button>
                        
                        <div x-show="isCompressing" style="margin-top: 12px; color: var(--accent); font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <div class="loading-spinner"></div>
                            <span>Mengecilkan ukuran gambar...</span>
                        </div>
                    </div>

                    @error('newFiles') <div class="form-error mt-2">{{ $message }}</div> @enderror
                    @error('newFiles.*') <div class="form-error mt-2">{{ $message }}</div> @enderror

                    <div wire:loading wire:target="newFiles" style="margin-top: 8px;">
                        <div class="flex items-center gap-2" style="color: var(--accent);">
                            <div class="loading-spinner"></div>
                            <span style="font-size: 0.85rem;">Mengupload...</span>
                        </div>
                    </div>

                    @if($newFiles && count($newFiles) > 0)
                        <div style="margin-top: 12px;" wire:loading.remove wire:target="newFiles">
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                {{ count($newFiles) }} file siap ditambahkan:
                            </div>
                            <div class="file-chips">
                                @foreach($newFiles as $file)
                                    <div class="file-chip">
                                        {{ Str::limit($file->getClientOriginalName(), 30) }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="simpanFileTambahan">💾 Simpan File</span>
                        <span wire:loading wire:target="simpanFileTambahan">Menyimpan...</span>
                    </button>
                    <button type="button" wire:click="toggleUploadForm" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    @endif

    @if($paginatedFiles->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            @foreach($paginatedFiles as $file)
                <div style="background: white; border: 1px solid #f1f5f9; padding: 16px; border-radius: 16px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; gap: 12px; transition: all 0.2s;" onmouseover="this.style.borderColor='#fde68a'; this.style.backgroundColor='#fffbeb';" onmouseout="this.style.borderColor='#f1f5f9'; this.style.backgroundColor='white';">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.25rem;">{{ $file->is_pdf ? '📄' : ($file->is_image ? '🖼️' : '📎') }}</span>
                            <p style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 0;">{{ $file->is_pdf ? 'PDF' : ($file->is_image ? 'Gambar' : 'File') }}</p>
                        </div>
                        <span style="font-size: 0.65rem; font-weight: 700; color: #94a3b8;">{{ $file->ukuran_file ? number_format($file->ukuran_file / 1024, 2) . ' KB' : '' }}</span>
                    </div>
                    <p style="font-size: 0.875rem; font-weight: 900; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0;" title="{{ $file->nama_file }}">{{ $file->nama_file }}</p>
                    <div style="display: flex; gap: 8px; margin-top: auto;">
                        @if($file->disk === 'url')
                            <a href="{{ $file->path }}" target="_blank" class="btn btn-sm btn-secondary" style="flex: 1; text-align: center; padding: 6px; font-size: 0.75rem;">
                                🌐 Tautan Drive
                            </a>
                        @else
                            <a href="{{ route('file.preview', $file) }}" target="_blank" class="btn btn-sm btn-secondary" style="flex: 1; text-align: center; padding: 6px; font-size: 0.75rem;">
                                👁️ Lihat
                            </a>
                            <a href="{{ route('file.download', $file) }}" class="btn btn-sm btn-primary" style="flex: 1; text-align: center; padding: 6px; font-size: 0.75rem;">
                                ⬇️ Unduh
                            </a>
                        @endif
                    </div>
                    @if(auth()->user()->isAdmin())
                        <button wire:click="hapusFile({{ $file->id }})"
                                wire:confirm="Yakin ingin menghapus file '{{ $file->nama_file }}'?"
                                class="btn btn-sm btn-danger mt-1" style="width: 100%; justify-content: center; font-size: 0.75rem; padding: 6px;">
                            🗑️ Hapus
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
        
        @if($paginatedFiles->hasPages())
            <div class="mt-6">
                {{ $paginatedFiles->links() }}
            </div>
        @endif
    @else
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">📎</div>
                <div class="empty-state-text">Tidak ada file lampiran</div>
            </div>
        </div>
    @endif

    <!-- Back Button -->
    <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="/bundles/{{ $dokumen->kategori->bundle->id }}/detail" 
           class="btn btn-secondary">← Kembali ke Bundle Detail</a>
        <a href="/bundles/{{ $dokumen->kategori->bundle->id }}/kategori/{{ $dokumen->kategori->id }}" 
           class="btn btn-secondary">← Kembali ke {{ $dokumen->kategori->nama }}</a>
    </div>
</div>
</div>
