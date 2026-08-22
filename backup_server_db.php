<?php

/**
 * Script Backup Database Server ke Komputer Lokal
 * 
 * Cara menggunakan:
 * 1. Buka terminal (CMD / PowerShell / Git Bash) di folder project ini
 * 2. Jalankan perintah: php backup_server_db.php
 */

require __DIR__ . '/vendor/autoload.php';

use phpseclib3\Net\SFTP;
use Dotenv\Dotenv;

// Load file .env untuk mengambil konfigurasi SSH
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Ambil data SSH dari .env atau gunakan default
$host = $_ENV['DEPLOY_SSH_HOST'] ?? '145.79.14.233';
$port = $_ENV['DEPLOY_SSH_PORT'] ?? 65002;
$username = $_ENV['DEPLOY_SSH_USER'] ?? 'u674511048';
$password = $_ENV['DEPLOY_SSH_PASS'] ?? '!FarizAhmad123456';
$dir = $_ENV['DEPLOY_SSH_DIR'] ?? '/home/u674511048/domains/farizahmad.com/public_html/e-arsip';

// Data Database sesuai permintaan
$dbUser = 'u674511048_arsip';
$dbPass = '!FarizAhmad123456';
$dbName = 'u674511048_arsip';

echo "=================================================\n";
echo "   Memulai Proses Backup Database dari Server\n";
echo "=================================================\n\n";

echo "⏳ Menghubungkan ke $host:$port...\n";

try {
    $sftp = new SFTP($host, $port);
    if (!$sftp->login($username, $password)) {
        die("❌ Gagal login SSH. Periksa username dan password.\n");
    }
} catch (Exception $e) {
    die("❌ Gagal terhubung ke server: " . $e->getMessage() . "\n");
}

echo "✅ Login SSH berhasil!\n";
echo "⏳ Mengeksekusi mysqldump di server...\n";

// Nama file backup sementara di server (menggunakan tanggal hari ini)
$date = date('Y-m-d_H-i-s');
$filename = "backup_server_{$date}.sql";

// Perintah untuk mem-backup database langsung di server (menyembunyikan output agar tidak penuh)
$dumpCmd = "cd $dir && mysqldump -u $dbUser -p'$dbPass' $dbName > $filename 2>&1";

$output = $sftp->exec($dumpCmd);

// Cek apakah output mengandung error
if (stripos($output, 'error') !== false || stripos($output, 'denied') !== false || stripos($output, 'command not found') !== false) {
    echo "❌ Terjadi kesalahan saat melakukan dump di server: \n" . trim($output) . "\n";
    $sftp->delete($dir . '/' . $filename);
    exit;
}

echo "✅ Database berhasil di-backup di server (file: $filename).\n";
echo "⏳ Mengunduh file backup ke komputer lokal Anda...\n";

// Tentukan lokasi penyimpanan lokal (di root folder)
$localPath = __DIR__ . '/' . $filename;

// Download file dari server menggunakan SFTP
if ($sftp->get($dir . '/' . $filename, $localPath)) {
    echo "✅ Berhasil diunduh!\n";
    echo "📁 File tersimpan di: $localPath\n\n";
    
    echo "⏳ Menghapus file backup sementara di server untuk menghemat ruang...\n";
    $sftp->delete($dir . '/' . $filename);
    
    echo "🎉 PROSES SELESAI!\n";
} else {
    echo "❌ Gagal mengunduh file dari server.\n";
}
