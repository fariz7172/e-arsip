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
            $table->foreignId('dokumen_id')->nullable()->constrained('dokumens')->nullOnDelete();
        });

        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->foreignId('dokumen_id')->nullable()->constrained('dokumens')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropForeign(['dokumen_id']);
            $table->dropColumn('dokumen_id');
        });

        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->dropForeign(['dokumen_id']);
            $table->dropColumn('dokumen_id');
        });
    }
};
