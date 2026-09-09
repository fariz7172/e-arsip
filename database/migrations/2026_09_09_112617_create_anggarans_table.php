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
        Schema::create('anggarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('tipe', 50)->comment('program, kegiatan, sub_kegiatan, aktivitas, rekening');
            $table->string('kode', 100);
            $table->string('nama', 255);
            $table->decimal('pagu', 15, 2)->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('anggarans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggarans');
    }
};
