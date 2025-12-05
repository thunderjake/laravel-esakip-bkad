<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            if (! Schema::hasColumn('kpis', 'bukti_dukung')) {
                $table->string('bukti_dukung')->nullable()->after('realisasi');
            }
            if (! Schema::hasColumn('kpis', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('ket');
            }
            if (! Schema::hasColumn('kpis', 'capaian')) {
                $table->decimal('capaian', 8, 2)->default(0)->after('keterangan');
            }
            if (! Schema::hasColumn('kpis', 'triwulan')) {
                $table->string('triwulan')->nullable()->after('capaian');
            }
        });

        // Ubah status menjadi nullable (raw SQL untuk menghindari doctrine/dbal requirement)
        try {
            DB::statement("ALTER TABLE `kpis` MODIFY COLUMN `status` ENUM('Hijau','Kuning','Merah') NULL DEFAULT NULL");
        } catch (\Throwable $e) {
            // jika gagal karena permission atau engine, kamu bisa install doctrine/dbal lalu gunakan change()
        }
    }

    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            if (Schema::hasColumn('kpis', 'triwulan')) {
                $table->dropColumn('triwulan');
            }
            if (Schema::hasColumn('kpis', 'capaian')) {
                $table->dropColumn('capaian');
            }
            if (Schema::hasColumn('kpis', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
            if (Schema::hasColumn('kpis', 'bukti_dukung')) {
                $table->dropColumn('bukti_dukung');
            }
        });

        try {
            DB::statement("ALTER TABLE `kpis` MODIFY COLUMN `status` ENUM('Hijau','Kuning','Merah') NOT NULL DEFAULT 'Hijau'");
        } catch (\Throwable $e) {
            // ignore
        }
    }
};

