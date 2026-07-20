<?php

namespace App\Exports;

use App\Models\SuratKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuratKeluarExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return SuratKeluar::orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            "NO",
            "TANGGAL",
            "NO. SURAT",
            "KEPADA",
            "PERIHAL",
            "TEMBUSAN",
            "MASUK KASUBAG",
            "MASUK KASUDIN",
            "KELUAR",
            "DIKEMBALIKAN SEKSI",
            "KETERANGAN",
            "SCAN"
        ];
    }

    public function map($surat): array
    {
        // Handle scan files (array to string)
        $scans = is_array($surat->scan_file) ? implode(', ', $surat->scan_file) : $surat->scan_file;

        return [
            $surat->no_urut,
            $surat->tanggal,
            $surat->no_surat,
            $surat->tujuan_surat,
            $surat->perihal,
            $surat->tembusan,
            $surat->tgl_masuk_kasubag,
            $surat->tgl_masuk_kasudin,
            $surat->tgl_keluar,
            $surat->tgl_dikembalikan_tu,
            $surat->keterangan,
            $scans,
        ];
    }
}
