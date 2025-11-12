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
            $table->dropColumn(['capaian', 'keterangan']);
        });
    }
};
