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
        Schema::create('question_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->unsignedInteger('scale');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_screenings');
    }
};
