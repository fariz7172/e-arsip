<?php

use Livewire\Volt\Component;
use App\Models\Bundle;

new #[\Livewire\Attributes\Layout('layouts.print')] class extends Component {
    public Bundle $bundle;

    public function mount(Bundle $bundle)
    {
        $this->bundle = $bundle;
    }
}; ?>
<div>
    <style id="print-style">
        /* Pengaturan default */
        @media print {
            @page {
                size: 80mm 50mm; 
                margin: 0;
            }
            body { margin: 0; padding: 0; background: white; }
        }
        .label-container {
            width: 80mm;
            height: 50mm;
        }
    </style>
    <style>
        .label-container {
            box-sizing: border-box;
            padding: 3mm;
            border: 2px dashed #ccc;
            margin: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-family: Arial, sans-serif;
            background: white;
            page-break-after: always;
            transition: all 0.3s ease;
        }

        .label-qr {
            margin-bottom: 2mm;
        }
        
        .label-qr img {
            width: 25mm;
            height: 25mm;
            display: block;
            margin: auto;
            transition: all 0.3s ease;
        }

        .label-code {
            font-size: 11pt;
            font-weight: bold;
            font-family: monospace;
            margin-bottom: 2px;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .label-title {
            font-size: 9pt;
            font-weight: bold;
            line-height: 1.2;
            max-height: 22pt;
            overflow: hidden;
            margin-bottom: 1px;
            transition: all 0.3s ease;
        }

        .label-meta {
            font-size: 7pt;
            color: #333;
        }

        @media print {
            .no-print { display: none !important; }
            .label-container { border: none; margin: 0; }
        }
    </style>

    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 20px; background: #f3f4f6; border-radius: 8px; max-width: 600px; margin-left: auto; margin-right: auto;">
        <h3 style="margin-bottom: 15px; color: #111;">Pengaturan Cetak Label</h3>
        
        <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 10px;">
            <label style="font-weight: bold; font-size: 0.9rem;">Pilih Ukuran Kertas Printer:</label>
            <select id="sizeSelector" onchange="changeSize()" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
                <option value="80x50">80mm x 50mm (Standar Ordner)</option>
                <option value="100x70">100mm x 70mm (Stiker Besar)</option>
                <option value="50x30">50mm x 30mm (Stiker Kecil)</option>
                <option value="60x40">60mm x 40mm (Stiker Sedang)</option>
            </select>
        </div>

        <div style="display: flex; justify-content: center; gap: 10px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Label
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Tutup</button>
        </div>
    </div>

    <!-- Label Fisik -->
    <div class="label-container" id="printableLabel">
        <div class="label-qr">
            <!-- QR Code MURNI KODE BUNDLE agar bisa di scan alat USB -->
            <img id="qrImg" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($bundle->kode) }}" alt="QR Code">
        </div>
        <div class="label-code" id="labelCode">{{ $bundle->kode }}</div>
        <div class="label-title" id="labelTitle">{{ \Illuminate\Support\Str::limit($bundle->nama, 35) }}</div>
        <div class="label-meta" id="labelMeta">Th: {{ $bundle->tahun }} | E-Arsip</div>
    </div>

    <script>
        function changeSize() {
            const size = document.getElementById('sizeSelector').value;
            const style = document.getElementById('print-style');
            const qrImg = document.getElementById('qrImg');
            const code = document.getElementById('labelCode');
            const title = document.getElementById('labelTitle');
            const meta = document.getElementById('labelMeta');
            
            let width, height, qrSize, codeSize, titleSize, metaSize;

            if (size === '100x70') {
                width = '100mm'; height = '70mm'; qrSize = '35mm'; codeSize = '14pt'; titleSize = '10pt'; metaSize = '8pt';
            } else if (size === '80x50') {
                width = '80mm'; height = '50mm'; qrSize = '25mm'; codeSize = '11pt'; titleSize = '9pt'; metaSize = '7pt';
            } else if (size === '60x40') {
                width = '60mm'; height = '40mm'; qrSize = '20mm'; codeSize = '9pt'; titleSize = '7pt'; metaSize = '6pt';
            } else if (size === '50x30') {
                width = '50mm'; height = '30mm'; qrSize = '15mm'; codeSize = '7pt'; titleSize = '6pt'; metaSize = '5pt';
            }

            style.innerHTML = `
                @media print {
                    @page { size: ${width} ${height}; margin: 0; }
                    body { margin: 0; padding: 0; background: white; }
                }
                .label-container { width: ${width}; height: ${height}; }
            `;
            
            qrImg.style.width = qrSize;
            qrImg.style.height = qrSize;
            code.style.fontSize = codeSize;
            title.style.fontSize = titleSize;
            meta.style.fontSize = metaSize;
        }
    </script>
</div>
