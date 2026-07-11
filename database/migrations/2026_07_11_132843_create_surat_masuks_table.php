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
        Schema::create('surat_masuks', function (Blueprint $table) {
            $table->id();
            $table->integer('no_urut')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('no_surat')->nullable();
            $table->string('perihal')->nullable();
            $table->string('asal_surat')->nullable();
            $table->date('tanggal_acara')->nullable();
            $table->string('waktu_acara')->nullable();
            $table->string('tempat_acara')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->date('tgl_keluar')->nullable();
            $table->date('tgl_dikembalikan')->nullable();
            $table->string('distribusi')->nullable();
            $table->string('disposisi')->nullable();
            $table->string('sifat_surat')->nullable();
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
        Schema::dropIfExists('surat_masuks');
    }
};
