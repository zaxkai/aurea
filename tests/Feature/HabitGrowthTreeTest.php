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
        ->assertSee('targets completed');

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
        ->assertSee('targets completed')
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
        ->assertSee('targets completed');
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
        ->assertSee('targets completed');
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

it('prevents free students from applying premium tree skins', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $user->tree()->create(['growth_percentage' => 30, 'skin' => 'default']);

    expect($user->isPremium())->toBeFalse();

    $response = $this->actingAs($user)
        ->from(route('habit-growth-tree'))
        ->post(route('habit-growth-tree.skin'), ['skin' => 'sakura']);

    $response
        ->assertRedirect(route('habit-growth-tree'))
        ->assertSessionHas('error');

    expect($user->fresh()->tree->skin)->toBe('default');
});

it('allows premium students to customize tree skins and renders the custom skin', function (string $skinKey, string $skinName) {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $user->activatePremium();

    // 1. Post to change skin
    $response = $this->actingAs($user)
        ->from(route('habit-growth-tree'))
        ->post(route('habit-growth-tree.skin'), ['skin' => $skinKey]);

    $response
        ->assertRedirect(route('habit-growth-tree'))
        ->assertSessionHas('success');

    $tree = $user->fresh()->tree;
    expect($tree->skin)->toBe($skinKey);

    // 2. View habit growth tree page and assert custom skin image is rendered
    $this->actingAs($user)
        ->get(route('habit-growth-tree'))
        ->assertOk()
        ->assertSee(asset("images/habit-growth-tree/skins/{$skinKey}/pohon0.png"), false)
        ->assertSee($skinName);

    // 3. Complete tasks so tree grows to stage 3, and verify skin-specific falling particle class
    foreach (range(1, 6) as $taskNumber) {
        Task::create([
            'user_id' => $user->id,
            'title' => "Completed task {$taskNumber}",
            'is_done' => true,
            'completed_at' => now(),
        ]);
    }

    $this->actingAs($user)
        ->get(route('habit-growth-tree'))
        ->assertOk()
        ->assertSee("tree-leaf--{$skinKey}", false);

    // 4. View dashboard preview card and assert custom skin image and falling particle class are rendered
    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(asset("images/habit-growth-tree/skins/{$skinKey}/pohon3.png"), false)
        ->assertSee("tree-leaf--{$skinKey}", false)
        ->assertSee($skinName);
})->with([
    'sakura' => ['sakura', 'Bunga Sakura'],
    'cemara' => ['cemara', 'Pohon Cemara'],
    'gugur' => ['gugur', 'Musim Gugur'],
    'natal' => ['natal', 'Pohon Natal'],
    'buah' => ['buah', 'Pohon Apel Buah'],
]);

it('allows premium students to switch back to default skin', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $user->activatePremium();

    // Set to sakura first
    $user->tree()->create(['growth_percentage' => 30, 'skin' => 'sakura']);

    // Switch back to default
    $this->actingAs($user)
        ->from(route('habit-growth-tree'))
        ->post(route('habit-growth-tree.skin'), ['skin' => 'default'])
        ->assertRedirect(route('habit-growth-tree'))
        ->assertSessionHas('success');

    expect($user->fresh()->tree->skin)->toBe('default');
});
