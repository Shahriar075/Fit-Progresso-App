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
        Schema::create('personal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->string('exercise_type'); // 'strength', 'bodyweight', 'flexibility', 'cardio'
            $table->integer('max_value')->nullable(); // Max weight, distance, or other relevant metric
            $table->integer('max_reps')->nullable(); // Max repetitions
            $table->integer('max_time_spent')->nullable(); // Max duration in seconds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_records');
    }
};
