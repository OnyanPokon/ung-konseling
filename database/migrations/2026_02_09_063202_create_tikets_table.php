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
        Schema::create('tikets', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket', 12)->unique();
            $table->foreignId('konseli_id')->constrained('konselis')->onDelete('cascade');
            $table->foreignId('konselor_id')->constrained('konselors')->onDelete('cascade');
            $table->foreignId('hari_layanan_id')->constrained('hari_layanans')->onDelete('cascade');
            $table->text('deskripsi_keluhan');
            $table->enum('jenis_layanan', ['bimbingan', 'konseling']);
            $table->enum('jenis_keluhan', ['sosial', 'pribadi', 'akademik', 'karir']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tikets');
    }
};
