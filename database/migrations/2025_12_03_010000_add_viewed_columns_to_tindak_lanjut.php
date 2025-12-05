<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            if (! Schema::hasColumn('tindak_lanjut', 'viewed_at')) {
                $table->timestamp('viewed_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('tindak_lanjut', 'viewed_by')) {
                $table->foreignId('viewed_by')->nullable()->constrained('users')->onDelete('set null')->after('viewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            if (Schema::hasColumn('tindak_lanjut', 'viewed_by')) {
                $table->dropForeign(['viewed_by']);
                $table->dropColumn('viewed_by');
            }
            if (Schema::hasColumn('tindak_lanjut', 'viewed_at')) {
                $table->dropColumn('viewed_at');
            }
        });
    }
};
