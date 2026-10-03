<?php

use App\Livewire\Dashboard;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\OnboardingOption;
use App\Models\OnboardingQuestion;
use App\Models\Task;
use App\Models\Tree;
use App\Models\User;
use App\Services\PatternDetectionService;
use App\Services\WellbeingScoringService;
use Livewire\Livewire;

it('renders dashboard successfully for authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'Julian Casablancas',
        'current_streak' => 30,
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Julian')
        ->assertSee('Days of Streak');
});

it('can log a mood check-in via dashboard component', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('checkIn', 'calm')
        ->assertSet('todayMood', 'calm');

    $checkIn = $user->checkIns()->where('check_in_date', today())->first();

    expect($checkIn)->not->toBeNull()
        ->and($checkIn->mood)->toBe('calm')
        ->and($checkIn->wellbeing_index)->not->toBeNull();
});

it('saves a quick note and redirects it to AI Aurea', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('note', 'I had a difficult day.')
        ->call('submitQuickNote')
        ->assertRedirect(route('ai-aurea'));

    expect(session('ai-aurea-prompt'))->toBe('I had a difficult day.')
        ->and($user->checkIns()->whereDate('check_in_date', today())->value('note'))->toBe('I had a difficult day.');
});

it('does not save or redirect an empty quick note', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('note', '')
        ->call('submitQuickNote')
        ->assertHasErrors(['note' => 'required']);

    expect($user->checkIns()->count())->toBe(0);
});

it('creates personalized system habits and checklist items from onboarding answers', function () {
    $user = User::factory()->create();
    $answers = [
        'goal' => ['question' => 'What is your goal?', 'answer' => 'Improve my sleep'],
        'sleep' => ['question' => 'How is your sleep?', 'answer' => 'Hard to fall asleep'],
        'coping' => ['question' => 'How do you cope?', 'answer' => 'Isolate myself'],
        'mood' => ['question' => 'How is your mood?', 'answer' => 'Anxious or stressed'],
    ];

    foreach ($answers as $key => $answerData) {
        $question = OnboardingQuestion::create([
            'key' => $key,
            'question' => $answerData['question'],
            'order' => count($answers),
            'is_active' => true,
        ]);
        $option = OnboardingOption::create([
            'question_id' => $question->id,
            'label' => $answerData['answer'],
            'value' => strtolower(str_replace(' ', '_', $answerData['answer'])),
            'order' => 1,
        ]);
        $user->onboardingAnswers()->create([
            'question_id' => $question->id,
            'option_id' => $option->id,
        ]);
    }

    $dashboard = Livewire::actingAs($user)->test(Dashboard::class);
    Livewire::actingAs($user)->test(Dashboard::class);

    $progressHabits = $user->habits()->where('type', 'progress')->get();
    $checklistHabits = $user->habits()->where('type', 'checklist')->get();

    expect($progressHabits)->toHaveCount(2)
        ->and($progressHabits->pluck('name')->all())->toBe([
            'Keep a consistent bedtime',
            'Wind down without screens',
        ])
        ->and($progressHabits->every(fn (Habit $habit): bool => $habit->source === 'system'))->toBeTrue()
        ->and($checklistHabits)->toHaveCount(3)
        ->and($checklistHabits->pluck('name')->all())->toContain(
            'Reach out to someone you trust',
            'Start a calm bedtime routine',
        )
        ->and($checklistHabits->every(fn (Habit $habit): bool => $habit->source === 'system'))->toBeTrue();

    $dashboard->assertSee('Keep a consistent bedtime')
        ->assertSee('Reach out to someone you trust');
});

it('limits free users to three active progress habits and offers premium', function () {
    $user = User::factory()->create();

    $dashboard = Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('2/3 habit')
        ->call('openAddHabitModal')
        ->set('customHabitName', 'Read for fifteen minutes')
        ->set('customHabitTarget', 15)
        ->set('customHabitUnit', 'minutes')
        ->call('addCustomHabit')
        ->assertSet('showAddHabitModal', false)
        ->assertSee('Read for fifteen minutes')
        ->assertSee('3/3 habit');

    $dashboard->call('openAddHabitModal')
        ->assertSet('showHabitUpgradeModal', true)
        ->assertSee('Kamu sudah memakai 3 habit. Buka Premium untuk habit tanpa batas.')
        ->assertSee(route('premium'))
        ->set('customHabitName', 'Habit keempat')
        ->set('customHabitTarget', 1)
        ->set('customHabitUnit', 'kali')
        ->call('addCustomHabit')
        ->assertSet('showHabitUpgradeModal', true);

    $progressHabits = $user->habits()->where('type', 'progress')->get();

    expect($progressHabits)->toHaveCount(3)
        ->and($progressHabits->where('source', 'system'))->toHaveCount(2)
        ->and($progressHabits->where('source', 'user'))->toHaveCount(1);

    $customHabit = $progressHabits->firstWhere('source', 'user');

    $dashboard->call('removeCustomHabit', $customHabit->id)
        ->call('openAddHabitModal')
        ->set('customHabitName', 'Take a short walk')
        ->set('customHabitTarget', 10)
        ->set('customHabitUnit', 'minutes')
        ->call('addCustomHabit')
        ->assertSet('showAddHabitModal', false);

    expect($user->habits()->where('type', 'progress')->where('is_active', true)->count())->toBe(3)
        ->and((bool) $user->habits()->where('name', 'Read for fifteen minutes')->value('is_active'))->toBeFalse();
});

