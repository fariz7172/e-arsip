<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KodeKlasifikasiTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            [1, 'BM.00.01', 'Contoh deskripsi klasifikasi BM.00.01'],
            [2, 'BM.00.03', 'Contoh deskripsi klasifikasi BM.00.03'],
            [3, 'KR.01', 'Sumber Daya Air'],
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Klasifikasi',
            'Deskripsi',
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
                'startColor' => ['argb' => 'FF0284C7'],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);
    }
}

