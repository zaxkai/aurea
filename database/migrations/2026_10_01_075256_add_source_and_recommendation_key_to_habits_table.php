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
        Schema::table('habits', function (Blueprint $table): void {
            $table->string('source', 16)->default('user');
            $table->string('recommendation_key')->nullable();
            $table->unique(['user_id', 'recommendation_key'], 'habits_user_recommendation_key_unique');
            $table->index(['user_id', 'type', 'source'], 'habits_user_type_source_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('habits', function (Blueprint $table): void {
            $table->dropUnique('habits_user_recommendation_key_unique');
            $table->dropIndex('habits_user_type_source_index');
            $table->dropColumn(['source', 'recommendation_key']);
        });
    }
};
