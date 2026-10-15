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
                'q1_lainnya',
                'q2_lainnya',
                'q3_lainnya',
                'q4_lainnya',
                'q5_lainnya',
                'q6_lainnya',
                'q7_lainnya',
            ]);

            $table->text('catatan_lainnya')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('laisegs', function (Blueprint $table) {
            $table->dropColumn('catatan_lainnya');

            $table->text('q1_lainnya')->nullable();
            $table->text('q2_lainnya')->nullable();
            $table->text('q3_lainnya')->nullable();
            $table->text('q4_lainnya')->nullable();
            $table->text('q5_lainnya')->nullable();
            $table->text('q6_lainnya')->nullable();
            $table->text('q7_lainnya')->nullable();
        });
    }
};
