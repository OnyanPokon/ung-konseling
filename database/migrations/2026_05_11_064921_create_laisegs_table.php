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
        Schema::create('laisegs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sesi_konseling_id')->constrained('sesi_konselings')->cascadeOnDelete();
            $table->text('topik_pembahasan')->nullable();
            $table->text('pemahaman_baru')->nullable();
            $table->text('perasaan_setelah_layanan')->nullable();
            $table->text('rencana_setelah_layanan')->nullable();
            $table->boolean('apakah_terkait_masalah')->nullable();
            $table->text('keuntungan_jika_ya')->nullable();
            $table->text('keuntungan_jika_tidak')->nullable();
            $table->text('saran_pesan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laisegs');
    }
};
