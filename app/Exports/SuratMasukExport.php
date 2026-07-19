<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SuratMasukExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return SuratMasuk::orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'NO',
            'NO ID',
            'TANGGAL',
            'NO. LEMBAR DISPOSISI',
            'NO. SURAT',
            'PERIHAL BERKAS',
            'ASAL SURAT',
            'TANGGAL ACARA',
            'WAKTU',
            'TEMPAT',
            'MASUK',
            'KELUAR',
            'DIKEMBALIKAN TU',
            'DISTRIBUSI DISPOSISI',
            'DISPOSISI',
            'SIFAT SURAT',
            'KETERANGAN',
            'SCAN'
        ];
    }

    public function map($surat): array
    {
        return [
            $surat->no_urut,
            $surat->id,
            $surat->tanggal ? \Carbon\Carbon::parse($surat->tanggal)->format('Y-m-d') : '',
            $surat->kode, // Mapped to NO. LEMBAR DISPOSISI
            $surat->no_surat,
            $surat->perihal,
            $surat->asal_surat,
            $surat->tanggal_acara ? \Carbon\Carbon::parse($surat->tanggal_acara)->format('Y-m-d') : '',
            $surat->waktu_acara,
            $surat->tempat_acara,
            $surat->tgl_masuk ? \Carbon\Carbon::parse($surat->tgl_masuk)->format('Y-m-d') : '',
            $surat->tgl_keluar ? \Carbon\Carbon::parse($surat->tgl_keluar)->format('Y-m-d') : '',
            $surat->tgl_dikembalikan ? \Carbon\Carbon::parse($surat->tgl_dikembalikan)->format('Y-m-d') : '',
            $surat->distribusi,
            '', // DISPOSISI empty as per user instruction
            $surat->sifat_surat,
            $surat->keterangan,
            is_array($surat->scan_file) ? implode(', ', $surat->scan_file) : $surat->scan_file
        ];
    }
}
