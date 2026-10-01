<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The validation rule for this field has always been `sometimes|nullable`,
     * so a counselor could be registered with no phone number as far as the
     * request was concerned. The column disagreed, and the disagreement only
     * surfaced as an error at the point of writing.
     */
    public function up(): void
    {
        Schema::table('konselors', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konselors', function (Blueprint $table) {
            // Fill any newly allowed nulls before reverting the constraint
            \Illuminate\Support\Facades\DB::table('konselors')->whereNull('phone')->update(['phone' => '']);
            $table->string('phone')->nullable(false)->change();
        });
    }
};
