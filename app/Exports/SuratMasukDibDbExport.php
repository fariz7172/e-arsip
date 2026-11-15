<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use App\Models\KodeKlasifikasi;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SuratMasukDibDbExport
{
    protected ?array $ids;
    protected array $filters;
    protected ?string $tahun;

    public function __construct(?array $ids = null, array $filters = [], ?string $tahun = null)
    {
        $this->ids = $ids;
        $this->filters = $filters;
        $this->tahun = $tahun;
    }

    public static function extractKodeKlasifikasi(?string $noSurat): string
    {
        if (empty($noSurat) || strpos($noSurat, '/') === false) {
            return '-';
        }
        $parts = explode('/', $noSurat);
        $candidate = trim($parts[1] ?? '');
        return $candidate !== '' ? $candidate : '-';
    }

    public function createSpreadsheet(): Spreadsheet
    {
        $query = SuratMasuk::query();

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        } else {
            if (!empty($this->filters['tanggal_surat_dari'])) {
                $query->whereDate('tanggal', '>=', $this->filters['tanggal_surat_dari']);
            }
            if (!empty($this->filters['tanggal_surat_sampai'])) {
                $query->whereDate('tanggal', '<=', $this->filters['tanggal_surat_sampai']);
            }
            if (!empty($this->filters['tanggal_acara_dari'])) {
                $query->whereDate('tanggal_acara', '>=', $this->filters['tanggal_acara_dari']);
            }
            if (!empty($this->filters['tanggal_acara_sampai'])) {
                $query->whereDate('tanggal_acara', '<=', $this->filters['tanggal_acara_sampai']);
            }
            if (!empty($this->filters['search'])) {
                $search = $this->filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('no_surat', 'like', '%' . $search . '%')
                      ->orWhere('kode', 'like', '%' . $search . '%')
                      ->orWhere('perihal', 'like', '%' . $search . '%')
                      ->orWhere('asal_surat', 'like', '%' . $search . '%')
                      ->orWhere('distribusi', 'like', '%' . $search . '%');
                });
            }
        }

        $surats = $query->get();

        // Map and extract Kode Klasifikasi, then sort by Kode Klasifikasi ascending
        $mappedItems = $surats->map(function ($surat) {
            $extractedKode = self::extractKodeKlasifikasi($surat->no_surat);
            $year = $surat->tanggal ? Carbon::parse($surat->tanggal)->format('Y') : date('Y');
            $tglFormatted = $surat->tanggal ? Carbon::parse($surat->tanggal)->format('d/m/Y') : '';

            return [
                'id' => $surat->id,
                'nomor_berkas' => 1,
                'nama_berkas' => 'Sumber Daya Air',
                'no_item_arsip' => $surat->kode ?? '',
                'no_surat' => $surat->no_surat ?? '',
                'no_disposisi' => '',
                'kode_klasifikasi' => $extractedKode,
                'uraian_informasi' => $surat->perihal ?? '',
                'tanggal' => $tglFormatted,
                'jumlah_lembar' => $surat->jumlah_lembar ? (int)$surat->jumlah_lembar : 1,
                'keterangan' => 'Tekstual',
                'pejabat_penandatangan' => 'Heria Suwandi',
                'tingkat_perkembangan' => 'Asli',
                'lokasi_simpan' => 'Boks 1',
                'asal_surat' => $surat->asal_surat ?? '',
                'kepada' => $surat->distribusi ?? '',
                'year' => $year,
            ];
        })->sortBy('kode_klasifikasi', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $spreadsheet = new Spreadsheet();

        // ------------------------------------------------------------------
        // SHEET 1: DIB (Daftar Isi Berkas Arsip)
        // ------------------------------------------------------------------
        $dibSheet = $spreadsheet->getActiveSheet();
        $dibSheet->setTitle('DIB');

        // Row 1: Title
        $dibSheet->mergeCells('A1:O1');
        $dibSheet->setCellValue('A1', 'DAFTAR ISI BERKAS ARSIP');
        $dibSheet->getStyle('A1')->getFont()->setName('Arial')->setSize(14)->setBold(true);
        $dibSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 3: Unit Pengolah
        $dibSheet->mergeCells('A3:O3');
        $dibSheet->setCellValue('A3', 'Unit Pengolah : Subbag Tata Usaha');
        $dibSheet->getStyle('A3')->getFont()->setName('Arial')->setSize(11)->setBold(true);
        $dibSheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 4: Kop Surat Resmi
        $dibSheet->mergeCells('A4:O4');
        $kopText = "PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA\nDINAS SUMBER DAYA AIR\nSUKU DINAS SUMBER DAYA AIR\nKOTA ADMINISTRASI JAKARTA UTARA\nJl. Yos Sudarso Nomor 27-29 \nTelp: 021-43902028, Fax: 021-43902028, email : sudinsdaju@gmail.com\nJ A K A R T A \nKode Pos : 14320";
        $dibSheet->setCellValue('A4', $kopText);
        $dibSheet->getStyle('A4')->getFont()->setName('Arial')->setSize(10)->setBold(true);
        $dibSheet->getStyle('A4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $dibSheet->getRowDimension(4)->setRowHeight(95);

        // Logo DKI Jakarta
        $logoPath = public_path('assets/logo.png');
        if (file_exists($logoPath)) {
            $drawingDib = new Drawing();
            $drawingDib->setName('Logo DKI Jakarta');
            $drawingDib->setDescription('Logo DKI Jakarta');
            $drawingDib->setPath($logoPath);
            $drawingDib->setHeight(85);
            $drawingDib->setCoordinates('A4');
            $drawingDib->setOffsetX(20);
            $drawingDib->setOffsetY(5);
            $drawingDib->setWorksheet($dibSheet);
        }

        // Row 5 & 6: Headers
        $dibSheet->mergeCells('A5:A6')->setCellValue('A5', 'NOMOR BERKAS');
        $dibSheet->mergeCells('B5:B6')->setCellValue('B5', 'NAMA BERKAS');
        $dibSheet->mergeCells('C5:C6')->setCellValue('C5', 'NO. ITEM ARSIP');
        $dibSheet->mergeCells('D5:D6')->setCellValue('D5', 'NO SURAT');
        $dibSheet->setCellValue('E5', 'NO');
        $dibSheet->setCellValue('E6', 'DISPOSISI');
        $dibSheet->mergeCells('F5:F6')->setCellValue('F5', 'KODE KLASIFIKASI');
        $dibSheet->mergeCells('G5:G6')->setCellValue('G5', 'URAIAN INFORMASI ARSIP');
        $dibSheet->mergeCells('H5:H6')->setCellValue('H5', 'TANGGAL');
        $dibSheet->mergeCells('I5:I6')->setCellValue('I5', 'JUMLAH LEMBAR ');
        $dibSheet->mergeCells('J5:J6')->setCellValue('J5', 'KETERANGAN');
        $dibSheet->mergeCells('K5:K6')->setCellValue('K5', 'PEJABAT PENANDATANGAN');
        $dibSheet->mergeCells('L5:L6')->setCellValue('L5', 'TINGKAT PERKEMBANGAN');
        $dibSheet->mergeCells('M5:M6')->setCellValue('M5', 'LOKASI SIMPAN');
        $dibSheet->mergeCells('N5:N6')->setCellValue('N5', 'ASAL SURAT');
        $dibSheet->mergeCells('O5:O6')->setCellValue('O5', 'SURAT KELUAR (Kepada)');

        $dibSheet->getStyle('A5:O6')->getFont()->setName('Arial')->setSize(10)->setBold(true);
        $dibSheet->getStyle('A5:O6')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $dibSheet->getStyle('A5:O6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        // Row 7: (1) .. (14)
        $colNumbers = ['(1)', '(2)', '(3)', '(4)', '', '(5)', '(6)', '(7)', '(8)', '(9)', '(10)', '(11)', '(12)', '(13)', '(14)'];
        for ($i = 0; $i < count($colNumbers); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $dibSheet->setCellValue("{$colLetter}7", $colNumbers[$i]);
        }
        $dibSheet->getStyle('A7:O7')->getFont()->setName('Arial')->setSize(9)->setBold(true);
        $dibSheet->getStyle('A7:O7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $dibSheet->getStyle('A7:O7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // Column widths for DIB
        $colWidthsDIB = [
            'A' => 14,
            'B' => 30,
            'C' => 16,
            'D' => 20,
            'E' => 12,
            'F' => 22,
            'G' => 45,
            'H' => 16,
            'I' => 14,
            'J' => 16,
            'K' => 24,
            'L' => 16,
            'M' => 16,
            'N' => 28,
            'O' => 35,
        ];
        foreach ($colWidthsDIB as $col => $w) {
            $dibSheet->getColumnDimension($col)->setWidth($w);
        }

        // Fill DIB Data rows
        $rowIdx = 8;
        foreach ($mappedItems as $item) {
            $dibSheet->setCellValue("A{$rowIdx}", $item['nomor_berkas']);
            $dibSheet->setCellValue("B{$rowIdx}", $item['nama_berkas']);
            $dibSheet->setCellValue("C{$rowIdx}", $item['no_item_arsip']);
            $dibSheet->setCellValue("D{$rowIdx}", $item['no_surat']);
            $dibSheet->setCellValue("E{$rowIdx}", $item['no_disposisi']);
            $dibSheet->setCellValue("F{$rowIdx}", $item['kode_klasifikasi']);
            $dibSheet->setCellValue("G{$rowIdx}", $item['uraian_informasi']);
            $dibSheet->setCellValue("H{$rowIdx}", $item['tanggal']);
            $dibSheet->setCellValue("I{$rowIdx}", $item['jumlah_lembar']);
            $dibSheet->setCellValue("J{$rowIdx}", $item['keterangan']);
            $dibSheet->setCellValue("K{$rowIdx}", $item['pejabat_penandatangan']);
            $dibSheet->setCellValue("L{$rowIdx}", $item['tingkat_perkembangan']);
            $dibSheet->setCellValue("M{$rowIdx}", $item['lokasi_simpan']);
            $dibSheet->setCellValue("N{$rowIdx}", $item['asal_surat']);
            $dibSheet->setCellValue("O{$rowIdx}", $item['kepada']);
            $rowIdx++;
        }

        $highestDIBRow = max($rowIdx - 1, 7);
        // Apply table styling to DIB
        $dibSheet->getStyle("A5:O{$highestDIBRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF94A3B8'],
                ],
            ],
        ]);
        if ($highestDIBRow >= 8) {
            $dibSheet->getStyle("A8:A{$highestDIBRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $dibSheet->getStyle("C8:F{$highestDIBRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $dibSheet->getStyle("H8:M{$highestDIBRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $dibSheet->getStyle("A8:O{$highestDIBRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $dibSheet->getStyle("G8:G{$highestDIBRow}")->getAlignment()->setWrapText(true);
            $dibSheet->getStyle("O8:O{$highestDIBRow}")->getAlignment()->setWrapText(true);
        }

        // ------------------------------------------------------------------
        // SHEET 2: DB (Daftar Berkas Arsip)
        // ------------------------------------------------------------------
        $dbSheet = $spreadsheet->createSheet();
        $dbSheet->setTitle('DB');

        // Row 1: Title
        $dbSheet->mergeCells('A1:F1');
        $dbSheet->setCellValue('A1', 'DAFTAR BERKAS ARSIP');
        $dbSheet->getStyle('A1')->getFont()->setName('Arial')->setSize(14)->setBold(true);
        $dbSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 3: Unit Pengolah
        $dbSheet->mergeCells('A3:F3');
        $dbSheet->setCellValue('A3', 'Unit Pengolah : Subbag Tata Usaha');
        $dbSheet->getStyle('A3')->getFont()->setName('Arial')->setSize(11)->setBold(true);
        $dbSheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 4: Kop
        $dbSheet->mergeCells('A4:F4');
        $dbSheet->setCellValue('A4', $kopText);
        $dbSheet->getStyle('A4')->getFont()->setName('Arial')->setSize(10)->setBold(true);
        $dbSheet->getStyle('A4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $dbSheet->getRowDimension(4)->setRowHeight(95);

        // Logo DKI Jakarta
        if (file_exists($logoPath)) {
            $drawingDb = new Drawing();
            $drawingDb->setName('Logo DKI Jakarta');
            $drawingDb->setDescription('Logo DKI Jakarta');
            $drawingDb->setPath($logoPath);
            $drawingDb->setHeight(85);
            $drawingDb->setCoordinates('A4');
            $drawingDb->setOffsetX(15);
            $drawingDb->setOffsetY(5);
            $drawingDb->setWorksheet($dbSheet);
        }

        // Row 5: Headers
        $dbSheet->setCellValue('A5', 'NO');
        $dbSheet->setCellValue('B5', 'KODE KLASIFIKASI');
        $dbSheet->setCellValue('C5', 'JUDUL NASKAH');
        $dbSheet->setCellValue('D5', 'KURUN WAKTU');
        $dbSheet->setCellValue('E5', 'JUMLAH ITEM');
        $dbSheet->setCellValue('F5', 'KETERANGAN');
        $dbSheet->getStyle('A5:F5')->getFont()->setName('Arial')->setSize(10)->setBold(true);
        $dbSheet->getStyle('A5:F5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $dbSheet->getStyle('A5:F5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        // Row 6: (1) .. (6)
        $dbColNumbers = ['(1)', '(2)', '(3)', '(4)', '(5)', '(6)'];
        for ($i = 0; $i < count($dbColNumbers); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $dbSheet->setCellValue("{$colLetter}6", $dbColNumbers[$i]);
        }
        $dbSheet->getStyle('A6:F6')->getFont()->setName('Arial')->setSize(9)->setBold(true);
        $dbSheet->getStyle('A6:F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $dbSheet->getStyle('A6:F6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // Column widths for DB
        $colWidthsDB = [
            'A' => 8,
            'B' => 20,
            'C' => 50,
            'D' => 20,
            'E' => 16,
            'F' => 18,
        ];
        foreach ($colWidthsDB as $col => $w) {
            $dbSheet->getColumnDimension($col)->setWidth($w);
        }

        // Group by Kode Klasifikasi
        $dbGroups = $mappedItems->groupBy('kode_klasifikasi');
        $dbRowIdx = 7;
        $noUrut = 1;

        foreach ($dbGroups as $kode => $groupItems) {
            // Judul naskah: lookup from KodeKlasifikasi model or default
            $judul = KodeKlasifikasi::where('kode', $kode)->value('deskripsi');
            if (empty($judul)) {
                $judul = $kode === '-' ? 'Lain-lain / Berkas Tanpa Kode' : 'Sumber Daya Air';
            }

            // Kurun Waktu
            if (!empty($this->tahun)) {
                $kurunWaktu = 'Tahun ' . $this->tahun;
            } else {
                $distinctYears = $groupItems->pluck('year')->filter()->unique()->sort();
                $kurunWaktu = $distinctYears->count() > 0 ? 'Tahun ' . $distinctYears->implode(' - ') : 'Tahun ' . date('Y');
            }

            $itemCount = $groupItems->count();

            $dbSheet->setCellValue("A{$dbRowIdx}", $noUrut);
            $dbSheet->setCellValue("B{$dbRowIdx}", $kode);
            $dbSheet->setCellValue("C{$dbRowIdx}", $judul);
            $dbSheet->setCellValue("D{$dbRowIdx}", $kurunWaktu);
            $dbSheet->setCellValue("E{$dbRowIdx}", $itemCount);
            $dbSheet->setCellValue("F{$dbRowIdx}", 'Tekstual');

            $noUrut++;
            $dbRowIdx++;
        }

        $highestDBRow = max($dbRowIdx - 1, 6);
        // Apply table styling to DB
        $dbSheet->getStyle("A5:F{$highestDBRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF94A3B8'],
                ],
            ],
        ]);
        if ($highestDBRow >= 7) {
            $dbSheet->getStyle("A7:B{$highestDBRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $dbSheet->getStyle("D7:F{$highestDBRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $dbSheet->getStyle("A7:F{$highestDBRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $dbSheet->getStyle("C7:C{$highestDBRow}")->getAlignment()->setWrapText(true);
        }

        // Set DIB as active sheet
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}

