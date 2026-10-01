<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SuratMasukArsipExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return SuratMasuk::orderByRaw('CASE WHEN kode IS NULL OR kode = "" THEN 1 ELSE 0 END, CAST(kode AS UNSIGNED) ASC, id ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NOMOR BERKAS',
            'NAMA BERKAS',
            'NO. ITEM ARSIP',
            'NO SURAT',
            'NO DISPOSISI',
            'KODE KLASIFIKASI',
            'URAIAN INFORMASI ARSIP',
            'TANGGAL',
            'JUMLAH LEMBAR',
            'KETERANGAN',
            'PEJABAT PENANDATANGAN',
            'TINGKAT PERKEMBANGAN',
            'LOKASI SIMPAN',
            'ASAL SURAT',
            'SURAT KELUAR (Kepada)',
        ];
    }

    public function map($surat): array
    {
        // Format tanggal (mengambil tanggal_acara, fallback ke tanggal surat jika tanggal_acara null)
        $tanggal = '';
        if (!empty($surat->tanggal_acara)) {
            try {
                $tanggal = \Carbon\Carbon::parse($surat->tanggal_acara)->format('d/m/Y');
            } catch (\Exception $e) {
                $tanggal = (string)$surat->tanggal_acara;
            }
        } elseif (!empty($surat->tanggal)) {
            try {
                $tanggal = \Carbon\Carbon::parse($surat->tanggal)->format('d/m/Y');
            } catch (\Exception $e) {
                $tanggal = (string)$surat->tanggal;
            }
        }

        return [
            1,                                          // NOMOR BERKAS
            'Sumber Daya Air',                          // NAMA BERKAS
            $surat->kode ?? '',                         // NO. ITEM ARSIP
            $surat->no_surat ?? '',                     // NO SURAT
            '',                                         // NO DISPOSISI
            'KR.01',                                    // KODE KLASIFIKASI
            $surat->perihal ?? '',                      // URAIAN INFORMASI ARSIP
            $tanggal,                                   // TANGGAL
            1,                                          // JUMLAH LEMBAR
            'Tekstual',                                 // KETERANGAN
            'Heria Suwandi',                            // PEJABAT PENANDATANGAN
            'Asli',                                     // TINGKAT PERKEMBANGAN
            'Boks 1',                                   // LOKASI SIMPAN
            '',                                         // ASAL SURAT (SKIP / Dikosongkan)
            $surat->asal_surat ?? '',                   // SURAT KELUAR (Kepada)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style Heading Row 1
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['argb' => 'FF0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE2E8F0'],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Styling data cells
        if ($highestRow > 1) {
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H2:N{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Vertical center for all cells
            $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Wrap text for Uraian Informasi (G) and Kepada (O)
            $sheet->getStyle("G2:G{$highestRow}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("O2:O{$highestRow}")->getAlignment()->setWrapText(true);

            // Set borders for entire table
            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCBD5E1'],
                    ],
                ],
            ]);
        }

        return [];
    }
}
