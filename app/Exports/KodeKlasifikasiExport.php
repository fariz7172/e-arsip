<?php

namespace App\Exports;

use App\Models\KodeKlasifikasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KodeKlasifikasiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = KodeKlasifikasi::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('kode', 'like', '%' . $this->search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('nomor', 'asc')->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Klasifikasi',
            'Deskripsi',
        ];
    }

    public function map($item): array
    {
        static $counter = 0;
        $counter++;

        return [
            $item->nomor ?: $counter,
            $item->kode,
            $item->deskripsi ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E293B'],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);
    }
}

