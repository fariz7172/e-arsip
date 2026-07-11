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
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->integer('no_urut')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('no_surat')->nullable();
            $table->string('perihal')->nullable();
            $table->string('tembusan')->nullable();
            $table->date('tgl_masuk_kasubag')->nullable();
            $table->date('tgl_masuk_kasudin')->nullable();
            $table->date('tgl_keluar')->nullable();
            $table->date('tgl_dikembalikan_tu')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('scan_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluars');
    }
};
