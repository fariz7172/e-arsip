<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Net\SFTP;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function downloadServerBackup()
    {
        // Pastikan hanya superadmin atau admin
        if (!in_array(auth()->user()->role, ['superadmin', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $host = env('DEPLOY_SSH_HOST');
        $port = env('DEPLOY_SSH_PORT', 65002);
        $username = env('DEPLOY_SSH_USER');
        $password = env('DEPLOY_SSH_PASS');
        $dir = env('DEPLOY_SSH_DIR');

        $dbUser = 'u674511048_arsip';
        $dbPass = '!FarizAhmad123456';
        $dbName = 'u674511048_arsip';

        if (!$host || !$username || !$password || !$dir) {
            return back()->with('error', 'Kredensial SSH di file .env tidak lengkap.');
        }

        try {
            $sftp = new SFTP($host, $port);
            if (!$sftp->login($username, $password)) {
                return back()->with('error', 'Gagal login SSH ke server.');
            }

            $date = date('Y-m-d_H-i-s');
            $filename = "backup_server_{$date}.sql";
            
            // Execute mysqldump remotely
            $dumpCmd = "cd $dir && mysqldump -u $dbUser -p'$dbPass' $dbName > $filename 2>&1";
            $output = $sftp->exec($dumpCmd);

            if (stripos($output, 'error') !== false || stripos($output, 'denied') !== false || stripos($output, 'command not found') !== false) {
                $sftp->delete($dir . '/' . $filename);
                Log::error('Backup DB Error: ' . $output);
                return back()->with('error', 'Terjadi kesalahan saat mem-backup di server: ' . substr($output, 0, 100));
            }

            $localPath = storage_path('app/public/' . $filename);
            if ($sftp->get($dir . '/' . $filename, $localPath)) {
                // Hapus file di server Hostinger
                $sftp->delete($dir . '/' . $filename);

                // Kirim file ke browser, setelah selesai download, hapus file lokal
                return response()->download($localPath)->deleteFileAfterSend(true);
            }

            return back()->with('error', 'Gagal mengunduh file backup dari server.');

        } catch (\Exception $e) {
            Log::error('Backup Exception: ' . $e->getMessage());
            return back()->with('error', 'Koneksi ke server gagal: ' . $e->getMessage());
        }
    }
}
