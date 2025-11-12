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
        Schema::create('kpis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bidang_id')->constrained('bidangs')->onDelete('cascade');
        $table->string('nama_kpi');
        $table->string('satuan')->nullable();
        $table->decimal('target', 8, 2)->nullable();
        $table->decimal('realisasi', 8, 2)->nullable();
        $table->decimal('bobot', 5, 2)->nullable();
        $table->enum('status', ['Hijau', 'Kuning', 'Merah'])->default('Hijau');
        $table->string('ket');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
