<?php

use Livewire\Volt\Component;
use App\Models\Dokumen;

new #[\Livewire\Attributes\Layout('layouts.app')] class extends Component {
    use \Livewire\WithFileUploads;

    public Dokumen $dokumen;
    public $newFiles = [];
    public bool $showUploadForm = false;

    public function title(): string
    {
        return $this->dokumen->judul;
    }

    public function mount(Dokumen $dokumen)
    {
        $this->dokumen = $dokumen->load(['fileAttachments', 'uploader', 'kategori.bundle']);
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

        $this->dokumen->load('fileAttachments');
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
        $this->dokumen->load('fileAttachments');
        session()->flash('success', 'File tambahan berhasil diupload.');
    }
}; ?>
<div>
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
            <a href="{{ route('dokumen.print', $dokumen) }}" target="_blank" class="btn btn-secondary">
                🖨️ Cetak
            </a>
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
            <span class="badge badge-success">{{ $dokumen->fileAttachments->count() }} file</span>
        </div>
    </div>

    <!-- File Attachments -->
    <div class="flex items-center justify-between mb-4">
        <h2 style="font-size: 1.25rem; font-weight: 700;">
            📎 File Lampiran ({{ $dokumen->fileAttachments->count() }})
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

    @if($dokumen->fileAttachments->count() > 0)
        <div class="file-preview-grid">
            @foreach($dokumen->fileAttachments as $file)
                <div class="file-preview-card">
                    <!-- Thumbnail -->
                    <div class="file-preview-thumb">
                        @if($file->is_image)
                            <img src="{{ route('file.preview', $file) }}" alt="{{ $file->nama_file }}" loading="lazy">
                        @elseif($file->is_pdf)
                            <span>📄</span>
                        @else
                            <span>📎</span>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="file-preview-info">
                        <div class="file-preview-name" title="{{ $file->nama_file }}">{{ $file->nama_file }}</div>
                        <div class="file-preview-size">
                            {{ $file->ukuran_format }} · {{ strtoupper($file->extension) }}
                        </div>
                        <div class="file-preview-actions">
                            @if($file->disk === 'url')
                                <a href="{{ $file->path }}" target="_blank" class="btn btn-sm btn-secondary" style="flex: 1; justify-content: center;">
                                    🌐 Buka Tautan Google Drive
                                </a>
                            @else
                                <a href="{{ route('file.preview', $file) }}" target="_blank" class="btn btn-sm btn-secondary" style="flex: 1; justify-content: center;">
                                    👁️ Preview
                                </a>
                                <a href="{{ route('file.download', $file) }}" class="btn btn-sm btn-primary" style="flex: 1; justify-content: center;">
                                    ⬇️ Download
                                </a>
                            @endif
                        </div>
                        @if(auth()->user()->isAdmin())
                            <button wire:click="hapusFile({{ $file->id }})"
                                    wire:confirm="Yakin ingin menghapus file '{{ $file->nama_file }}'?"
                                    class="btn btn-sm btn-danger mt-2" style="width: 100%; justify-content: center;">
                                🗑️ Hapus
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
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
