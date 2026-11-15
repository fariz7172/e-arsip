<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_masuks', 'jumlah_lembar')) {
                $table->integer('jumlah_lembar')->nullable()->default(1)->after('no_surat');
            }
        });

        Schema::table('surat_keluars', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_keluars', 'jumlah_lembar')) {
                $table->integer('jumlah_lembar')->nullable()->default(1)->after('no_surat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            if (Schema::hasColumn('surat_masuks', 'jumlah_lembar')) {
                $table->dropColumn('jumlah_lembar');
            }
        });

        Schema::table('surat_keluars', function (Blueprint $table) {
            if (Schema::hasColumn('surat_keluars', 'jumlah_lembar')) {
                $table->dropColumn('jumlah_lembar');
            }
        });
    }
};
