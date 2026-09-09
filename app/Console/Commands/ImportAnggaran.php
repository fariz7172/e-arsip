<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Anggaran;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAnggaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-anggaran {file=public/assets/daftar-kode.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Master Anggaran from Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path($this->argument('file'));
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Importing from {$filePath}...");

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        $currentProgramId = null;
        $currentKegiatanId = null;
        $currentSubKegiatanId = null;
        $currentAktivitasId = null;

        // Kosongkan tabel dulu
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Anggaran::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $count = 0;

        foreach ($worksheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE);
            
            $data = [];
            foreach ($cellIterator as $cell) {
                $data[] = $cell->getValue();
            }

            $colA = trim((string)($data[0] ?? ''));
            $colB = trim((string)($data[1] ?? ''));
            $colC = trim((string)($data[2] ?? ''));
            $colD = trim((string)($data[3] ?? ''));
            $colE = trim((string)($data[4] ?? ''));
            $colF = $data[5] ?? null; // Pagu

            if (empty($colA) && empty($colB) && empty($colC) && empty($colD) && empty($colE)) {
                continue; // Skip empty row
            }

            // Program (Col A)
            if (!empty($colA) && empty($colB) && empty($colC)) {
                // Ignore "Belanja Langsung" or headers
                if (strpos($colA, 'Belanja') !== false || strpos($colA, 'Program') !== false && strpos($colA, '.') === false) {
                    continue;
                }
                
                $parts = explode(' ', $colA, 2);
                $kode = $parts[0] ?? '';
                $nama = $parts[1] ?? '';

                $program = Anggaran::create([
                    'parent_id' => null,
                    'tipe' => 'program',
                    'kode' => $kode,
                    'nama' => $nama,
                    'pagu' => is_numeric($colF) ? $colF : null,
                ]);
                $currentProgramId = $program->id;
                $currentKegiatanId = null;
                $currentSubKegiatanId = null;
                $currentAktivitasId = null;
                $count++;
                continue;
            }

            // Kegiatan (Col B)
            if (!empty($colB) && empty($colC)) {
                $parts = explode(' ', $colB, 2);
                $kode = $parts[0] ?? '';
                $nama = $parts[1] ?? '';

                $kegiatan = Anggaran::create([
                    'parent_id' => $currentProgramId,
                    'tipe' => 'kegiatan',
                    'kode' => $kode,
                    'nama' => $nama,
                    'pagu' => is_numeric($colF) ? $colF : null,
                ]);
                $currentKegiatanId = $kegiatan->id;
                $currentSubKegiatanId = null;
                $currentAktivitasId = null;
                $count++;
                continue;
            }

            // Sub Kegiatan (Col C)
            if (!empty($colC) && empty($colD)) {
                $parts = explode(' ', $colC, 2);
                $kode = $parts[0] ?? '';
                $nama = $parts[1] ?? '';

                $sub = Anggaran::create([
                    'parent_id' => $currentKegiatanId,
                    'tipe' => 'sub_kegiatan',
                    'kode' => $kode,
                    'nama' => $nama,
                    'pagu' => is_numeric($colF) ? $colF : null,
                ]);
                $currentSubKegiatanId = $sub->id;
                $currentAktivitasId = null;
                $count++;
                continue;
            }

            // Aktivitas (Col D ada, Col E kosong)
            if (!empty($colD) && empty($colE)) {
                $parts = explode(' ', $colD, 2);
                $kode = $parts[0] ?? '';
                $nama = $parts[1] ?? '';

                $aktivitas = Anggaran::create([
                    'parent_id' => $currentSubKegiatanId,
                    'tipe' => 'aktivitas',
                    'kode' => $kode,
                    'nama' => $nama,
                    'pagu' => is_numeric($colF) ? $colF : null,
                ]);
                $currentAktivitasId = $aktivitas->id;
                $count++;
                continue;
            }

            // Rekening (Col D ada, Col E ada)
            if (!empty($colD) && !empty($colE)) {
                Anggaran::create([
                    'parent_id' => $currentAktivitasId,
                    'tipe' => 'rekening',
                    'kode' => $colD,
                    'nama' => $colE,
                    'pagu' => is_numeric($colF) ? $colF : null,
                ]);
                $count++;
                continue;
            }
        }

        $this->info("Import selesai! Total data masuk: {$count}");
    }
}
