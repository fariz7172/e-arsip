<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use phpseclib3\Net\SSH2;

class DeployApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy {--branch=farizahmad.github.io : The branch to pull}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mem-push/deploy aplikasi secara otomatis ke server Hostinger menggunakan SSH';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = env('DEPLOY_SSH_HOST');
        $port = env('DEPLOY_SSH_PORT', 65002);
        $username = env('DEPLOY_SSH_USER');
        $password = env('DEPLOY_SSH_PASS');
        $dir = env('DEPLOY_SSH_DIR');
        $branch = $this->option('branch');

        if (!$host || !$username || !$password || !$dir) {
            $this->error('Pengaturan SSH (DEPLOY_SSH_HOST, DEPLOY_SSH_USER, DEPLOY_SSH_PASS, DEPLOY_SSH_DIR) tidak lengkap di file .env');
            return Command::FAILURE;
        }

        $this->info("Menghubungkan ke server $host:$port...");

        try {
            $ssh = new SSH2($host, $port);
            if (!$ssh->login($username, $password)) {
                $this->error('Gagal masuk ke SSH: Username atau Password salah.');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Koneksi SSH gagal: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Login Berhasil! Mengeksekusi perintah deploy...');

        $commands = [
            "cd $dir && pwd",
            "cd $dir && git pull origin $branch",
            "cd $dir && php artisan migrate --force",
        ];

        foreach ($commands as $cmd) {
            $this->line("\n> <fg=yellow>$cmd</>");
            $output = $ssh->exec($cmd);
            $this->line(trim($output));
        }

        $this->info("\n✅ Deployment Selesai!");
        
        return Command::SUCCESS;
    }
}
