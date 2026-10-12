<?php

use App\Livewire\Settings;
use App\Livewire\Teacher\StudentWellbeing;
use App\Models\CheckIn;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

it('sends need attention notification to teacher when enrolled student checks in with low wellbeing or stressed mood', function () {
    $teacher = User::factory()->create([
        'name' => 'Julian Teacher',
        'role' => 'teacher',
        'onboarding_completed_at' => now(),
    ]);

    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Class 10-A',
        'code' => 'AUR10A',
    ]);

    $student = User::factory()->create([
        'name' => 'Alex Student',
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
    $student->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);

    // Student performs check-in with stressed mood and low WHO-5 score
    $response = $this->actingAs($student)->post(route('checkin.store'), [
        'mood' => 'stressed',
        'who5_score' => 20,
        'sleep_duration' => 4,
        'physical_activity_duration' => 10,
        'screen_time_duration' => 8,
    ]);

    $response->assertRedirect(route('dashboard'));

    $notification = $teacher->notifications()->first();
    expect($notification)->not->toBeNull()
        ->and($notification->data['title'])->toBe('⚠️ Siswa Membutuhkan Perhatian')
        ->and($notification->data['type'])->toBe('warning')
        ->and($notification->data['icon'])->toBe('warning')
        ->and($notification->data['message'])->toContain('Alex Student')
        ->and($notification->data['message'])->toContain('Class 10-A');
});

it('does not spam teacher with duplicate attention notifications on the same day for the same student', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Class 10-B',
        'code' => 'AUR10B',
    ]);

    $student = User::factory()->create(['name' => 'Miles Student', 'role' => 'student']);
    $student->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);

    // First check-in
    $this->actingAs($student)->post(route('checkin.store'), [
        'mood' => 'stressed',
        'who5_score' => 20,
    ]);

    expect($teacher->notifications()->count())->toBe(1);

    // Second check-in on the same day
    $this->actingAs($student)->post(route('checkin.store'), [
        'mood' => 'exhausted',
        'who5_score' => 15,
    ]);

    expect($teacher->notifications()->count())->toBe(1);
});

it('sends new student joined notification to teacher when student enters classroom code', function () {
    $teacher = User::factory()->create(['name' => 'Teacher Sarah', 'role' => 'teacher']);
    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Kelas Biologi',
        'code' => 'BIO777',
    ]);

    $student = User::factory()->create(['name' => 'Charlie Newbie', 'role' => 'student']);

    Livewire::actingAs($student)
        ->test(Settings::class)
        ->set('classCode', 'BIO777')
        ->call('joinClassroom');

    $notification = $teacher->notifications()->first();
    expect($notification)->not->toBeNull()
        ->and($notification->data['title'])->toBe('🎓 Siswa Baru Bergabung')
        ->and($notification->data['icon'])->toBe('student')
        ->and($notification->data['type'])->toBe('info')
        ->and($notification->data['message'])->toContain('Charlie Newbie')
        ->and($notification->data['message'])->toContain('Kelas Biologi');
});

it('sends weekly summary notification to teachers with enrolled students via artisan command', function () {
    $teacher = User::factory()->create(['name' => 'Teacher Dave', 'role' => 'teacher']);
    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Class Math',
        'code' => 'MATH101',
    ]);

    $studentHealthy = User::factory()->create(['name' => 'Healthy Student', 'role' => 'student']);
    $studentHealthy->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);
    CheckIn::create([
        'user_id' => $studentHealthy->id,
        'mood' => 'calm',
        'wellbeing_index' => 85,
        'check_in_date' => today(),
    ]);

    $studentStressed = User::factory()->create(['name' => 'Stressed Student', 'role' => 'student']);
    $studentStressed->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);
    CheckIn::create([
        'user_id' => $studentStressed->id,
        'mood' => 'stressed',
        'wellbeing_index' => 40,
        'check_in_date' => today(),
    ]);

    Artisan::call('aurea:send-weekly-teacher-summary');

    $notification = $teacher->notifications()->first();
    expect($notification)->not->toBeNull()
        ->and($notification->data['title'])->toBe('📊 Rekap Mingguan Kelas')
        ->and($notification->data['icon'])->toBe('chart')
        ->and($notification->data['message'])->toContain('1 siswa memerlukan perhatian')
        ->and($notification->data['message'])->toContain('1 siswa kondisi stabil');
});

it('supports URL search and status filter parameters in StudentWellbeing', function () {
    $teacher = User::factory()->create(['role' => 'teacher', 'onboarding_completed_at' => now()]);
    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Class Alpha',
        'code' => 'ALPHA01',
    ]);

    $studentA = User::factory()->create(['name' => 'Student Alpha', 'role' => 'student']);
    $studentA->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);
    CheckIn::create([
        'user_id' => $studentA->id,
        'mood' => 'calm',
        'wellbeing_index' => 90,
        'check_in_date' => today(),
    ]);

    $studentB = User::factory()->create(['name' => 'Student Beta', 'role' => 'student']);
    $studentB->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);
    CheckIn::create([
        'user_id' => $studentB->id,
        'mood' => 'stressed',
        'wellbeing_index' => 45,
        'check_in_date' => today(),
    ]);

    // Test filter by search
    Livewire::actingAs($teacher)
        ->test(StudentWellbeing::class, ['search' => 'Beta'])
        ->assertSee('Student Beta')
        ->assertDontSee('Student Alpha');

    // Test filter by attention status
    Livewire::actingAs($teacher)
        ->test(StudentWellbeing::class, ['statusFilter' => 'attention'])
        ->assertSee('Student Beta')
        ->assertDontSee('Student Alpha');
});
