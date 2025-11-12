<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('kpis')->onDelete('cascade');
            $table->enum('triwulan', ['TW1', 'TW2', 'TW3']);
            $table->decimal('realisasi', 10, 2)->nullable();
            $table->decimal('persentase_capaian', 5, 2)->nullable();
            $table->text('rekomendasi')->nullable();
            $table->enum('status', ['Hijau', 'Kuning', 'Merah'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasis');
    }
};
