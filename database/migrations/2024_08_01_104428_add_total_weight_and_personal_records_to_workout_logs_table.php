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
        Schema::table('workout_logs', function (Blueprint $table) {
            $table->string('total_weight')->nullable(); // Add this column
            $table->string('personal_records')->nullable(); // Add this column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_logs', function (Blueprint $table) {
            $table->dropColumn(['total_weight', 'personal_records']);
        });
    }
};

