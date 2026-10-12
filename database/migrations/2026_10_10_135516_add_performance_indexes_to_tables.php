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
            $table->index(['user_id', 'created_at'], 'journals_user_id_created_at_idx');
        });

        Schema::table('habits', function (Blueprint $table) {
            $table->index(['user_id', 'is_active'], 'habits_user_id_is_active_idx');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['user_id', 'is_done'], 'tasks_user_id_is_done_idx');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['chat_session_id', 'created_at'], 'chat_messages_session_created_idx');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'updated_at'], 'chat_sessions_user_updated_idx');
        });

        if (Schema::hasTable('classroom_student')) {
            Schema::table('classroom_student', function (Blueprint $table) {
                $table->index('student_id', 'classroom_student_student_id_idx');
            });
        }

        Schema::table('check_ins', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'check_ins_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropIndex('journals_user_id_created_at_idx');
        });

        Schema::table('habits', function (Blueprint $table) {
            $table->dropIndex('habits_user_id_is_active_idx');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_user_id_is_done_idx');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('chat_messages_session_created_idx');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropIndex('chat_sessions_user_updated_idx');
        });

        if (Schema::hasTable('classroom_student')) {
            Schema::table('classroom_student', function (Blueprint $table) {
                $table->dropIndex('classroom_student_student_id_idx');
            });
        }

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropIndex('check_ins_user_created_idx');
        });
    }
};
