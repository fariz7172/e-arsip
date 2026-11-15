<?php

namespace App\Exports;

use App\Models\SuratKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuratKeluarExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = SuratKeluar::query();

        if (!empty($this->filters['tanggal_surat_dari'])) {
            $query->whereDate('tanggal', '>=', $this->filters['tanggal_surat_dari']);
        }

        if (!empty($this->filters['tanggal_surat_sampai'])) {
            $query->whereDate('tanggal', '<=', $this->filters['tanggal_surat_sampai']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('no_surat', 'like', '%' . $search . '%')
                  ->orWhere('no_urut', 'like', '%' . $search . '%')
                  ->orWhere('perihal', 'like', '%' . $search . '%')
                  ->orWhere('tujuan_surat', 'like', '%' . $search . '%')
                  ->orWhere('tembusan', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('tanggal', 'desc')->orderByRaw('CAST(no_urut AS UNSIGNED) DESC')->get();
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
