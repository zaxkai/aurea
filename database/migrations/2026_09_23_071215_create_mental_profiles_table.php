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
        Schema::create('mental_profiles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
        $table->text('symptoms')->nullable();       // akan di-cast encrypted di model
        $table->text('anxiety_triggers')->nullable();
        $table->text('bad_experiences')->nullable();
        $table->json('focus_areas')->nullable();     // contoh: ["School", "Friendships"]
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mental_profiles');
    }
};
