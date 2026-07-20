<?php

namespace App\Imports;

use App\Models\SuratKeluar;
use App\Models\Dokumen;
use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SuratKeluarImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $rowModel)
    {
        $row = $rowModel->toArray();
        
        // Extract hyperlinks from the row cells (if any)
        $hyperlinks = [];
        foreach ($rowModel->getDelegate()->getCellIterator() as $cell) {
            if ($cell->hasHyperlink()) {
                $hyperlinks[] = $cell->getHyperlink()->getUrl();
            }
        }
        
        // Add parsed hyperlinks to the array
        if (!empty($hyperlinks)) {
            $row['scan'] = implode(',', $hyperlinks);
        }

        // Skip rows that are empty or just have sequence numbers
        if (empty($row['kepada']) && empty($row['perihal']) && empty($row['no_surat'])) {
            return;
        }

        $tanggal = $this->parseDate($row['tanggal'] ?? null);
        $tgl_masuk_kasubag = $this->parseDate($row['masuk_kasubag'] ?? null);
        $tgl_masuk_kasudin = $this->parseDate($row['masuk_kasudin'] ?? null);
        $tgl_keluar = $this->parseDate($row['keluar'] ?? null);
        $tgl_dikembalikan_tu = $this->parseDate($row['dikembalikan_seksi'] ?? null);

        $no_urut = $row['no'] ?? null;
        
        $data = [
            'tanggal' => $tanggal,
            'no_surat' => $row['no_surat'] ?? null,
            'tujuan_surat' => Str::limit($row['kepada'] ?? '-', 250),
            'perihal' => Str::limit($row['perihal'] ?? '-', 500),
            'tembusan' => $row['tembusan'] ? Str::limit($row['tembusan'], 250) : null,
            'tgl_masuk_kasubag' => $tgl_masuk_kasubag,
            'tgl_masuk_kasudin' => $tgl_masuk_kasudin,
            'tgl_keluar' => $tgl_keluar,
            'tgl_dikembalikan_tu' => $tgl_dikembalikan_tu,
            'keterangan' => $row['keterangan'] ?? null,
            'bundle_id' => 2, // Default to bundle_id 2 for Surat Keluar
        ];

        // Process Scan File links
        if (!empty($row['scan'])) {
            $links = array_map('trim', explode(',', $row['scan']));
            $data['scan_file'] = $links;
        }

        // Find existing record or create new
        $surat = null;
        if (!empty($no_urut)) {
            $surat = SuratKeluar::where('no_urut', $no_urut)->first();
            $data['no_urut'] = $no_urut;
        }

        if ($surat) {
            $surat->update($data);
        } else {
            if (empty($data['no_urut'])) {
                $lastSurat = SuratKeluar::orderByRaw('CAST(no_urut AS UNSIGNED) DESC')->first();
                $data['no_urut'] = $lastSurat ? intval($lastSurat->no_urut) + 1 : 1;
            }
            $surat = SuratKeluar::create($data);
        }

        // Dokumen Integration
        $kategori = Kategori::firstOrCreate([
            'bundle_id' => 2,
            'nama' => 'Surat Keluar'
        ], [
            'kode' => 'SK',
            'urutan' => 99
        ]);

        $dokumen = null;
        if ($surat->dokumen_id) {
            $dokumen = Dokumen::find($surat->dokumen_id);
        }

        if (!$dokumen) {
            $dokumen = Dokumen::create([
                'kategori_id' => $kategori->id,
                'judul' => \Illuminate\Support\Str::limit('Surat Keluar: ' . ($surat->perihal ?? $surat->no_surat ?? '-'), 250),
                'tanggal_dokumen' => $surat->tanggal,
                'nomor_dokumen' => $surat->no_surat,
                'keterangan' => $surat->keterangan,
                'uploaded_by' => auth()->id() ?? 1
            ]);
            $surat->update(['dokumen_id' => $dokumen->id]);
        } else {
            $dokumen->update([
                'judul' => \Illuminate\Support\Str::limit('Surat Keluar: ' . ($surat->perihal ?? $surat->no_surat ?? '-'), 250),
                'tanggal_dokumen' => $surat->tanggal,
                'nomor_dokumen' => $surat->no_surat,
                'keterangan' => $surat->keterangan,
            ]);
        }

        // --- Sinkronisasi Link Eksternal sebagai FileAttachment ---
        if (!empty($data['scan_file']) && $dokumen->id) {
            foreach ($data['scan_file'] as $scanStr) {
                if (\Illuminate\Support\Str::startsWith($scanStr, 'http')) {
                    \App\Models\FileAttachment::firstOrCreate([
                        'dokumen_id' => $dokumen->id,
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
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        if (is_numeric($date)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))->format('Y-m-d');
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
