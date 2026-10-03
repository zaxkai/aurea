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
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('mood', ['energetic', 'calm', 'neutral', 'stressed', 'exhausted']);
            $table->integer('who5_score')->nullable(); // Mental well-being (0-100)
            $table->decimal('sleep_duration', 4, 2)->nullable(); // in hours
            $table->integer('physical_activity_duration')->nullable(); // in minutes
            $table->decimal('screen_time_duration', 4, 2)->nullable(); // in hours
            $table->integer('wellbeing_index')->nullable(); // Final calculated index
            $table->text('ai_insight')->nullable(); // Insight from AI pattern detection
            $table->text('note')->nullable();
            $table->date('check_in_date');
            $table->timestamps();

            $table->unique(['user_id', 'check_in_date']); // satu check-in per hari
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
