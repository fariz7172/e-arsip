<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Bundle;
use App\Models\Payment;
use App\Models\Kategori;
use App\Models\Dokumen;
use App\Models\FileAttachment;
use Livewire\Attributes\Url;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Tarik Data Pembayaran')] class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';

    #[Url]
    public $perPage = 10;

    // Modal state
    public bool $showModal = false;
    public ?int $selectedPaymentId = null;

    // Bundle Form State
    public $uploadOption = 'existing'; // 'existing' or 'new'
    public $searchBundle = '';
    public $selectedBundleId = null;
    
    public $newBundleNama = '';
    public $newBundleKode = '';
    public $newBundleTahun = '';
    
    public $uploadedFiles = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function openModal($paymentId)
    {
        $this->selectedPaymentId = $paymentId;
        $this->showModal = true;
        
        $payment = Payment::find($paymentId);
        if ($payment && $payment->bundle_id) {
            $this->uploadOption = 'existing';
            $this->selectedBundleId = $payment->bundle_id;
        } else {
            $this->uploadOption = 'new';
            $this->newBundleTahun = date('Y');
            $this->newBundleNama = 'Berkas SPN ' . ($payment->no_spm ?? '');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['selectedPaymentId', 'uploadOption', 'searchBundle', 'selectedBundleId', 'newBundleNama', 'newBundleKode', 'newBundleTahun', 'uploadedFiles']);
    }

    public function saveBundle()
    {
        $payment = Payment::findOrFail($this->selectedPaymentId);

        if ($this->uploadOption === 'existing') {
            $this->validate(['selectedBundleId' => 'required|exists:bundles,id'], ['selectedBundleId.required' => 'Pilih bundle yang ada terlebih dahulu.']);
            $payment->update(['bundle_id' => $this->selectedBundleId]);
        } else {
            $this->validate([
                'newBundleNama' => 'required|string|max:255',
                'newBundleTahun' => 'required|integer',
            ]);
            $bundle = Bundle::create([
                'nama' => $this->newBundleNama,
                'kode' => $this->newBundleKode,
                'tahun' => $this->newBundleTahun,
                'created_by' => auth()->id()
            ]);
            $payment->update(['bundle_id' => $bundle->id]);
        }
        
        $bundleToUse = Bundle::find($payment->bundle_id);

        if (!empty($this->uploadedFiles) && $bundleToUse) {
            $kategori = Kategori::firstOrCreate(
                ['bundle_id' => $bundleToUse->id, 'nama' => 'Berkas Pendukung SPM'],
                ['kode' => 'SPM', 'urutan' => 1]
            );

            $dokumen = Dokumen::create([
                'kategori_id' => $kategori->id,
                'judul' => 'Lampiran SPM: ' . ($payment->no_spm ?? '-'),
                'tanggal_dokumen' => now(),
                'uploaded_by' => auth()->id()
            ]);

            foreach ($this->uploadedFiles as $file) {
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

                FileAttachment::create([
                    'dokumen_id' => $dokumen->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => 'public',
                    'mime_type' => $mime,
                    'ukuran' => $file->getSize()
                ]);
            }
        }

        session()->flash('success', 'Berhasil menghubungkan pembayaran dengan bundle.');
        $this->closeModal();
    }

    public function with(): array
    {
        $query = Payment::with('bundle')->orderBy('id', 'desc');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhere('no_spm', 'like', '%' . $this->search . '%')
                  ->orWhere('keperluan', 'like', '%' . $this->search . '%')
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(contract, '$.nomor_kontrak')) LIKE ?", ['%' . $this->search . '%'])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(vendor, '$.nama_perusahaan')) LIKE ?", ['%' . $this->search . '%']);
            });
        }

        $bundleQuery = Bundle::select('id', 'nama', 'kode', 'tahun')->orderBy('created_at', 'desc');
        if (!empty($this->searchBundle)) {
            $bundleQuery->where(function($q) {
                $q->where('nama', 'like', '%' . $this->searchBundle . '%')
                  ->orWhere('kode', 'like', '%' . $this->searchBundle . '%');
            });
        }
        $bundles = $bundleQuery->limit(50)->get();

        if ($this->selectedBundleId && !$bundles->contains('id', $this->selectedBundleId)) {
            $selected = Bundle::find($this->selectedBundleId);
            if ($selected) {
                $bundles->prepend($selected);
            }
        }

        return [
            'payments' => $query->paginate($this->perPage),
            'bundles' => $bundles
        ];
    }
}; ?>
<div>
    <!-- Page Header -->
    <div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em; line-height:1.2;">Cetak Dokumen Pembayaran</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:4px;">Tarik data dari API Aplikasi Mailing Sudin untuk dicetak.</p>
        </div>
        
        <!-- Form Tarik Data Payment -->
        <div style="background: white; padding: 12px 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display:flex; flex-direction:column; gap: 8px;">
            <!-- Form ID Tunggal -->
            <form action="{{ route('payments.sync', 631) }}" method="POST" onsubmit="this.action='/payments/sync/'+document.getElementById('api_id').value;" style="display:flex; gap: 8px; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                @csrf
                <label for="api_id" style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); width:130px;">Berdasarkan ID API:</label>
                <input type="number" id="api_id" value="631" required style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; width: 80px; font-size: 0.9rem;">
                <button type="submit" class="btn btn-primary btn-sm">Tarik</button>
            </form>
            
            <!-- Form Range Tanggal -->
            <form action="{{ route('payments.sync-batch') }}" method="POST" style="display:flex; gap: 8px; align-items: center;">
                @csrf
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); width:130px;">Berdasarkan Tgl Dibuat:</label>
                <input type="date" name="start_date" required style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem;">
                <span style="font-size:0.8rem; color:var(--text-muted);">s/d</span>
                <input type="date" name="end_date" required style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem;">
                <button type="submit" class="btn btn-success btn-sm" style="background:var(--success); color:white;">Tarik Banyak</button>
            </form>
        </div>
    </div>

    @if(session('error'))
        <div style="background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div style="background: #d1fae5; color: #047857; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Recent Payments -->
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h2 class="card-title" style="display:flex;align-items:center;gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;color:var(--primary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Data yang Pernah Ditarik
            </h2>
            <div style="display: flex; gap: 12px; align-items: center; width: 100%; max-width: 400px; justify-content: flex-end;">
                <select wire:model.live="perPage" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; font-size:0.9rem; background: white; cursor: pointer;">
                    <option value="10">10 Data</option>
                    <option value="15">15 Data</option>
                    <option value="50">50 Data</option>
                    <option value="100">100 Data</option>
                    <option value="500">500 Data</option>
                </select>
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID, No SPM, No Kontrak, Perusahaan, atau Keperluan..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; width: 100%; font-size:0.9rem;">
                </div>
            </div>
        </div>

        @if($payments->count() > 0)
            <div class="table-container" style="max-height: 60vh; overflow-y: auto; overflow-x: auto; border: 1px solid var(--border-color); border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                    <thead style="position: sticky; top: 0; z-index: 10; background: #f8fafc; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <tr>
                            <th>ID API</th>
                            <th>No. SPM</th>
                            <th>No. Kontrak</th>
                            <th>Perusahaan</th>
                            <th>Tanggal SPM</th>
                            <th>Keperluan</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td style="font-weight: bold;">{{ $payment->id }}</td>
                                <td style="color:var(--text-muted); font-family:'Courier New',monospace; font-size:0.8rem;">
                                    {{ $payment->no_spm ?? '-' }}
                                </td>
                                <td style="font-family:'Courier New',monospace; font-size:0.85rem; color:var(--text-primary);">
                                    {{ $payment->contract->nomor_kontrak ?? '-' }}
                                </td>
                                <td>
                                    <div style="font-size:0.85rem; font-weight:600; color:var(--text-primary); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $payment->vendor->nama_perusahaan ?? '-' }}">
                                        {{ $payment->vendor->nama_perusahaan ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    {{ $payment->tgl_spm?->format('d M Y') ?? '-' }}
                                </td>
                                <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $payment->keperluan }}">
                                    {{ $payment->keperluan ?? '-' }}
                                </td>
                                <td style="font-weight: 600; color:var(--text-primary);">
                                    Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button wire:click="openModal({{ $payment->id }})" class="btn btn-sm btn-secondary" style="display: inline-flex; align-items: center; gap: 4px; border: 1px solid var(--border-color); background: white;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;color:var(--primary);"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                            @if($payment->bundle_id) <span style="color:var(--primary);">Bundle #{{ $payment->bundle_id }}</span> @else Bundle @endif
                                        </button>
                                        <a href="{{ route('payments.print', $payment->id) }}" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                            Cetak
                                        </a>
                                        @if($payment->bundle_id)
                                        <a href="/bundles/{{ $payment->bundle_id }}/detail" class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 4px; background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                            Lihat Berkas
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="padding: 16px; border-top: 1px solid var(--border-color);">
                {{ $payments->links('vendor.pagination.custom', data: ['scrollTo' => false]) }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="empty-state-text">Belum ada data pembayaran yang ditemukan</div>
                <div class="empty-state-hint">Silakan cari kata kunci lain atau tarik data dari API</div>
            </div>
        @endif
    </div>

    <!-- Modal Upload / Hubungkan ke Bundle -->
    @if($showModal)
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;">
            <div style="background: white; border-radius: 12px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);">
                
                <!-- Modal Header -->
                <div style="padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-primary);">Hubungkan Bukti Fisik</h3>
                    <button wire:click="closeModal" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1;">&times;</button>
                </div>

                <!-- Modal Body -->
                <form wire:submit.prevent="saveBundle" style="padding: 24px;">
                    
                    <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 20px;">
                        Pilih Bundle Arsip yang sudah ada, atau buat Bundle baru untuk menyimpan fisik dokumen pembayaran ini.
                    </p>

                    <!-- Option 1: Existing Bundle -->
                    <div style="margin-bottom: 16px; padding: 12px; border: 1px solid {{ $uploadOption === 'existing' ? 'var(--primary)' : 'var(--border-color)' }}; border-radius: 8px; background: {{ $uploadOption === 'existing' ? 'var(--primary-light)' : 'white' }}; transition: all 0.2s;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: {{ $uploadOption === 'existing' ? '12px' : '0' }};">
                            <input type="radio" wire:model.live="uploadOption" value="existing" style="width: 16px; height: 16px; accent-color: var(--primary);">
                            <span style="font-weight: 700; color: var(--text-primary);">Pilih Bundle Arsip Tersedia</span>
                        </label>
                        
                        @if($uploadOption === 'existing')
                            <div style="padding-left: 24px;">
                                <!-- Custom Dropdown for Livewire Search -->
                                <div x-data="{ open: false }" style="position: relative;" @click.away="open = false">
                                    
                                    <!-- Selected Display -->
                                    <div @click="open = !open" style="border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 6px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">
                                            @if($selectedBundleId)
                                                {{ collect($bundles)->firstWhere('id', $selectedBundleId)?->nama ?? 'Pilih Bundle...' }}
                                                <span style="color:var(--text-muted); font-size:0.8rem; font-weight:normal;">({{ collect($bundles)->firstWhere('id', $selectedBundleId)?->kode ?? '-' }})</span>
                                            @else
                                                <span style="color: var(--text-muted); font-weight: normal;">-- Cari dan Pilih Bundle --</span>
                                            @endif
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                    </div>
                                    
                                    <!-- Dropdown List -->
                                    <div x-show="open" style="display: none; position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px; background: white; border: 1px solid var(--border-color); border-radius: 6px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 50;">
                                        <div style="padding: 8px; border-bottom: 1px solid var(--border-color);">
                                            <input type="text" wire:model.live.debounce.300ms="searchBundle" placeholder="Ketik nama atau kode bundle untuk mencari..." style="width: 100%; padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 4px; outline: none; font-size: 0.85rem;">
                                        </div>
                                        
                                        <div style="max-height: 200px; overflow-y: auto;">
                                            @if(count($bundles) === 0)
                                                <div style="padding: 10px 12px; color: var(--text-muted); font-size: 0.85rem; text-align: center;">Tidak ada hasil ditemukan</div>
                                            @else
                                                @foreach($bundles as $b)
                                                    <div wire:click="$set('selectedBundleId', {{ $b->id }}); open = false" 
                                                         style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f8fafc; transition:background 0.2s; {{ $selectedBundleId === $b->id ? 'background: var(--primary-light);' : '' }}" 
                                                         onmouseover="this.style.background='#f8fafc'" 
                                                         onmouseout="this.style.background='{{ $selectedBundleId === $b->id ? 'var(--primary-light)' : 'transparent' }}'">
                                                        <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">{{ $b->nama }}</div>
                                                        <div style="font-size: 0.75rem; color: var(--text-muted);">Kode: {{ $b->kode ?? '-' }}</div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @error('selectedBundleId') <div style="color:var(--danger); font-size:0.75rem; margin-top:4px; font-weight:600;">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>


                    <!-- Option 2: New Bundle -->
                    <div style="margin-bottom: 24px; padding:12px; border:1px solid {{ $uploadOption === 'new' ? 'var(--primary)' : 'var(--border-color)' }}; border-radius:8px; background:{{ $uploadOption === 'new' ? 'var(--primary-light)' : 'white' }};">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom: {{ $uploadOption === 'new' ? '12px' : '0' }};">
                            <input type="radio" wire:model.live="uploadOption" value="new" style="width:16px; height:16px; accent-color:var(--primary);">
                            <span style="font-weight:700; color:var(--text-primary);">Buat Bundle Baru</span>
                        </label>
                        
                        @if($uploadOption === 'new')
                            <div style="padding-left: 24px; display:flex; flex-direction:column; gap:12px;">
                                <div>
                                    <label style="font-size:0.8rem; font-weight:600; color:var(--text-secondary); margin-bottom:4px; display:block;">Nama Bundle *</label>
                                    <input type="text" wire:model="newBundleNama" class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--border-color); border-radius:6px; font-size:0.9rem;" placeholder="Misal: Berkas SPM 123">
                                    @error('newBundleNama') <div style="color:var(--danger); font-size:0.75rem; margin-top:4px; font-weight:600;">{{ $message }}</div> @enderror
                                </div>
                                <div style="display:flex; gap:12px;">
                                    <div style="flex:1;">
                                        <label style="font-size:0.8rem; font-weight:600; color:var(--text-secondary); margin-bottom:4px; display:block;">Kode Bundle</label>
                                        <input type="text" wire:model="newBundleKode" class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--border-color); border-radius:6px; font-size:0.9rem;">
                                    </div>
                                    <div style="width:100px;">
                                        <label style="font-size:0.8rem; font-weight:600; color:var(--text-secondary); margin-bottom:4px; display:block;">Tahun *</label>
                                        <input type="number" wire:model="newBundleTahun" class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--border-color); border-radius:6px; font-size:0.9rem;">
                                        @error('newBundleTahun') <div style="color:var(--danger); font-size:0.75rem; margin-top:4px; font-weight:600;">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Upload File (Opsional) -->
                    <div style="margin-bottom: 24px;">
                        <label class="form-label" style="font-weight:600; color:var(--text-primary);">Upload File Scan/Pendukung (Opsional)</label>
                        <div style="border: 2px dashed var(--border-color); padding: 24px; border-radius: 8px; text-align: center; background: #f8fafc;">
                            <input type="file" wire:model="uploadedFiles" multiple class="form-input" style="width: 100%; max-width: 300px; margin: 0 auto; display: block;" accept=".pdf,.jpg,.jpeg,.png">
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">Format: PDF, JPG, PNG. File akan disimpan sebagai dokumen pada bundle terpilih.</p>
                        </div>
                        <div wire:loading wire:target="uploadedFiles" style="font-size: 0.8rem; color: var(--primary); margin-top: 8px; font-weight: 600;">
                            Sedang mengunggah file sementara...
                        </div>
                        
                        @if($uploadedFiles)
                            <div style="margin-top: 12px; display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:0.8rem; font-weight:600; color:var(--text-primary);">File yang dipilih:</div>
                                @foreach($uploadedFiles as $index => $file)
                                    <div style="display:flex; justify-content:space-between; align-items:center; background:white; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px; height:16px; color:var(--primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            <span style="font-size:0.85rem; color:var(--text-primary); font-weight:500;">{{ $file->getClientOriginalName() }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @error('uploadedFiles.*') <div style="color:var(--danger); font-size:0.75rem; margin-top:4px;">{{ $message }}</div> @enderror
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                        <button type="button" wire:click="closeModal" class="btn" style="background:#f1f5f9; color:var(--text-secondary); font-weight:600;">Batal</button>
                        <button type="submit" class="btn btn-primary" style="font-weight:700;">
                            Simpan & Lanjutkan
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;margin-left:4px;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
