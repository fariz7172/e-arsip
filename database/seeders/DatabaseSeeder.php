<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Dokumen;
use App\Models\FileAttachment;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create operator user:
        $operator = User::create([
            'name' => 'Operator Arsip',
            'email' => 'operator@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        // Create viewer user
        User::create([
            'name' => 'Viewer',
            'email' => 'viewer@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
        ]);

        // ==========================================
        // Bundle 1: Dinas Jakarta
        // ==========================================
        $bundleJakarta = Bundle::create([
            'nama' => 'Arsip Dinas Jakarta',
            'kode' => 'BDL-JKT-2026',
            'deskripsi' => 'Kumpulan arsip dokumen Dinas Jakarta tahun 2026, mencakup SDA, kontrak, dan kwitansi.',
            'tahun' => 2026,
            'created_by' => $admin->id,
        ]);

        // Kategori SDA
        $sda = Kategori::create([
            'bundle_id' => $bundleJakarta->id,
            'nama' => 'SDA',
            'kode' => 'KTG-SDA',
            'deskripsi' => 'Dokumen Sumber Daya Alam',
            'urutan' => 1,
        ]);

        Dokumen::create([
            'kategori_id' => $sda->id,
            'judul' => 'Laporan Survei SDA Q1 2026',
            'nomor_dokumen' => 'DOK/SDA/001/2026',
            'tanggal_dokumen' => '2026-03-15',
            'keterangan' => 'Laporan survei sumber daya alam kuartal pertama tahun 2026.',
            'uploaded_by' => $operator->id,
        ]);

        Dokumen::create([
            'kategori_id' => $sda->id,
            'judul' => 'Hasil Analisis Tanah',
            'nomor_dokumen' => 'DOK/SDA/002/2026',
            'tanggal_dokumen' => '2026-04-20',
            'keterangan' => 'Analisis kandungan tanah di kawasan industri Jakarta Utara.',
            'uploaded_by' => $operator->id,
        ]);

        // Kategori Kontrak
        $kontrak = Kategori::create([
            'bundle_id' => $bundleJakarta->id,
            'nama' => 'Kontrak',
            'kode' => 'KTG-KTR',
            'deskripsi' => 'Dokumen kontrak dan perjanjian',
            'urutan' => 2,
        ]);

        Dokumen::create([
            'kategori_id' => $kontrak->id,
            'judul' => 'Kontrak Vendor IT 2026',
            'nomor_dokumen' => 'KTR/IT/001/2026',
            'tanggal_dokumen' => '2026-01-10',
            'keterangan' => 'Kontrak penyediaan jasa IT dengan PT Teknologi Maju.',
            'uploaded_by' => $admin->id,
        ]);

        // Kategori Kwitansi
        $kwitansi = Kategori::create([
            'bundle_id' => $bundleJakarta->id,
            'nama' => 'Kwitansi',
            'kode' => 'KTG-KWT',
            'deskripsi' => 'Dokumen kwitansi dan bukti pembayaran',
            'urutan' => 3,
        ]);

        Dokumen::create([
            'kategori_id' => $kwitansi->id,
            'judul' => 'Kwitansi Pengadaan ATK',
            'nomor_dokumen' => 'KWT/ATK/001/2026',
            'tanggal_dokumen' => '2026-02-05',
            'keterangan' => 'Kwitansi pembelian alat tulis kantor bulan Februari.',
            'uploaded_by' => $operator->id,
        ]);

        Dokumen::create([
            'kategori_id' => $kwitansi->id,
            'judul' => 'Kwitansi Sewa Gedung',
            'nomor_dokumen' => 'KWT/SGD/001/2026',
            'tanggal_dokumen' => '2026-01-01',
            'keterangan' => 'Kwitansi pembayaran sewa gedung kantor semester 1.',
            'uploaded_by' => $admin->id,
        ]);

        // ==========================================
        // Bundle 2: Dinas Bandung
        // ==========================================
        $bundleBandung = Bundle::create([
            'nama' => 'Arsip Dinas Bandung',
            'kode' => 'BDL-BDG-2026',
            'deskripsi' => 'Arsip dokumen Dinas Kota Bandung.',
            'tahun' => 2026,
            'created_by' => $admin->id,
        ]);

        $infrastruktur = Kategori::create([
            'bundle_id' => $bundleBandung->id,
            'nama' => 'Infrastruktur',
            'kode' => 'KTG-INF',
            'deskripsi' => 'Dokumen infrastruktur dan pembangunan',
            'urutan' => 1,
        ]);

        Dokumen::create([
            'kategori_id' => $infrastruktur->id,
            'judul' => 'Proposal Pembangunan Jalan',
            'nomor_dokumen' => 'PRO/INF/001/2026',
            'tanggal_dokumen' => '2026-05-01',
            'keterangan' => 'Proposal pembangunan jalan penghubung kawasan selatan.',
            'uploaded_by' => $operator->id,
        ]);

        $keuangan = Kategori::create([
            'bundle_id' => $bundleBandung->id,
            'nama' => 'Keuangan',
            'kode' => 'KTG-KEU',
            'deskripsi' => 'Dokumen laporan keuangan',
            'urutan' => 2,
        ]);

        Dokumen::create([
            'kategori_id' => $keuangan->id,
            'judul' => 'Laporan Keuangan Q1',
            'nomor_dokumen' => 'LAP/KEU/001/2026',
            'tanggal_dokumen' => '2026-04-01',
            'keterangan' => 'Laporan keuangan kuartal 1 tahun 2026.',
            'uploaded_by' => $admin->id,
        ]);

        // ==========================================
        // Bundle 3: Arsip Lama
        // ==========================================
        $bundleLama = Bundle::create([
            'nama' => 'Arsip Dinas Jakarta 2025',
            'kode' => 'BDL-JKT-2025',
            'deskripsi' => 'Arsip tahun sebelumnya untuk referensi.',
            'tahun' => 2025,
            'created_by' => $admin->id,
        ]);

        $umum = Kategori::create([
            'bundle_id' => $bundleLama->id,
            'nama' => 'Umum',
            'kode' => 'KTG-UMM',
            'deskripsi' => 'Dokumen umum',
            'urutan' => 1,
        ]);

        Dokumen::create([
            'kategori_id' => $umum->id,
            'judul' => 'Surat Keputusan 2025',
            'nomor_dokumen' => 'SK/UMM/001/2025',
            'tanggal_dokumen' => '2025-06-15',
            'keterangan' => 'Surat keputusan pengangkatan pejabat.',
            'uploaded_by' => $admin->id,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📧 Login credentials:');
        $this->command->info('   Admin    : admin@earsip.test / password');
        $this->command->info('   Operator : operator@earsip.test / password');
        $this->command->info('   Viewer   : viewer@earsip.test / password');
    }
}
