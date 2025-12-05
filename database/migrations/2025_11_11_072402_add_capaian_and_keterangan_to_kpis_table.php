<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            if (!Schema::hasColumn('kpis', 'capaian')) {
                $table->decimal('capaian', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('kpis', 'keterangan')) {
                $table->string('keterangan')->nullable();
            }
        });
    }

   public function down(): void
{
    Schema::table('kpis', function (Blueprint $table) {
        // cek sebelum drop agar migrate:refresh tidak gagal
        if (Schema::hasColumn('kpis', 'capaian')) {
            $table->dropColumn('capaian');
        }
        if (Schema::hasColumn('kpis', 'keterangan')) {
            $table->dropColumn('keterangan');
        }
        // jika ada kolom lain yang di-drop, cek juga
        if (Schema::hasColumn('kpis', 'triwulan')) {
            $table->dropColumn('triwulan');
        }
        if (Schema::hasColumn('kpis', 'bukti_dukung')) {
            $table->dropColumn('bukti_dukung');
        }
    });

    try {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `kpis` MODIFY COLUMN `status` ENUM('Hijau','Kuning','Merah') NOT NULL DEFAULT 'Hijau'");
    } catch (\Throwable $e) {
        // ignore
    }
}

};
