<?php

use App\Models\Task;
use App\Models\User;

it('shows the matching tree stage as the user completes tasks', function (int $completedTasks, string $image) {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    for ($taskNumber = 1; $taskNumber <= $completedTasks; $taskNumber++) {
        Task::create([
            'user_id' => $user->id,
            'title' => "Completed task {$taskNumber}",
            'is_done' => true,
            'completed_at' => now(),
        ]);
    }

    $response = $this->actingAs($user)->get(route('habit-growth-tree'));

    $response
        ->assertSee(asset("images/habit-growth-tree/{$image}"), false)
        ->assertSee((string) $completedTasks)
        ->assertSee('target selesai');

    if ($completedTasks >= 3) {
        $response->assertSee('tree-leaf', false);
    }
})->with([
    'a seed before any tasks are complete' => [0, 'pohon0.png'],
    'a sprout after one completed task' => [1, 'pohon1.png'],
    'a young tree after three completed tasks' => [3, 'pohon2.png'],
    'a branching tree after six completed tasks' => [6, 'pohon3.png'],
    'a full tree after ten completed tasks' => [10, 'pohon4.png'],
]);

it('does not grow the tree for unfinished tasks', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    foreach (range(1, 3) as $taskNumber) {
        Task::create([
            'user_id' => $user->id,
            'title' => "Unfinished task {$taskNumber}",
            'is_done' => false,
        ]);
    }

    $response = $this->actingAs($user)->get(route('habit-growth-tree'));

    $response
        ->assertSee(asset('images/habit-growth-tree/pohon0.png'), false)
        ->assertSee('0')
        ->assertSee('target selesai')
        ->assertDontSee('tree-leaf', false);
});

it('grows the tree when active habits reach their daily target', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    foreach (range(1, 4) as $habitNumber) {
        $habit = $user->habits()->create([
            'name' => "Habit {$habitNumber}",
            'type' => 'progress',
            'target_value' => 1,
            'unit' => 'time',
            'is_active' => true,
        ]);

        $habit->logs()->create([
            'log_date' => today(),
            'value_logged' => 1,
        ]);
    }

    $this->actingAs($user)
        ->get(route('habit-growth-tree'))
        ->assertSee(asset('images/habit-growth-tree/pohon2.png'), false)
        ->assertSee('4')
        ->assertSee('target selesai');
});

it('does not count inactive or unfinished habits toward the tree stage', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $inactiveHabit = $user->habits()->create([
        'name' => 'Inactive habit',
        'type' => 'progress',
        'target_value' => 1,
        'unit' => 'time',
        'is_active' => false,
    ]);
    $unfinishedHabit = $user->habits()->create([
        'name' => 'Unfinished habit',
        'type' => 'progress',
        'target_value' => 2,
        'unit' => 'times',
        'is_active' => true,
    ]);

    $inactiveHabit->logs()->create([
        'log_date' => today(),
        'value_logged' => 1,
    ]);
    $unfinishedHabit->logs()->create([
        'log_date' => today(),
        'value_logged' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('habit-growth-tree'))
        ->assertSee(asset('images/habit-growth-tree/pohon0.png'), false)
        ->assertSee('0')
        ->assertSee('target selesai');
});

it('uses the same task-based tree stage in the dashboard preview', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    foreach (range(1, 6) as $taskNumber) {
        Task::create([
            'user_id' => $user->id,
            'title' => "Completed task {$taskNumber}",
            'is_done' => true,
            'completed_at' => now(),
        ]);
    }

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee(asset('images/habit-growth-tree/pohon3.png'), false);
});
