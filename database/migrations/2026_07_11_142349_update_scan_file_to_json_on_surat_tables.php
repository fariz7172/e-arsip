<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change column to text to accommodate longer JSON arrays
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->text('scan_file')->nullable()->change();
        });
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->text('scan_file')->nullable()->change();
        });

        // Convert existing scalar strings to JSON arrays
        $masuks = DB::table('surat_masuks')->whereNotNull('scan_file')->get();
        foreach ($masuks as $m) {
            if (!str_starts_with($m->scan_file, '[')) {
                DB::table('surat_masuks')->where('id', $m->id)->update(['scan_file' => json_encode([$m->scan_file])]);
            }
        }

        $keluars = DB::table('surat_keluars')->whereNotNull('scan_file')->get();
        foreach ($keluars as $k) {
            if (!str_starts_with($k->scan_file, '[')) {
                DB::table('surat_keluars')->where('id', $k->id)->update(['scan_file' => json_encode([$k->scan_file])]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->string('scan_file', 255)->nullable()->change();
        });
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->string('scan_file', 255)->nullable()->change();
        });
    }
};
