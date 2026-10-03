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
        if (Schema::hasColumn('habits', 'daily_target') && ! Schema::hasColumn('habits', 'target_value')) {
            Schema::table('habits', function (Blueprint $table) {
                $table->renameColumn('daily_target', 'target_value');
            });
        }

        if (Schema::hasColumn('habit_logs', 'progress') && ! Schema::hasColumn('habit_logs', 'value_logged')) {
            Schema::table('habit_logs', function (Blueprint $table) {
                $table->renameColumn('progress', 'value_logged');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('habits', 'target_value') && ! Schema::hasColumn('habits', 'daily_target')) {
            Schema::table('habits', function (Blueprint $table) {
                $table->renameColumn('target_value', 'daily_target');
            });
        }

        if (Schema::hasColumn('habit_logs', 'value_logged') && ! Schema::hasColumn('habit_logs', 'progress')) {
            Schema::table('habit_logs', function (Blueprint $table) {
                $table->renameColumn('value_logged', 'progress');
            });
        }
    }
};
