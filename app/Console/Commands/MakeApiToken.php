<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-token {email} {--name=API_Token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Sanctum API Token for a given user email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Pengguna dengan email '{$email}' tidak ditemukan.");
            return;
        }

        $token = $user->createToken($name);

        $this->info("Berhasil membuat Token API untuk {$user->name}.");
        $this->warn("Simpan token ini baik-baik! Ini adalah satu-satunya saat Anda bisa melihatnya.");
        $this->line('');
        $this->info("API Token: " . $token->plainTextToken);
        $this->line('');
    }
}
