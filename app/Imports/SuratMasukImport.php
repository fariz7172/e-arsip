<?php

namespace App\Imports;

use App\Models\SuratMasuk;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Row;

class SuratMasukImport implements OnEachRow, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function onRow(Row $rowModel)
    {
        $row = $rowModel->toArray();
        // Extract hyperlinks from the row cells
        $hyperlinks = [];
        foreach ($rowModel->getDelegate()->getCellIterator() as $cell) {
            if ($cell->hasHyperlink()) {
                $hyperlinks[] = $cell->getHyperlink()->getUrl();
            }
        }
        
        // If we found any hyperlinks, add them to the 'scan' column
        if (!empty($hyperlinks)) {
            $row['scan'] = implode(',', $hyperlinks);
        }
        // Skip empty rows (or rows that only have a 'no' column filled due to Excel drag)
        if (empty($row['no_lembar_disposisi']) && empty($row['perihal_berkas']) && empty($row['no_surat'])) {
            return;
        }

        $tanggal = $this->parseDate($row['tanggal'] ?? null);
        $tglAcara = $this->parseDate($row['tanggal_acara'] ?? null);
        $tglMasuk = $this->parseDate($row['masuk'] ?? null);
        $tglKeluar = $this->parseDate($row['keluar'] ?? null);
        $tglDikembalikan = $this->parseDate($row['dikembalikan_tu'] ?? null);

        $id = $row['no'] ?? null;
        $kode = $row['no_lembar_disposisi'] ?? null;
        
        $data = [
            'kode' => $kode,
            'tanggal' => $tanggal,
            'no_surat' => $row['no_surat'] ?? null,
            'perihal' => isset($row['perihal_berkas']) ? \Illuminate\Support\Str::limit($row['perihal_berkas'], 250) : null,
            'asal_surat' => $row['asal_surat'] ?? null,
            'tanggal_acara' => $tglAcara,
            'waktu_acara' => $row['waktu'] ?? null,
            'tempat_acara' => $row['tempat'] ?? null,
            'tgl_masuk' => $tglMasuk,
            'tgl_keluar' => $tglKeluar,
            'tgl_dikembalikan' => $tglDikembalikan,
            'distribusi' => $row['distribusi_disposisi'] ?? null,
            'sifat_surat' => $row['sifat_surat'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
            'scan_file' => !empty($row['scan']) ? array_map('trim', explode(',', $row['scan'])) : null,
            'bundle_id' => 1,
        ];

        $kode = $row['no_lembar_disposisi'] ?? null;

        // Pastikan Kategori Surat Masuk ada di Bundle 1
        $kategori = \App\Models\Kategori::firstOrCreate([
            'bundle_id' => 1,
            'nama' => 'Surat Masuk'
        ], [
            'kode' => 'SM',
            'urutan' => 98
        ]);

        // Upsert behavior: Update ONLY if NO. LEMBAR DISPOSISI (kode) matches
        $existing = null;
        if ($kode) {
            $existing = SuratMasuk::where('kode', $kode)->first();
        }

        $dokumen_id = null;

        if ($existing) {
            if ($existing->dokumen_id) {
                $dokumen_id = $existing->dokumen_id;
                \App\Models\Dokumen::where('id', $dokumen_id)->update([
                    'judul' => \Illuminate\Support\Str::limit('Surat Masuk: ' . ($data['perihal'] ?? $data['no_surat'] ?? '-'), 250),
                    'tanggal_dokumen' => $tanggal,
                    'nomor_dokumen' => $data['no_surat'],
                    'keterangan' => $data['keterangan'],
                ]);
            } else {
                $dok = \App\Models\Dokumen::create([
                    'kategori_id' => $kategori->id,
                    'judul' => \Illuminate\Support\Str::limit('Surat Masuk: ' . ($data['perihal'] ?? $data['no_surat'] ?? '-'), 250),
                    'tanggal_dokumen' => $tanggal,
                    'nomor_dokumen' => $data['no_surat'],
                    'keterangan' => $data['keterangan'],
                    'uploaded_by' => auth()->id() ?? 1
                ]);
                $dokumen_id = $dok->id;
                $data['dokumen_id'] = $dokumen_id;
            }

            $existing->update($data);
        } else {
            $dokumen = \App\Models\Dokumen::create([
                'kategori_id' => $kategori->id,
                'judul' => \Illuminate\Support\Str::limit('Surat Masuk: ' . ($data['perihal'] ?? $data['no_surat'] ?? '-'), 250),
                'tanggal_dokumen' => $tanggal,
                'nomor_dokumen' => $data['no_surat'],
                'keterangan' => $data['keterangan'],
                'uploaded_by' => auth()->id() ?? 1
            ]);
            
            $dokumen_id = $dokumen->id;
            $data['dokumen_id'] = $dokumen_id;
        }

        // --- Sinkronisasi Link Eksternal sebagai FileAttachment ---
        if (!empty($data['scan_file']) && $dokumen_id) {
            foreach ($data['scan_file'] as $scanStr) {
                if (\Illuminate\Support\Str::startsWith($scanStr, 'http')) {
                    \App\Models\FileAttachment::firstOrCreate([
                        'dokumen_id' => $dokumen_id,
                        'path' => $scanStr,
                    ], [
                        'nama_file' => 'Tautan Eksternal / Google Drive',
                        'disk' => 'url',
                        'mime_type' => 'application/url',
                        'ukuran' => 0
                    ]);
                }
            }
        }

        if ($existing) {
            return;
        }

        SuratMasuk::create($data);
    }
    
    private function parseDate($value)
    {
        if (empty($value)) return null;
        
        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
