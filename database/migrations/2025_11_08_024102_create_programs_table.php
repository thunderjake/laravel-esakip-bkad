<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_program')->unique();
            $table->string('nama_program');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('bidang_id')->nullable(); // relasi ke tabel bidang kalau ada
            $table->timestamps();

            // Jika tabel bidang sudah ada
            $table->foreign('bidang_id')->references('id')->on('bidangs')->onDelete('set null');
        });
    }

    /**
     * Batalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
