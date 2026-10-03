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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id')->nullable();
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('avatar')->nullable()->after('password');
            $table->date('last_active_date')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'google_id', 'avatar', 'last_active_date', 'onboarding_completed_at']);
        });
    }
};
