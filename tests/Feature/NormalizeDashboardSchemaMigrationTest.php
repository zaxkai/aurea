<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('preserves legacy habit values and refuses a destructive rollback', function () {
    $user = User::factory()->create();

    Schema::table('habits', function (Blueprint $table): void {
        $table->renameColumn('target_value', 'daily_target');
    });
    Schema::table('habit_logs', function (Blueprint $table): void {
        $table->renameColumn('value_logged', 'progress');
    });

    $habitId = DB::table('habits')->insertGetId([
        'user_id' => $user->id,
        'name' => 'Drink water',
        'unit' => 'ml',
        'daily_target' => 2000,
        'type' => 'progress',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('habit_logs')->insert([
        'habit_id' => $habitId,
        'log_date' => today(),
        'progress' => 750,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = require database_path('migrations/2026_09_30_120000_normalize_dashboard_schema.php');
    $migration->up();

    expect(DB::table('habits')->where('id', $habitId)->value('target_value'))->toBe(2000)
        ->and(DB::table('habit_logs')->where('habit_id', $habitId)->value('value_logged'))->toBe(750);

    expect(fn () => $migration->down())->toThrow(LogicException::class);

    expect(DB::table('habits')->where('id', $habitId)->value('target_value'))->toBe(2000)
        ->and(DB::table('habit_logs')->where('habit_id', $habitId)->value('value_logged'))->toBe(750);
});
