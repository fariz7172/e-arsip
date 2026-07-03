<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Bundle;
use App\Models\Kategori;
use App\Models\Dokumen;
use App\Models\FileAttachment;

new #[\Livewire\Attributes\Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public Bundle $bundle;
    public Kategori $kategori;

    public function title(): string
    {
        return $this->kategori->nama;
    }

    // Document form
    public bool $showForm = false;
    public string $judul = '';
    public string $nomorDokumen = '';
    public ?string $tanggalDokumen = null;
    public string $keterangan = '';
    public $files = [];

    // Edit Document state
    public ?int $editDokumenId = null;
    public string $editJudul = '';
    public string $editNomorDokumen = '';
    public ?string $editTanggalDokumen = null;
    public string $editKeterangan = '';

    public function mount(Bundle $bundle, Kategori $kategori)
    {
        $this->bundle = $bundle;
        $this->kategori = $kategori;
    }

    public function with(): array
    {
        $dokumens = Dokumen::where('kategori_id', $this->kategori->id)
            ->with(['fileAttachments', 'uploader'])
            ->latest()
            ->get();

        return compact('dokumens');
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        $this->resetForm();
    }

    public function simpanDokumen()
    {
        if (!auth()->user()->canManage()) {
            session()->flash('error', 'Anda tidak memiliki akses.');
            return;
        }

        $this->validate([
            'judul' => 'required|string|max:255',
            'nomorDokumen' => 'nullable|string|max:100',
            'tanggalDokumen' => 'nullable|date',
            'keterangan' => 'nullable|string|max:1000',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240',
        ], [
            'files.required' => 'Minimal upload 1 file.',
            'files.min' => 'Minimal upload 1 file.',
            'files.*.mimes' => 'File harus berformat PDF, JPG, PNG, GIF, atau WebP.',
            'files.*.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $dokumen = Dokumen::create([
            'kategori_id' => $this->kategori->id,
            'judul' => $this->judul,
            'nomor_dokumen' => $this->nomorDokumen ?: null,
            'tanggal_dokumen' => $this->tanggalDokumen ?: null,
            'keterangan' => $this->keterangan ?: null,
            'uploaded_by' => auth()->id(),
        ]);

        foreach ($this->files as $file) {
            // Kita simpan metadata file DULU sebelum memanggil store()
            // karena store() akan memindahkan/menghapus file temporary Livewire
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Simpan file
            $path = $file->store(
                "arsip/{$this->bundle->id}/{$this->kategori->id}",
                'local'
            );

            FileAttachment::create([
                'dokumen_id' => $dokumen->id,
                'nama_file' => $originalName,
                'path' => $path,
                'disk' => 'local',
                'mime_type' => $mimeType,
                'ukuran' => $size,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;

        session()->flash('success', "Dokumen '{$dokumen->judul}' berhasil disimpan dengan " . count($this->files) . " file!");
    }

    public function hapusDokumen(int $id)
    {
        if (!auth()->user()->isAdmin()) {
            session()->flash('error', 'Hanya admin yang dapat menghapus dokumen.');
            return;
        }

        $dokumen = Dokumen::findOrFail($id);
        $nama = $dokumen->judul;

        // Delete files from storage
        foreach ($dokumen->fileAttachments as $file) {
            \Illuminate\Support\Facades\Storage::disk($file->disk)->delete($file->path);
        }

        $dokumen->forceDelete();
        session()->flash('success', "Dokumen '{$nama}' berhasil dihapus.");
    }

    private function resetForm()
    {
        $this->judul = '';
        $this->nomorDokumen = '';
        $this->tanggalDokumen = null;
        $this->keterangan = '';
        $this->files = [];
    }

    public function editDokumen(int $id)
    {
        if (!auth()->user()->canManage()) return;

        $dokumen = Dokumen::findOrFail($id);
        $this->editDokumenId = $dokumen->id;
        $this->editJudul = $dokumen->judul;
        $this->editNomorDokumen = $dokumen->nomor_dokumen ?? '';
        $this->editTanggalDokumen = $dokumen->tanggal_dokumen ? $dokumen->tanggal_dokumen->format('Y-m-d') : null;
        $this->editKeterangan = $dokumen->keterangan ?? '';
    }

    public function cancelEditDokumen()
    {
        $this->editDokumenId = null;
    }

    public function updateDokumen()
    {
        if (!auth()->user()->canManage() || !$this->editDokumenId) return;

        $this->validate([
            'editJudul' => 'required|string|max:255',
            'editNomorDokumen' => 'nullable|string|max:100',
            'editTanggalDokumen' => 'nullable|date',
            'editKeterangan' => 'nullable|string|max:1000',
        ]);

        $dokumen = Dokumen::findOrFail($this->editDokumenId);
        $dokumen->update([
            'judul' => $this->editJudul,
            'nomor_dokumen' => $this->editNomorDokumen ?: null,
            'tanggal_dokumen' => $this->editTanggalDokumen ?: null,
            'keterangan' => $this->editKeterangan ?: null,
        ]);

        $this->editDokumenId = null;
        session()->flash('success', "Dokumen '{$this->editJudul}' berhasil diperbarui.");
    }
}; ?>
<div>
<div class="breadcrumb">
        <a href="/dashboard">Dashboard</a>
        <span class="separator">/</span>
        <a href="/bundles">Bundle</a>
        <span class="separator">/</span>
        <a href="/bundles/{{ $bundle->id }}">{{ $bundle->nama }}</a>
        <span class="separator">/</span>
        <span class="current">{{ $kategori->nama }}</span>
    </div>

    <!-- Kategori Header -->
    <div class="card mb-6">
        <div class="flex items-center justify-between" style="flex-wrap: wrap; gap: 16px;">
            <div class="flex items-center gap-3">
                <span style="font-size: 2rem;">📂</span>
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 800;">{{ $kategori->nama }}</h1>
                    <div style="color: var(--text-muted); font-size: 0.85rem;">
                        Bundle: {{ $bundle->nama }} · {{ $bundle->kode }}
                    </div>
                </div>
            </div>
            @if(auth()->user()->canManage())
                <button wire:click="toggleForm" class="btn btn-primary">
                    {{ $showForm ? '✕ Tutup Form' : '📤 Upload Dokumen' }}
                </button>
            @endif
        </div>
    </div>

    <!-- Upload Form -->
    @if($showForm)
        <div class="card mb-6" style="border-color: var(--accent); animation: slideDown 0.3s ease;">
            <h3 style="font-weight: 700; margin-bottom: 20px;">📤 Upload Dokumen Baru</h3>

            <form wire:submit="simpanDokumen">
                <div class="form-group">
                    <label class="form-label">Judul Dokumen *</label>
                    <input type="text" wire:model="judul" class="form-input"
                           placeholder="contoh: Laporan SDA Q1 2026" autofocus>
                    @error('judul') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nomor Dokumen</label>
                        <input type="text" wire:model="nomorDokumen" class="form-input"
                               placeholder="contoh: DOK/SDA/001/2026">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Dokumen</label>
                        <input type="date" wire:model="tanggalDokumen" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea wire:model="keterangan" class="form-textarea" rows="3"
                              placeholder="Catatan atau keterangan tambahan..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">File Lampiran * (PDF / Gambar, maks. 10MB)</label>
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
                                
                                $wire.uploadMultiple('files', processedFiles,
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
                        <div class="file-upload-hint">PDF, JPG, PNG, GIF, WebP — Maks. 10MB per file</div>
                        
                        <button type="button" @click="$refs.fileInput.click()" class="btn btn-secondary mt-3" style="width: 100%; justify-content: center;">
                            Pilih File dari Perangkat
                        </button>
                        
                        <div x-show="isCompressing" style="margin-top: 12px; color: var(--accent); font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <div class="loading-spinner"></div>
                            <span>Mengecilkan ukuran gambar sebelum upload...</span>
                        </div>
                    </div>

                    @error('files') <div class="form-error mt-2">{{ $message }}</div> @enderror
                    @error('files.*') <div class="form-error mt-2">{{ $message }}</div> @enderror

                    <!-- Upload progress -->
                    <div wire:loading wire:target="files" style="margin-top: 8px;">
                        <div class="flex items-center gap-2" style="color: var(--accent);">
                            <div class="loading-spinner"></div>
                            <span style="font-size: 0.85rem;">Mengupload file...</span>
                        </div>
                    </div>

                    <!-- File preview list -->
                    @if($files && count($files) > 0)
                        <div style="margin-top: 12px;" wire:loading.remove wire:target="files">
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px;">
                                {{ count($files) }} file siap diupload:
                            </div>
                            <div class="file-chips">
                                @foreach($files as $file)
                                    <div class="file-chip">
                                        @if(str_starts_with($file->getMimeType(), 'image/'))
                                            🖼️
                                        @else
                                            📄
                                        @endif
                                        {{ Str::limit($file->getClientOriginalName(), 30) }}
                                        <span style="color: var(--text-muted);">
                                            ({{ number_format($file->getSize() / 1024, 0) }} KB)
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="simpanDokumen">💾 Simpan Dokumen</span>
                        <span wire:loading wire:target="simpanDokumen">Menyimpan...</span>
                    </button>
                    <button type="button" wire:click="toggleForm" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Documents List -->
    <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 16px;">
        Daftar Dokumen ({{ $dokumens->count() }})
    </h2>

    @if($dokumens->count() > 0)
        @foreach($dokumens as $dokumen)
            <div class="dokumen-item">
                @if($editDokumenId === $dokumen->id)
                    <div style="padding: 16px; background: var(--bg-primary); border-radius: var(--radius-sm); border: 1px solid var(--accent);">
                        <h4 style="margin-bottom: 12px; font-weight: 700; color: var(--accent);">✏️ Edit Dokumen</h4>
                        <form wire:submit="updateDokumen">
                            <div class="form-group mb-2">
                                <label class="form-label">Judul Dokumen *</label>
                                <input type="text" wire:model="editJudul" class="form-input" required>
                            </div>
                            <div class="form-row mb-2">
                                <div class="form-group mb-0">
                                    <label class="form-label">Nomor Dokumen</label>
                                    <input type="text" wire:model="editNomorDokumen" class="form-input">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label">Tanggal Dokumen</label>
                                    <input type="date" wire:model="editTanggalDokumen" class="form-input">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea wire:model="editKeterangan" class="form-textarea" rows="2"></textarea>
                            </div>
                            <div class="flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Simpan Perubahan</button>
                                <button type="button" wire:click="cancelEditDokumen" class="btn btn-secondary btn-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                @else
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
                        <div class="flex items-center gap-2">
                            @if(auth()->user()->canManage())
                                <button wire:click="editDokumen({{ $dokumen->id }})" class="btn btn-sm btn-secondary" title="Edit Dokumen">✏️</button>
                            @endif
                            <a href="/dokumen/{{ $dokumen->id }}" class="btn btn-sm btn-secondary">Detail →</a>
                            @if(auth()->user()->isAdmin())
                                <button wire:click="hapusDokumen({{ $dokumen->id }})"
                                        wire:confirm="Yakin ingin menghapus dokumen '{{ $dokumen->judul }}'? File juga akan dihapus."
                                        class="btn btn-sm btn-danger">🗑️</button>
                            @endif
                        </div>
                    </div>

                    @if($dokumen->keterangan)
                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 8px;">
                            {{ Str::limit($dokumen->keterangan, 120) }}
                        </p>
                    @endif

                    <!-- File chips -->
                    <div class="file-chips">
                        @foreach($dokumen->fileAttachments as $file)
                            <a href="{{ route('file.preview', $file) }}" target="_blank" class="file-chip">
                                @if($file->is_image)
                                    🖼️
                                @elseif($file->is_pdf)
                                    📄
                                @else
                                    📎
                                @endif
                                {{ Str::limit($file->nama_file, 25) }}
                                <span style="color: var(--text-muted);">({{ $file->ukuran_format }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">📄</div>
                <div class="empty-state-text">Belum ada dokumen dalam kategori ini</div>
                <div class="empty-state-hint">Upload dokumen pertama Anda dengan tombol di atas</div>
            </div>
        </div>
    @endif

    <!-- Back Button -->
    <div style="margin-top: 24px;">
        <a href="/bundles/{{ $bundle->id }}" class="btn btn-secondary">
            ← Kembali ke Bundle {{ $bundle->nama }}
        </a>
    </div>

</div>
