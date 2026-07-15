<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\SuratMasuk;
use App\Models\Bundle;
use Illuminate\Support\Facades\Storage;

new #[\Livewire\Attributes\Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public ?SuratMasuk $surat = null;

    public $no_urut;
    public $tanggal;
    public $no_surat;
    public $perihal;
    public $asal_surat;
    public $tanggal_acara;
    public $waktu_acara;
    public $tempat_acara;
    public $tgl_masuk;
    public $tgl_keluar;
    public $tgl_dikembalikan;
    public $distribusi;
    public $disposisi_checkboxes = [];
    public $disposisi_custom = '';
    public $sifat_surat;
    public $keterangan;
    public $bundle_id;
    public $searchBundle = '';
    
    public $scan_files = []; // Untuk upload file baru (multiple)
    public $existing_scans = []; // Menampilkan file scan yang sudah ada

    public function title(): string
    {
        return $this->surat ? 'Edit Surat Masuk' : 'Tambah Surat Masuk';
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->surat = SuratMasuk::findOrFail($id);
            $this->no_urut = $this->surat->no_urut;
            $this->tanggal = $this->surat->tanggal;
            $this->no_surat = $this->surat->no_surat;
            $this->perihal = $this->surat->perihal;
            $this->asal_surat = $this->surat->asal_surat;
            $this->tanggal_acara = $this->surat->tanggal_acara;
            $this->waktu_acara = $this->surat->waktu_acara;
            $this->tempat_acara = $this->surat->tempat_acara;
            $this->tgl_masuk = $this->surat->tgl_masuk;
            $this->tgl_keluar = $this->surat->tgl_keluar;
            $this->tgl_dikembalikan = $this->surat->tgl_dikembalikan;
            $this->distribusi = $this->surat->distribusi;
            
            $decodedDisposisi = json_decode($this->surat->disposisi, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDisposisi)) {
                $this->disposisi_checkboxes = $decodedDisposisi['checkboxes'] ?? [];
                $this->disposisi_custom = $decodedDisposisi['custom'] ?? '';
            } else {
                $this->disposisi_custom = $this->surat->disposisi;
            }

            $this->sifat_surat = $this->surat->sifat_surat;
            $this->keterangan = $this->surat->keterangan;
            $this->bundle_id = $this->surat->bundle_id;
            $this->existing_scans = is_array($this->surat->scan_file) ? $this->surat->scan_file : [];
        } else {
            // Auto-increment No Urut based on current year
            $maxNo = SuratMasuk::whereYear('created_at', date('Y'))->max('no_urut');
            $this->no_urut = $maxNo ? $maxNo + 1 : 1;
            $this->tanggal = date('Y-m-d');
            $this->sifat_surat = 'Biasa';
            $this->bundle_id = 1;
        }
    }

    public function deleteExistingFile($index)
    {
        if (isset($this->existing_scans[$index])) {
            $path = $this->existing_scans[$index];
            Storage::disk('public')->delete($path);
            unset($this->existing_scans[$index]);
            $this->existing_scans = array_values($this->existing_scans); // reindex
            
            if ($this->surat) {
                $this->surat->update(['scan_file' => empty($this->existing_scans) ? null : $this->existing_scans]);
            }
        }
    }

    public function save()
    {
        $this->validate([
            'no_urut' => 'nullable|integer',
            'tanggal' => 'nullable|date',
            'no_surat' => 'nullable|string|max:255',
            'perihal' => 'required|string|max:500',
            'asal_surat' => 'nullable|string|max:255',
            'tanggal_acara' => 'nullable|date',
            'waktu_acara' => 'nullable|string|max:50',
            'tempat_acara' => 'nullable|string|max:255',
            'tgl_masuk' => 'nullable|date',
            'tgl_keluar' => 'nullable|date',
            'tgl_dikembalikan' => 'nullable|date',
            'distribusi' => 'nullable|string|max:255',
            'disposisi' => 'nullable|string|max:500',
            'sifat_surat' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
            'bundle_id' => 'nullable|exists:bundles,id',
            'scan_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB per file
        ]);

        $data = [
            'no_urut' => $this->no_urut,
            'tanggal' => $this->tanggal,
            'no_surat' => $this->no_surat,
            'perihal' => $this->perihal,
            'asal_surat' => $this->asal_surat,
            'tanggal_acara' => $this->tanggal_acara,
            'waktu_acara' => $this->waktu_acara,
            'tempat_acara' => $this->tempat_acara,
            'tgl_masuk' => $this->tgl_masuk,
            'tgl_keluar' => $this->tgl_keluar,
            'tgl_dikembalikan' => $this->tgl_dikembalikan,
            'distribusi' => $this->distribusi,
            'disposisi' => json_encode([
                'checkboxes' => $this->disposisi_checkboxes,
                'custom' => $this->disposisi_custom
            ]),
            'sifat_surat' => $this->sifat_surat,
            'keterangan' => $this->keterangan,
            'bundle_id' => $this->bundle_id,
        ];

        $suratModel = null;
        if ($this->surat) {
            $this->surat->update($data);
            $suratModel = $this->surat;
            $message = 'Data Surat Masuk berhasil diperbarui.';
        } else {
            $suratModel = SuratMasuk::create($data);
            $message = 'Surat Masuk baru berhasil ditambahkan.';
        }

        $allFiles = $this->existing_scans ?? [];

        // --- Logika Integrasi Dokumen & FileAttachment ---
        if ($this->bundle_id) {
            // Pastikan kategori Surat Masuk ada di Bundle ini
            $kategori = \App\Models\Kategori::firstOrCreate([
                'bundle_id' => $this->bundle_id,
                'nama' => 'Surat Masuk'
            ], [
                'kode' => 'SM',
                'urutan' => 98
            ]);

            $dokumen = null;
            if ($suratModel->dokumen_id) {
                $dokumen = \App\Models\Dokumen::find($suratModel->dokumen_id);
            }

            if (!$dokumen) {
                $dokumen = \App\Models\Dokumen::create([
                    'kategori_id' => $kategori->id,
                    'judul' => 'Surat Masuk: ' . ($this->perihal ?? $this->no_surat ?? '-'),
                    'tanggal_dokumen' => $this->tanggal,
                    'nomor_dokumen' => $this->no_surat,
                    'keterangan' => $this->keterangan,
                    'uploaded_by' => auth()->id()
                ]);
                $suratModel->update(['dokumen_id' => $dokumen->id]);
            } else {
                $dokumen->update([
                    'kategori_id' => $kategori->id,
                    'judul' => 'Surat Masuk: ' . ($this->perihal ?? $this->no_surat ?? '-'),
                    'tanggal_dokumen' => $this->tanggal,
                    'nomor_dokumen' => $this->no_surat,
                    'keterangan' => $this->keterangan,
                ]);
            }

            // Simpan file baru sebagai FileAttachment (selain JSON lama)
            if (!empty($this->scan_files)) {
                foreach ($this->scan_files as $file) {
                    $path = $file->store('dokumen_files', 'public');
                    $mime = $file->getMimeType();
                    if ($mime === 'application/octet-stream') {
                        $ext = strtolower($file->getClientOriginalExtension());
                        $mime = match($ext) {
                            'pdf' => 'application/pdf',
                            'jpg', 'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                            'webp' => 'image/webp',
                            default => 'application/octet-stream'
                        };
                    }
                    \App\Models\FileAttachment::create([
                        'dokumen_id' => $dokumen->id,
                        'nama_file' => $file->getClientOriginalName(),
                        'path' => $path,
                        'disk' => 'public',
                        'mime_type' => $mime,
                        'ukuran' => $file->getSize()
                    ]);
                    
                    // Juga tambahkan ke array lama agar backward compatible di form
                    $allFiles[] = $path;
                }
            }
        } else {
            // Jika tidak ada bundle, jalankan fallback upload lama saja
            if (!empty($this->scan_files)) {
                foreach ($this->scan_files as $file) {
                    $path = $file->store('surat_masuk', 'public');
                    $allFiles[] = $path;
                }
            }
        }
        
        $suratModel->update(['scan_file' => empty($allFiles) ? null : $allFiles]);

        session()->flash('success', $message);
        $this->redirect('/surat-masuk', navigate: true);
    }

    public function with(): array
    {
        return [
            'bundles' => Bundle::where('id', 1)->get()
        ];
    }
}; ?>
<div>
    <div class="breadcrumb" style="margin-bottom: 24px; font-size: 0.9rem;">
        <a href="/surat-masuk" style="color: var(--text-muted); text-decoration: none;">Surat Masuk</a>
        <span style="margin: 0 8px; color: var(--border-color);">/</span>
        <span style="color: var(--text-primary); font-weight: 600;">{{ $surat ? 'Edit Data' : 'Tambah Data Baru' }}</span>
    </div>

    <form wire:submit="save" enctype="multipart/form-data" class="card" style="padding: 32px; width: 100%;">
        <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            {{ $surat ? 'Edit Surat Masuk' : 'Form Surat Masuk' }}
        </h2>

        @if ($errors->any())
            <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #fca5a5;">
                <strong style="display:block; margin-bottom: 4px;">Gagal menyimpan data:</strong>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            
            <!-- Kolom Kiri: Informasi Utama Surat -->
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 8px;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">Informasi Surat</h3>
                    
                    <input type="hidden" wire:model="no_urut">
                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Tanggal Surat</label>
                        <input type="date" wire:model="tanggal" class="form-input" style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Nomor Surat (Fisik)</label>
                        <input type="text" wire:model="no_surat" class="form-input" style="width: 100%;" placeholder="Contoh: 123/UM/2026">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Asal Surat</label>
                        <input type="text" wire:model="asal_surat" class="form-input" style="width: 100%;" placeholder="Instansi/Perusahaan pengirim">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Perihal *</label>
                        <textarea wire:model="perihal" class="form-input" style="width: 100%; min-height: 80px;" placeholder="Isi perihal surat" required></textarea>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.85rem;">Sifat Surat</label>
                        <select wire:model="sifat_surat" class="form-input" style="width: 100%;">
                            <option value="Biasa">Biasa</option>
                            <option value="Penting">Penting</option>
                            <option value="Segera">Segera</option>
                            <option value="Sangat Segera">Sangat Segera</option>
                            <option value="Rahasia">Rahasia</option>
                        </select>
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">Detail Acara (Opsional)</h3>
                    <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <label class="form-label" style="font-size: 0.85rem;">Tanggal Acara</label>
                            <input type="date" wire:model="tanggal_acara" class="form-input" style="width: 100%;">
                        </div>
                        <div style="flex: 1;">
                            <label class="form-label" style="font-size: 0.85rem;">Waktu Acara</label>
                            <input type="text" wire:model="waktu_acara" class="form-input" style="width: 100%;" placeholder="09:00 - Selesai">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.85rem;">Tempat Acara</label>
                        <input type="text" wire:model="tempat_acara" class="form-input" style="width: 100%;">
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Tracking & Upload -->
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 16px;">Tracking & Disposisi</h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Tgl Masuk (Pimpinan)</label>
                            <input type="date" wire:model="tgl_masuk" class="form-input" style="width: 100%;">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Tgl Keluar (Pimpinan)</label>
                            <input type="date" wire:model="tgl_keluar" class="form-input" style="width: 100%;">
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Distribusi (Ke Seksi/Unit)</label>
                        <input type="text" wire:model="distribusi" class="form-input" style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Isi Disposisi / Arahan</label>
                        <div style="background: white; border: 1px solid var(--border-color); border-radius: 6px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" wire:model="disposisi_checkboxes" value="Ka. Sub Bag Tata Usaha" style="margin-top: 3px;">
                                <span>1. Ka. Sub Bag Tata Usaha.</span>
                            </label>
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" wire:model="disposisi_checkboxes" value="Ka. Sie Perencanaan" style="margin-top: 3px;">
                                <span>2. Ka. Sie Perencanaan.</span>
                            </label>
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" wire:model="disposisi_checkboxes" value="Ka. Sie Pemeliharaan Drainase" style="margin-top: 3px;">
                                <span>3. Ka. Sie Pemeliharaan Drainase.</span>
                            </label>
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" wire:model="disposisi_checkboxes" value="Ka. Sie Pembangunan dan Peningkatan Drainase" style="margin-top: 3px;">
                                <span>4. Ka. Sie Pembangunan dan Peningkatan Drainase.</span>
                            </label>
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" wire:model="disposisi_checkboxes" value="Ka. Sie Pengelolaan Sarana Pengendali Banjir, Air Bersih, dan Air Limbah" style="margin-top: 3px;">
                                <span>5. Ka. Sie Pengelolaan Sarana Pengendali Banjir, Air Bersih, dan Air Limbah.</span>
                            </label>
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" wire:model="disposisi_checkboxes" value="Satuan Pelaksana SDA Kecamatan Cilincing" style="margin-top: 3px;">
                                <span>6. Satuan Pelaksana SDA Kecamatan Cilincing.</span>
                            </label>
                            <div style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; margin-top: 4px;">
                                <span style="margin-top: 8px;">7.</span>
                                <textarea wire:model="disposisi_custom" class="form-input" placeholder="........................ (Tambahkan teks arahan khusus di sini)" style="width: 100%; min-height: 60px; padding: 8px;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <div style="flex: 1;">
                            <label class="form-label" style="font-size: 0.85rem;">Tgl Dikembalikan</label>
                            <input type="date" wire:model="tgl_dikembalikan" class="form-input" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <div style="background: #f0f9ff; padding: 16px; border-radius: 8px; border: 1px solid #bae6fd;">
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: #0369a1; margin-bottom: 16px;">Pengorganisasian & File</h3>
                    
                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-size: 0.85rem; color: #0369a1;">Pilih Bundle (Opsional)</label>
                        
                        <!-- Custom Dropdown Searchable -->
                        <div x-data="{ open: false }" style="position: relative;" @click.away="open = false">
                            <div @click="open = !open" style="border: 1px solid #bae6fd; padding: 8px 12px; border-radius: 6px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                                    @if($bundle_id)
                                        {{ collect($bundles)->firstWhere('id', $bundle_id)?->nama ?? 'Bundle tidak ditemukan' }}
                                        <span style="color:var(--text-muted); font-size:0.8rem; font-weight:normal;">({{ collect($bundles)->firstWhere('id', $bundle_id)?->tahun ?? '-' }})</span>
                                    @else
                                        <span style="color: var(--text-muted); font-weight: normal;">-- Biarkan kosong jika tidak dimasukkan ke Bundle --</span>
                                    @endif
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>
                            <div x-show="open" style="display: none; position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px; background: white; border: 1px solid var(--border-color); border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 50;">
                                
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <div wire:click="$set('bundle_id', null); open = false" 
                                         style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f8fafc; transition:background 0.2s;" 
                                         onmouseover="this.style.background='#f8fafc'" 
                                         onmouseout="this.style.background='transparent'">
                                        <div style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted);">-- Kosongkan Pilihan --</div>
                                    </div>
                                    @if(count($bundles) === 0)
                                        <div style="padding: 10px 12px; color: var(--text-muted); font-size: 0.85rem; text-align: center;">Tidak ada hasil ditemukan</div>
                                    @else
                                        @foreach($bundles as $b)
                                            <div wire:click="$set('bundle_id', {{ $b->id }}); open = false" 
                                                 style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f8fafc; transition:background 0.2s; {{ $bundle_id == $b->id ? 'background: var(--primary-light);' : '' }}" 
                                                 onmouseover="this.style.background='#f8fafc'" 
                                                 onmouseout="this.style.background='{{ $bundle_id == $b->id ? 'var(--primary-light)' : 'transparent' }}'">
                                                <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">{{ $b->nama }}</div>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">Tahun: {{ $b->tahun }} | Kode: {{ $b->kode ?? '-' }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Pilih bundle jika Anda ingin menyimpan surat ini ke dalam arsip bundle tertentu.</p>
                    </div>
                    
                    <div style="margin-bottom: 16px;">
                        <label class="form-label" style="font-size: 0.85rem; color: #0369a1;">Keterangan Tambahan</label>
                        <textarea wire:model="keterangan" class="form-input" style="width: 100%; min-height: 60px; border-color: #bae6fd;"></textarea>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.85rem; color: #0369a1;">Upload Scan Fisik (Opsional)</label>
                        
                        @if(!empty($existing_scans))
                            @foreach($existing_scans as $index => $scan)
                                <div style="margin-bottom: 8px; padding: 8px; background: white; border-radius: 6px; border: 1px solid #bae6fd; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="display: flex; align-items: center; gap: 6px;">
                                        📄 {{ Str::afterLast($scan, '/') }}
                                    </span>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="{{ Storage::url($scan) }}" target="_blank" style="color: var(--primary); font-weight: 600;">Lihat</a>
                                        <button type="button" wire:click="deleteExistingFile({{ $index }})" style="color: var(--danger); font-weight: 600; border: none; background: none; cursor: pointer;">Hapus</button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <div style="margin-top: 8px;">
                            <input type="file" wire:model="scan_files" accept=".pdf,image/*" multiple style="width: 100%; padding: 8px; background: white; border: 1px dashed #7dd3fc; border-radius: 6px; font-size: 0.85rem; cursor: pointer;">
                            
                            <!-- Indikator Loading Upload -->
                            <div wire:loading.flex wire:target="scan_files" style="margin-top: 8px; font-size: 0.85rem; font-weight: 600; color: var(--primary); align-items: center; gap: 6px;">
                                <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sedang mengunggah file sementara...
                            </div>
                            
                            <!-- Menampilkan Nama File Jika Berhasil Masuk Sementara -->
                            @if(!empty($scan_files) && is_array($scan_files))
                                <div wire:loading.remove wire:target="scan_files" style="margin-top: 8px; font-size: 0.85rem; color: var(--success); font-weight: 600;">
                                    @foreach($scan_files as $file)
                                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                            ✅ File "{{ $file->getClientOriginalName() }}" siap disimpan.
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('scan_files.*') <div style="color: var(--danger); font-size: 0.8rem; margin-top: 4px; font-weight: 600;">{{ $message }}</div> @enderror
                    </div>
                </div>

            </div>
        </div>

        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px;">
            <a href="/surat-masuk" class="btn" style="background: #f1f5f9; color: var(--text-secondary); font-weight: 600;">Batalkan</a>
            <button type="submit" class="btn btn-primary" style="font-weight: 700; padding-left: 24px; padding-right: 24px;">
                <span wire:loading.remove wire:target="save">Simpan Data</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
