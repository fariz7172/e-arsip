<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Dokumen;
use App\Models\Kategori;
use App\Models\FileAttachment;
use Illuminate\Support\Str;

class MigrateSuratToDokumen extends Command
{
    protected $signature = 'app:migrate-surat-to-dokumen';
    protected $description = 'Migrate existing SuratMasuk and SuratKeluar to Dokumen';

    public function handle()
    {
        $this->info('Starting migration for Surat Masuk...');
        $suratMasuks = SuratMasuk::whereNull('dokumen_id')->whereNotNull('bundle_id')->get();
        foreach ($suratMasuks as $surat) {
            $kategori = Kategori::firstOrCreate([
                'bundle_id' => $surat->bundle_id,
                'nama' => 'Surat Masuk'
            ], [
                'kode' => 'SM',
                'urutan' => 98
            ]);

            $dokumen = Dokumen::create([
                'kategori_id' => $kategori->id,
                'judul' => 'Surat Masuk: ' . ($surat->perihal ?? $surat->no_surat ?? '-'),
                'tanggal_dokumen' => $surat->tanggal,
                'nomor_dokumen' => $surat->no_surat,
                'keterangan' => $surat->keterangan,
                'uploaded_by' => 1 // as fallback
            ]);
            
            $surat->update(['dokumen_id' => $dokumen->id]);

            if (is_array($surat->scan_file)) {
                foreach ($surat->scan_file as $path) {
                    FileAttachment::create([
                        'dokumen_id' => $dokumen->id,
                        'nama_file' => Str::afterLast($path, '/'),
                        'path' => $path,
                        'disk' => 'public',
                        'mime_type' => 'application/octet-stream',
                        'ukuran' => 0
                    ]);
                }
            }
        }

        $this->info('Starting migration for Surat Keluar...');
        $suratKeluars = SuratKeluar::whereNull('dokumen_id')->whereNotNull('bundle_id')->get();
        foreach ($suratKeluars as $surat) {
            $kategori = Kategori::firstOrCreate([
                'bundle_id' => $surat->bundle_id,
                'nama' => 'Surat Keluar'
            ], [
                'kode' => 'SK',
                'urutan' => 99
            ]);

            $dokumen = Dokumen::create([
                'kategori_id' => $kategori->id,
                'judul' => 'Surat Keluar: ' . ($surat->perihal ?? $surat->no_surat ?? '-'),
                'tanggal_dokumen' => $surat->tanggal,
                'nomor_dokumen' => $surat->no_surat,
                'keterangan' => $surat->keterangan,
                'uploaded_by' => 1
            ]);
            
            $surat->update(['dokumen_id' => $dokumen->id]);

            if (is_array($surat->scan_file)) {
                foreach ($surat->scan_file as $path) {
                    FileAttachment::create([
                        'dokumen_id' => $dokumen->id,
                        'nama_file' => Str::afterLast($path, '/'),
                        'path' => $path,
                        'disk' => 'public',
                        'mime_type' => 'application/octet-stream',
                        'ukuran' => 0
                    ]);
                }
            }
        }

        $this->info('Migration completed successfully.');
    }
}
