<?php

use Livewire\Volt\Component;
use App\Models\SuratMasuk;

new #[\Livewire\Attributes\Layout('layouts.print')] class extends Component {
    public SuratMasuk $surat;

    public function mount($id)
    {
        $this->surat = SuratMasuk::findOrFail($id);
    }
}; ?>
<div>
    <style>
        .disposisi-container {
            max-width: 800px;
            margin: 0 auto;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            line-height: 1.6;
            color: #000;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .title { font-size: 20px; text-decoration: underline; margin-bottom: 20px; }
        .row-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .row-table td { padding: 4px 0; vertical-align: top; }
        .col-label { width: 140px; }
        .col-colon { width: 20px; text-align: center; }
        
        .box-container {
            display: flex;
            margin-top: 20px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 15px 0;
        }
        .box-left, .box-right {
            flex: 1;
        }
        .box-left {
            padding-right: 20px;
            border-right: 1px solid #000;
        }
        .box-right {
            padding-left: 20px;
        }
        .check-item {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
        }
        .check-box {
            width: 20px;
            height: 20px;
            border: 1px solid #000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            margin-top: 2px;
            font-family: Arial, sans-serif;
            font-weight: bold;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            width: 100%;
            min-height: 20px;
        }
        .dotted-line-inline {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 150px;
        }
        .footer-note {
            margin-top: 20px;
            font-style: italic;
        }
        
        /* Edit mode for print view */
        [contenteditable] {
            outline: none;
        }
        [contenteditable]:hover {
            background: rgba(255, 255, 0, 0.2);
            cursor: text;
        }
        [contenteditable]:focus {
            background: rgba(255, 255, 0, 0.3);
            outline: none;
        }
        
        @media print {
            .no-print { display: none; }
            [contenteditable]:hover, [contenteditable]:focus { background: transparent; outline: none; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pageId = 'disposisi_surat_{{ $surat->id ?? 0 }}';
            const editables = document.querySelectorAll('[contenteditable]');
            
            // Load from localStorage
            editables.forEach((el, index) => {
                const saved = localStorage.getItem(pageId + '_field_' + index);
                if (saved !== null) {
                    el.innerText = saved;
                }
                
                // Save to localStorage on input
                el.addEventListener('input', function() {
                    localStorage.setItem(pageId + '_field_' + index, el.innerText);
                });
            });
        });
    </script>

    <div class="disposisi-container" style="page-break-after: always; position: relative;">
        <div style="position: relative; text-align: center; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; min-height: 70px;">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" style="position: absolute; left: 0; width: 60px; height: auto;">
            <div class="font-bold title" style="margin-bottom: 0;">LEMBAR DISPOSISI / CATATAN</div>
        </div>
        
        <table class="row-table" style="table-layout: fixed;">
            <tr>
                <td style="width:80px;">Index</td>
                <td style="width:10px;">:</td>
                <td><span class="font-bold" contenteditable="plaintext-only">{{ $surat->no_urut ?? '.................' }}</span></td>
                <td style="width:120px;">Tanggal Masuk</td>
                <td style="width:10px;">:</td>
                <td contenteditable="plaintext-only">{{ $surat->tgl_masuk ? \Carbon\Carbon::parse($surat->tgl_masuk)->format('d/m/Y') : '.................' }}</td>
                <td style="width:50px;">Kode</td>
                <td style="width:10px;">:</td>
                <td contenteditable="plaintext-only">{{ $surat->kode ?? '.................' }}</td>
            </tr>
        </table>

        <table class="row-table">
            <tr>
                <td class="col-label">Perihal ringkas</td>
                <td class="col-colon">:</td>
                <td style="border-bottom: 1px dotted #000;" contenteditable="plaintext-only">{{ $surat->perihal ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-label">Tgl. / No Surat</td>
                <td class="col-colon">:</td>
                <td style="border-bottom: 1px dotted #000;">
                    <span contenteditable="plaintext-only">{{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('d/m/Y') : '........' }}</span> / <span contenteditable="plaintext-only">{{ $surat->no_surat ?? '........' }}</span>
                </td>
            </tr>
            <tr>
                <td class="col-label">Asal</td>
                <td class="col-colon">:</td>
                <td style="border-bottom: 1px dotted #000;" contenteditable="plaintext-only">{{ $surat->asal_surat ?? '' }}</td>
            </tr>
        </table>

        @php
            $decodedDisposisi = json_decode($surat->disposisi, true);
            $checkboxes = [];
            $customText = '';
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDisposisi)) {
                $checkboxes = $decodedDisposisi['checkboxes'] ?? [];
                $customText = $decodedDisposisi['custom'] ?? '';
            } else {
                $customText = $surat->disposisi;
            }

            $options = [
                'Ka. Sub Bag Tata Usaha',
                'Ka. Sie Perencanaan',
                'Ka. Sie Pemeliharaan Drainase',
                'Ka. Sie Pembangunan dan Peningkatan Drainase',
                'Ka. Sie Pengelolaan Sarana Pengendali Banjir, Air Bersih, dan Air Limbah',
                'Satuan Pelaksana SDA Kecamatan Cilincing',
                'Satuan Pelaksana SDA Kecamatan'
            ];
            
            // Handle legacy / missing exact match
            $isChecked = function($val) use ($checkboxes) {
                return in_array($val, $checkboxes) ? '✓' : '';
            };
        @endphp

        <div class="box-container">
            <div class="box-left">
                <div class="font-bold text-center" style="margin-bottom: 15px;">Instruksi / Informasi</div>
                <div style="min-height: 200px; white-space: pre-wrap;" contenteditable="plaintext-only">{{ $surat->distribusi ?? '' }}</div>
            </div>
            <div class="box-right">
                <div class="font-bold text-center" style="margin-bottom: 15px;">Diteruskan / Kepada</div>
                
                <div class="check-item">
                    <span>1.</span>
                    <span class="check-box">{{ $isChecked('Ka. Sub Bag Tata Usaha') }}</span>
                    <span>Ka. Sub Bag Tata Usaha.</span>
                </div>
                <div class="check-item">
                    <span>2.</span>
                    <span class="check-box">{{ $isChecked('Ka. Sie Perencanaan') }}</span>
                    <span>Ka. Sie Perencanaan.</span>
                </div>
                <div class="check-item">
                    <span>3.</span>
                    <span class="check-box">{{ $isChecked('Ka. Sie Pemeliharaan Drainase') }}</span>
                    <span>Ka. Sie Pemeliharaan Drainase.</span>
                </div>
                <div class="check-item">
                    <span>4.</span>
                    <span class="check-box">{{ $isChecked('Ka. Sie Pembangunan dan Peningkatan Drainase') }}</span>
                    <span>Ka. Sie Pembangunan dan Peningkatan Drainase.</span>
                </div>
                <div class="check-item">
                    <span>5.</span>
                    <span class="check-box">{{ $isChecked('Ka. Sie Pengelolaan Sarana Pengendali Banjir, Air Bersih, dan Air Limbah') }}</span>
                    <span>Ka. Sie Pengelolaan Sarana Pengendali Banjir, Air Bersih, dan Air Limbah.</span>
                </div>
                <div class="check-item" style="align-items: flex-start;">
                    <span>6.</span>
                    <span style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
                        <div>Satuan Pelaksana SDA Kecamatan:</div>
                        @php
                            $kecamatans = ['Cilincing', 'Pademangan', 'Kelapa Gading', 'Koja', 'Penjaringan', 'Tanjung Priok'];
                        @endphp
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-top: 2px; padding-left: 4px;">
                            @foreach($kecamatans as $kec)
                                <div style="display: flex; gap: 6px;">
                                    <span class="check-box" style="margin-top: 1px; width: 14px; height: 14px; line-height: 14px; font-size: 10px;">{{ in_array('Satuan Pelaksana SDA Kecamatan ' . $kec, $checkboxes) || in_array('Satuan Pelaksana SDA Kecamatan', $checkboxes) ? '✓' : '' }}</span>
                                    <span style="font-size: 0.9em;">{{ $kec }}</span>
                                </div>
                            @endforeach
                        </div>
                    </span>
                </div>
                <div class="check-item">
                    <span>7.</span>
                    <span class="check-box">{{ $customText ? '✓' : '' }}</span>
                    <span>
                        @if($customText)
                            {{ $customText }}
                        @else
                            <span class="dotted-line" style="margin-top: 15px;"></span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="footer-note">
            <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">
                Sesudah di gunakan harap segera dikembalikan
            </div>
            <table class="row-table">
                <tr>
                    <td style="width: 80px;">Kepada</td>
                    <td style="width: 10px;">:</td>
                    <td style="border-bottom: 1px dotted #000;">{{ $surat->keterangan ?? '' }}</td>
                </tr>
            </table>
            <div>*Coret yang tidak perlu</div>
        </div>
    </div>
</div>
