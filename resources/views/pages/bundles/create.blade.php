<?php

use Livewire\Volt\Component;
use App\Models\Bundle;

new #[\Livewire\Attributes\Layout('layouts.app')] #[\Livewire\Attributes\Title('Buat Bundle')] class extends Component {
    public string $nama = '';
    public string $kode = '';
    public string $deskripsi = '';
    public string $tahun = '';

    public function mount()
    {
        $this->tahun = (string) date('Y');
        $this->generateKode();
    }

    public function generateKode()
    {
        $year = $this->tahun ?: date('Y');
        $random = strtoupper(\Illuminate\Support\Str::random(5));
        $this->kode = "BDL-{$year}-{$random}";
    }

    public function simpan()
    {
        if (!auth()->user()->canManage()) {
            session()->flash('error', 'Anda tidak memiliki akses untuk membuat bundle.');
            return;
        }

        $this->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:bundles,kode',
            'deskripsi' => 'nullable|string|max:500',
            'tahun' => 'required|integer|min:2000|max:2099',
        ]);

        $bundle = Bundle::create([
            'nama' => $this->nama,
            'kode' => strtoupper($this->kode),
            'deskripsi' => $this->deskripsi,
            'tahun' => (int) $this->tahun,
            'created_by' => auth()->id(),
        ]);

        session()->flash('success', "Bundle '{$bundle->nama}' berhasil dibuat!");
        return redirect("/bundles/{$bundle->id}");
    }
}; ?>
<div>
<div class="breadcrumb">
        <a href="/dashboard">Dashboard</a>
        <span class="separator">/</span>
        <a href="/bundles">Bundle Arsip</a>
        <span class="separator">/</span>
        <span class="current">Buat Baru</span>
    </div>

    <div class="page-header">
        <h1>Buat Bundle Baru</h1>
        <p>Buat bundle arsip baru untuk mengelompokkan dokumen</p>
    </div>

    <div class="card" style="max-width: 640px;">
        <form wire:submit="simpan">
            <div class="form-group">
                <label class="form-label">Nama Bundle *</label>
                <input type="text" wire:model="nama" class="form-input" 
                       placeholder="contoh: Arsip Dinas Jakarta" autofocus>
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">Kode Bundle *</label>
                        <button type="button" wire:click="generateKode" class="btn btn-secondary" style="padding: 2px 8px; font-size: 0.7rem;">🔄 Generate</button>
                    </div>
                    <input type="text" wire:model.live="kode" class="form-input" 
                           placeholder="contoh: BDL-JKT-2026" style="text-transform: uppercase;">
                    @error('kode') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Tahun *</label>
                    <input type="number" wire:model="tahun" class="form-input" 
                           min="2000" max="2099">
                    @error('tahun') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            @if($kode)
                <div class="form-group" style="text-align: center; padding: 16px; background: var(--bg-primary); border-radius: var(--radius-sm); border: 1px dashed var(--border-color);">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Preview QR Code (Kode)</div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($kode) }}" alt="QR Code" style="border-radius: 8px; border: 4px solid white; display: inline-block;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 8px;">⚠️ Setelah Bundle disimpan, QR Code akan otomatis mengarah ke halaman detail bundle ini.</div>
                </div>
            @endif

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea wire:model="deskripsi" class="form-textarea" rows="3"
                          placeholder="Deskripsi singkat tentang bundle ini..."></textarea>
                @error('deskripsi') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>💾 Simpan Bundle</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
                <a href="/bundles" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>


</div>
