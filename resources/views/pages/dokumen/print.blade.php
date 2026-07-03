<?php

use Livewire\Volt\Component;
use App\Models\Dokumen;

new #[\Livewire\Attributes\Layout('layouts.print')] #[\Livewire\Attributes\Title('Cetak: {{ $dokumen->judul }}')] class extends Component {
    public Dokumen $dokumen;

    public function mount(Dokumen $dokumen)
    {
        $this->dokumen = $dokumen->load(['fileAttachments', 'uploader', 'kategori.bundle']);
    }
}; ?>
<div>
    <div class="header">
        <h1>DETAIL DOKUMEN ARSIP</h1>
        <p>Aplikasi E-Arsip Digital</p>
    </div>

    <table>
        <tbody>
            <tr>
                <th>Judul Dokumen</th>
                <td>{{ $dokumen->judul }}</td>
            </tr>
            <tr>
                <th>Nomor Dokumen</th>
                <td>{{ $dokumen->nomor_dokumen ?: '-' }}</td>
            </tr>
            <tr>
                <th>Tanggal Dokumen</th>
                <td>{{ $dokumen->tanggal_dokumen ? $dokumen->tanggal_dokumen->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Bundle / Kategori</th>
                <td>{{ $dokumen->kategori->bundle->nama }} / {{ $dokumen->kategori->nama }}</td>
            </tr>
            <tr>
                <th>Pengunggah (Uploader)</th>
                <td>{{ $dokumen->uploader?->name ?: '-' }}</td>
            </tr>
            <tr>
                <th>Waktu Diunggah</th>
                <td>{{ $dokumen->created_at->format('d F Y, H:i') }}</td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td>{{ $dokumen->keterangan ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    <h3 style="margin-top: 30px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Daftar File Lampiran ({{ $dokumen->fileAttachments->count() }})</h3>
    
    @if($dokumen->fileAttachments->count() > 0)
        <table class="file-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama File</th>
                    <th>Tipe File</th>
                    <th>Ukuran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dokumen->fileAttachments as $index => $file)
                    <tr>
                        <td style="width: 50px; text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $file->nama_file }}</td>
                        <td>{{ strtoupper($file->extension) }}</td>
                        <td>{{ $file->ukuran_format }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada file lampiran pada dokumen ini.</p>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y, H:i') }}</p>
        <div>
            Mengetahui,<br>
            Petugas Arsip
            <br><br>
            <span class="signature-space"></span><br>
            ( ......................................... )
        </div>
    </div>
</div>
