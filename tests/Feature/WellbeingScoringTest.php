<?php

use App\Models\CheckIn;
use App\Models\User;
use App\Services\PatternDetectionService;
use App\Services\WellbeingScoringService;

it('calculates a high wellbeing index for optimal health data', function () {
    $service = new WellbeingScoringService;

    $checkIn = new CheckIn([
        'mood' => 'energetic',
        'who5_score' => 80,          // Good mental well-being
        'sleep_duration' => 8.5,     // Optimal for teens
        'physical_activity_duration' => 60, // Meets WHO recommendation
        'screen_time_duration' => 1.5,     // Below 2 hours
    ]);

    $index = $service->calculateIndex($checkIn);

    expect($index)->toBeGreaterThanOrEqual(80)
        ->and($index)->toBeLessThanOrEqual(100);
});

it('calculates a low wellbeing index for poor health data', function () {
    $service = new WellbeingScoringService;

    $checkIn = new CheckIn([
        'mood' => 'exhausted',
        'who5_score' => 20,          // Poor mental well-being
        'sleep_duration' => 4,       // Very low
        'physical_activity_duration' => 5, // Almost no activity
        'screen_time_duration' => 8,       // Excessive
    ]);

    $index = $service->calculateIndex($checkIn);

    expect($index)->toBeLessThanOrEqual(40);
});

it('generates warning insight when sleep is low and screen time is high', function () {
    $service = new WellbeingScoringService;

    $checkIn = new CheckIn([
        'mood' => 'stressed',
        'sleep_duration' => 4,
        'screen_time_duration' => 8,
        'physical_activity_duration' => 10,
    ]);

    $result = $service->generateInsight($checkIn);

    expect($result['insight'])->toContain('screen time')
        ->and($result['recommendation'])->not->toBeEmpty();
});

it('generates positive insight when all metrics are good', function () {
    $service = new WellbeingScoringService;

    $checkIn = new CheckIn([
        'mood' => 'calm',
        'who5_score' => 90,
        'sleep_duration' => 9,
        'physical_activity_duration' => 60,
        'screen_time_duration' => 1,
    ]);

    $result = $service->generateInsight($checkIn);

    expect($result['insight'])->toContain('seimbang');
});

it('detects declining patterns over multiple days', function () {
    $user = User::factory()->create();

    // Simulate 5 days of declining data
    foreach (range(0, 4) as $i) {
        CheckIn::create([
            'user_id' => $user->id,
            'mood' => $i < 2 ? 'calm' : 'stressed',
            'who5_score' => 80 - ($i * 10),
            'sleep_duration' => 9 - ($i * 0.8),
            'physical_activity_duration' => 60 - ($i * 10),
            'screen_time_duration' => 1 + ($i * 1.5),
            'check_in_date' => now()->subDays(4 - $i),
        ]);
    }

    $service = new PatternDetectionService;
    $result = $service->analyze($user, 7);

    expect($result['warning_level'])->not->toBe('none')
        ->and($result['patterns'])->not->toBeEmpty();
});

it('returns insufficient data message when fewer than 3 check-ins exist', function () {
    $user = User::factory()->create();

    CheckIn::create([
        'user_id' => $user->id,
        'mood' => 'calm',
        'who5_score' => 70,
        'sleep_duration' => 8,
        'physical_activity_duration' => 30,
        'screen_time_duration' => 2,
        'check_in_date' => today(),
    ]);

    $service = new PatternDetectionService;
    $result = $service->analyze($user);

    expect($result['warning_level'])->toBe('none')
        ->and($result['patterns'][0])->toContain('Belum cukup data');
});

it('stores a check-in and calculates wellbeing index via controller', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('checkin.store'), [
        'mood' => 'neutral',
        'who5_score' => 60,
        'sleep_duration' => 7.5,
        'physical_activity_duration' => 45,
        'screen_time_duration' => 3,
        'note' => 'Hari biasa saja.',
    ]);

    $response->assertRedirect(route('dashboard'));

    $checkIn = $user->checkIns()->where('check_in_date', today())->first();

    expect($checkIn)->not->toBeNull()
        ->and($checkIn->wellbeing_index)->not->toBeNull()
        ->and($checkIn->ai_insight)->not->toBeNull()
        ->and($checkIn->mood)->toBe('neutral');
});

it('prevents duplicate check-in on the same day', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('checkin.store'), [
        'mood' => 'calm',
        'who5_score' => 70,
        'sleep_duration' => 8,
        'physical_activity_duration' => 30,
        'screen_time_duration' => 2,
    ]);

    $this->actingAs($user)->post(route('checkin.store'), [
        'mood' => 'stressed',
        'who5_score' => 40,
        'sleep_duration' => 5,
        'physical_activity_duration' => 10,
        'screen_time_duration' => 6,
    ]);

    expect($user->checkIns()->where('check_in_date', today())->count())->toBe(1)
        ->and($user->checkIns()->where('check_in_date', today())->first()->mood)->toBe('stressed');
});

it('updates user streak on check-in', function () {
    $user = User::factory()->create([
        'current_streak' => 2,
        'last_check_in_date' => now()->subDay(),
    ]);

    $this->actingAs($user)->post(route('checkin.store'), [
        'mood' => 'energetic',
        'who5_score' => 80,
        'sleep_duration' => 9,
        'physical_activity_duration' => 60,
        'screen_time_duration' => 1,
    ]);

    $user->refresh();

    expect($user->current_streak)->toBe(3)
        ->and($user->last_check_in_date->isToday())->toBeTrue();
});
