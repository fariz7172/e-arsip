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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pptk_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->string('no_spd')->nullable();
            $table->date('tgl_spd')->nullable();
            $table->string('program')->nullable();
            $table->string('kegiatan')->nullable();
            $table->string('sub_kegiatan')->nullable();
            $table->string('kode_rek')->nullable();
            $table->string('no_spp')->nullable();
            $table->date('tgl_spp')->nullable();
            $table->string('no_spm')->nullable();
            $table->date('tgl_spm')->nullable();
            $table->string('no_sp2d')->nullable();
            $table->date('tgl_sp2d')->nullable();
            $table->string('no_bast')->nullable();
            $table->date('tgl_bast')->nullable();
            $table->string('no_kwi')->nullable();
            $table->date('tgl_kwi')->nullable();
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->string('terbilang')->nullable();
            $table->decimal('tagihan_1', 15, 2)->default(0);
            $table->decimal('tagihan_2', 15, 2)->default(0);
            $table->decimal('tagihan_3', 15, 2)->default(0);
            $table->decimal('tagihan_4', 15, 2)->default(0);
            $table->decimal('tagihan_5', 15, 2)->default(0);
            $table->text('keperluan')->nullable();
            $table->decimal('denda', 15, 2)->default(0);
            $table->string('progres')->nullable();
            $table->string('nik')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('terbilang_kontrak')->nullable();
            $table->json('print_data')->nullable();
            
            $table->unsignedBigInteger('program_id')->nullable();
            $table->unsignedBigInteger('kegiatan_id')->nullable();
            $table->unsignedBigInteger('sub_kegiatan_id')->nullable();
            
            $table->json('vendor')->nullable();
            $table->json('contract')->nullable();
            $table->json('pptk')->nullable();
            $table->json('program_ref')->nullable();
            $table->json('kegiatan_ref')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
