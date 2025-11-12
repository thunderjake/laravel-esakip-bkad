<?php
// database/migrations/2025_11_07_021459_add_realisasi_capaian_status_to_kpis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            // Cek satu per satu biar aman dari duplikat
            if (!Schema::hasColumn('kpis', 'capaian')) {
                $table->decimal('capaian', 10, 2)->nullable()->after('realisasi');
            }
            if (!Schema::hasColumn('kpis', 'status_warna')) {
                $table->string('status_warna')->nullable()->after('capaian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            if (Schema::hasColumn('kpis', 'capaian')) {
                $table->dropColumn('capaian');
            }
            if (Schema::hasColumn('kpis', 'status_warna')) {
                $table->dropColumn('status_warna');
            }
        });
    }
};
