<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laisegs', function (Blueprint $table) {
            $table->dropColumn([
                'topik_pembahasan',
                'pemahaman_baru',
                'perasaan_setelah_layanan',
                'rencana_setelah_layanan',
                'apakah_terkait_masalah',
                'keuntungan_jika_ya',
                'keuntungan_jika_tidak',
                'saran_pesan',
            ]);

            $table->string('q1_topik_sesuai')->nullable();
            $table->text('q1_lainnya')->nullable();

            $table->string('q2_pemahaman_baru')->nullable();
            $table->text('q2_lainnya')->nullable();

            $table->string('q3_merasa_lebih_baik')->nullable();
            $table->text('q3_lainnya')->nullable();

            $table->string('q4_rencana_tindakan')->nullable();
            $table->text('q4_lainnya')->nullable();

            $table->string('q5_berkaitan_masalah')->nullable();
            $table->text('q5_lainnya')->nullable();

            $table->string('q6_manfaat_layanan')->nullable();
            $table->text('q6_lainnya')->nullable();

            $table->string('q7_layanan_sudah_baik')->nullable();
            $table->text('q7_lainnya')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('laisegs', function (Blueprint $table) {
            $table->dropColumn([
                'q1_topik_sesuai', 'q1_lainnya',
                'q2_pemahaman_baru', 'q2_lainnya',
                'q3_merasa_lebih_baik', 'q3_lainnya',
                'q4_rencana_tindakan', 'q4_lainnya',
                'q5_berkaitan_masalah', 'q5_lainnya',
                'q6_manfaat_layanan', 'q6_lainnya',
                'q7_layanan_sudah_baik', 'q7_lainnya',
            ]);

            $table->text('topik_pembahasan')->nullable();
            $table->text('pemahaman_baru')->nullable();
            $table->text('perasaan_setelah_layanan')->nullable();
            $table->text('rencana_setelah_layanan')->nullable();
            $table->boolean('apakah_terkait_masalah')->nullable();
            $table->text('keuntungan_jika_ya')->nullable();
            $table->text('keuntungan_jika_tidak')->nullable();
            $table->text('saran_pesan')->nullable();
        });
    }
};
