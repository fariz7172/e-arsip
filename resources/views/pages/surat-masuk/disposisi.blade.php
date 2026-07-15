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
    </style>

    <div class="disposisi-container">
        <div style="position: relative; text-align: center; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; min-height: 70px;">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" style="position: absolute; left: 0; width: 60px; height: auto;">
            <div class="font-bold title" style="margin-bottom: 0;">LEMBAR DISPOSISI / CATATAN</div>
        </div>
        
        <table class="row-table">
            <tr>
                <td style="width:80px;">Index</td>
                <td style="width:10px;">:</td>
                <td><span class="font-bold">{{ $surat->no_urut ?? '.................' }}</span></td>
                <td style="width:120px;">Tanggal Masuk</td>
                <td style="width:10px;">:</td>
                <td>{{ $surat->tgl_masuk ? \Carbon\Carbon::parse($surat->tgl_masuk)->format('d/m/Y') : '.................' }}</td>
                <td style="width:50px;">Kode</td>
                <td style="width:10px;">:</td>
                <td>.................</td>
            </tr>
        </table>

        <table class="row-table">
            <tr>
                <td class="col-label">Perihal ringkas</td>
                <td class="col-colon">:</td>
                <td style="border-bottom: 1px dotted #000;">{{ $surat->perihal ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-label">Tgl. / No Surat</td>
                <td class="col-colon">:</td>
                <td style="border-bottom: 1px dotted #000;">
                    {{ $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('d/m/Y') : '........' }} / {{ $surat->no_surat ?? '........' }}
                </td>
            </tr>
            <tr>
                <td class="col-label">Asal</td>
                <td class="col-colon">:</td>
                <td style="border-bottom: 1px dotted #000;">{{ $surat->asal_surat ?? '' }}</td>
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
                <div style="min-height: 200px; white-space: pre-wrap;">{{ $customText }}</div>
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
                <div class="check-item">
                    <span>6.</span>
                    <span class="check-box">{{ in_array('Satuan Pelaksana SDA Kecamatan Cilincing', $checkboxes) || in_array('Satuan Pelaksana SDA Kecamatan', $checkboxes) ? '✓' : '' }}</span>
                    <span>Satuan Pelaksana SDA Kecamatan.</span>
                </div>
                <div class="check-item">
                    <span>7.</span>
                    <span class="check-box"></span>
                    <span class="dotted-line" style="margin-top: 15px;"></span>
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
                    <td style="border-bottom: 1px dotted #000;"></td>
                </tr>
            </table>
            <div>*Coret yang tidak perlu</div>
        </div>
    </div>
</div>