it('allows premium users to add more than three active progress habits', function () {
    $user = User::factory()->create();
    $user->activatePremium();

    $dashboard = Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('openAddHabitModal')
        ->set('customHabitName', 'Habit premium pertama')
        ->set('customHabitTarget', 1)
        ->set('customHabitUnit', 'kali')
        ->call('addCustomHabit')
        ->call('openAddHabitModal')
        ->set('customHabitName', 'Habit premium kedua')
        ->set('customHabitTarget', 1)
        ->set('customHabitUnit', 'kali')
        ->call('addCustomHabit')
        ->assertSet('showHabitUpgradeModal', false)
        ->assertSee('Tanpa batas');

    expect($user->habits()->where('type', 'progress')->where('is_active', true)->count())->toBe(4);
});

it('shows a navigable calendar month on the dashboard', function () {
    $user = User::factory()->create();
    $month = now()->format('F Y');
    $nextMonth = now()->addMonth()->format('F Y');

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee($month)
        ->assertSee('Buka lebih banyak fitur')
        ->assertSee('Buka Premium')
        ->assertSee(route('premium.activate'))
        ->call('nextMonth')
        ->assertSee($nextMonth);
});

it('shows premium status instead of the upgrade banner for premium users', function () {
    $user = User::factory()->create();
    $user->activatePremium();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Premium Aktif')
        ->assertDontSee('Buka Premium');
});

it('can toggle checklist habit and update value', function () {
    $user = User::factory()->create();

    $habit = Habit::create([
        'user_id' => $user->id,
        'name' => "Write 3 things I'm grateful for",
        'type' => 'checklist',
        'target_value' => 1,
        'unit' => 'times',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('toggleHabit', $habit->id);

    $log = $habit->logs()->where('log_date', today())->first();

    expect($log)->not->toBeNull()
        ->and($log->value_logged)->toBe(1);
});

it('grows the tree to one hundred percent when every task and active habit is complete', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $habit = Habit::create([
        'user_id' => $user->id,
        'name' => 'Read',
        'type' => 'progress',
        'target_value' => 2,
        'unit' => 'pages',
        'is_active' => true,
    ]);

    HabitLog::create([
        'habit_id' => $habit->id,
        'log_date' => today(),
        'value_logged' => 2,
    ]);

    foreach (range(1, 5) as $number) {
        Task::create([
            'user_id' => $user->id,
            'title' => "Task {$number}",
            'is_done' => true,
            'completed_at' => now(),
        ]);
    }

    $tree = Tree::create([
        'user_id' => $user->id,
        'growth_percentage' => 86,
    ]);

    $this->actingAs($user)
        ->get(route('habit-growth-tree'))
        ->assertSee('100%');

    expect($tree->fresh()->growth_percentage)->toBe(100);
});

it('includes incomplete tasks when calculating tree growth', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);
    $habit = Habit::create([
        'user_id' => $user->id,
        'name' => 'Read',
        'type' => 'progress',
        'target_value' => 1,
        'unit' => 'time',
        'is_active' => true,
    ]);

    HabitLog::create([
        'habit_id' => $habit->id,
        'log_date' => today(),
        'value_logged' => 1,
    ]);

    Task::create([
        'user_id' => $user->id,
        'title' => 'Finished task',
        'is_done' => true,
        'completed_at' => now(),
    ]);
    Task::create([
        'user_id' => $user->id,
        'title' => 'Unfinished task',
        'is_done' => false,
    ]);

    $this->actingAs($user)
        ->get(route('habit-growth-tree'))
        ->assertSee('67%');
});

it('can submit detailed health metrics to compute wellbeing index', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('todayMood', 'energetic')
        ->set('sleepDuration', 8.5)
        ->set('physicalActivityDuration', 60)
        ->set('screenTimeDuration', 2.0)
        ->set('who5Score', 85)
        ->call('saveDetailedCheckIn', app(WellbeingScoringService::class), app(PatternDetectionService::class));

    $checkIn = $user->checkIns()->where('check_in_date', today())->first();

    expect($checkIn)->not->toBeNull()
        ->and($checkIn->wellbeing_index)->toBeGreaterThanOrEqual(80)
        ->and($checkIn->ai_insight)->not->toBeEmpty();
});
