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
        Schema::create('laporan_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_konseling_id')->constrained()->cascadeOnDelete();
            $table->foreignId('konselor_id')->constrained()->cascadeOnDelete();

            $table->string('nama_kegiatan');
            $table->enum('jenis_layanan', ['dasar', 'responsif', 'perencanaan_individual', 'dukungan_sistem']);
            $table->text('tujuan_kegiatan')->nullable();

            $table->string('waktu_tempat');
            $table->string('jumlah_peserta');

            $table->text('uraian_kegiatan')->nullable();
            $table->text('hasil_dampak')->nullable();
            $table->text('rekomendasi')->nullable();

            // penting
            $table->longText('html_content')->nullable();
            $table->string('file_path')->nullable();

            $table->enum('status', ['draft', 'final'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_konseling');
    }
};
