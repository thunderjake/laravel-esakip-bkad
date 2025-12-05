<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_measurements', function (Blueprint $table) {
            $table->id();

            // Relasi ke KPI
            $table->foreignId('kpi_id')
                ->constrained('kpis')
                ->onDelete('cascade');

            // Tahun pengukuran (sesuai key 'tahun' pada controller/view)
            $table->year('tahun')->index();

            // Triwulan 1 - 4
            $table->tinyInteger('triwulan')
                ->comment('1 = TW1, 2 = TW2, 3 = TW3, 4 = TW4');

            // Nilai pengukuran
            $table->decimal('target', 12, 2)->nullable();
            $table->decimal('realisasi', 12, 2)->nullable();

            // File bukti (path disimpan relatif ke disk public, contoh: "public/kpi/1/measurements/file.pdf")
            $table->string('bukti_file')->nullable();

            // Catatan opsional
            $table->text('catatan')->nullable();

            // User penginput (user_id lebih umum)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Draft atau Final
            $table->enum('status', ['draft', 'final'])
                ->default('draft');

            $table->timestamps();

            // Mencegah duplikasi input (kpi + tahun + triwulan)
            $table->unique(['kpi_id', 'tahun', 'triwulan'], 'kpi_year_triwulan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_measurements');
    }
};
