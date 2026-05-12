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
        Schema::create('sesi_konselings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('konselor_id')->constrained('konselors')->onDelete('cascade');
            $table->foreignId('hari_layanan_id')->constrained('hari_layanans')->onDelete('cascade');
            $table->date('tanggal_konseling');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('tempat');
            $table->enum('status', [
                'dijadwalkan',
                'selesai',
                'dijadwalkan_ulang',
                'dibatalkan'
            ])->default('dijadwalkan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_konselings');
    }
};
