<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function downloadServerBackup()
    {
        // Pastikan hanya superadmin atau admin
        if (!in_array(auth()->user()->role, ['superadmin', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        try {
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbHost = env('DB_HOST', '127.0.0.1');

            $date = date('Y-m-d_H-i-s');
            $filename = "backup_database_{$date}.sql";
            $localPath = storage_path('app/public/' . $filename);

            // Execute mysqldump locally on the server (bypassing SSH entirely)
            // Adding --no-tablespaces to avoid privileges error on shared hosting
            $dumpCmd = "mysqldump --no-tablespaces -h {$dbHost} -u {$dbUser} -p'{$dbPass}' {$dbName} > " . escapeshellarg($localPath) . " 2>&1";
            
            exec($dumpCmd, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('Backup DB Error: ' . implode("\n", $output));
                
                // Fallback: If mysqldump fails (e.g. not found or no permission), use pure PHP backup
                $this->purePhpBackup($localPath);
            }

            if (file_exists($localPath) && filesize($localPath) > 0) {
                return response()->download($localPath)->deleteFileAfterSend(true);
            }

            return back()->with('error', 'Gagal membuat file backup (File kosong atau tidak ada).');

        } catch (\Exception $e) {
            Log::error('Backup Exception: ' . $e->getMessage());
            return back()->with('error', 'Gagal mem-backup database: ' . $e->getMessage());
        }
    }

    private function purePhpBackup($filePath)
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');
        $property = 'Tables_in_' . $dbName;
        
        $sql = "-- Database Backup\n-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $tableName = $table->$property;
            
            // Get Create Table statement
            $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
            $sql .= "\n\nDROP TABLE IF EXISTS `$tableName`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            
            // Get data
            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $sql .= "INSERT INTO `$tableName` VALUES(";
                $values = [];
                foreach ($row as $val) {
                    if (is_null($val)) {
                        $values[] = "NULL";
                    } else {
                        // Escape quotes and backslashes properly
                        $val = str_replace(['\\', "'"], ['\\\\', "''"], $val);
                        $values[] = "'" . $val . "'";
                    }
                }
                $sql .= implode(", ", $values) . ");\n";
            }
        }
        
        file_put_contents($filePath, $sql);
    }
}
