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
        if (!Schema::hasTable('tindak_lanjut')) {
            Schema::create('tindak_lanjut', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bidang_id')
                    ->constrained('bidangs')
                    ->onDelete('cascade');

                $table->text('pesan')->nullable();
                $table->enum('status', ['baru', 'selesai'])->default('baru');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};
