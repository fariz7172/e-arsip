<?php

namespace App\Imports;

use App\Models\KodeKlasifikasi;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class KodeKlasifikasiImport implements OnEachRow, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected int $importedCount = 0;

    public function onRow(Row $rowModel)
    {
        $row = $rowModel->toArray();

        // Cari nilai kode dari berbagai kemungkinan penamaan header
        $kode = $row['kode_klarifikasi'] 
            ?? $row['kode_klasifikasi'] 
            ?? $row['kode'] 
            ?? $row[1] 
            ?? null;

        if (empty($kode)) {
            return;
        }

        $kode = trim((string)$kode);
        if ($kode === '' || strtolower($kode) === 'kode klarifikasi' || strtolower($kode) === 'kode klasifikasi') {
            return;
        }

        // Nomor urut
        $nomor = $row['no'] ?? $row['nomor'] ?? $row[0] ?? null;
        if ($nomor !== null && is_numeric($nomor)) {
            $nomor = (int)$nomor;
        } else {
            $nomor = null;
        }

        // Deskripsi
        $deskripsi = $row['deskripsi'] ?? $row['keterangan'] ?? $row[2] ?? null;
        $deskripsi = ($deskripsi !== null && trim((string)$deskripsi) !== '') ? trim((string)$deskripsi) : null;

        // Upsert berdasarkan kode
        KodeKlasifikasi::updateOrCreate(
            ['kode' => $kode],
            [
                'nomor' => $nomor,
                'deskripsi' => $deskripsi,
            ]
        );

        $this->importedCount++;
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}

