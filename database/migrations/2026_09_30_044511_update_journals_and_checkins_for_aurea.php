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
        Schema::table('journals', function (Blueprint $table) {
            $table->text('summary')->nullable();
            $table->text('advice')->nullable();
            $table->date('journal_date')->nullable();
            $table->index(['user_id', 'journal_date']);
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn(['summary', 'advice', 'journal_date']);
        });
    }
};
