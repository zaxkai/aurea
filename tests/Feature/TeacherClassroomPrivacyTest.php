<?php

use App\Livewire\Settings;
use App\Livewire\Teacher\StudentWellbeing;
use App\Livewire\Teacher\TeacherDashboard;
use App\Models\CheckIn;
use App\Models\Classroom;
use App\Models\User;
use Livewire\Livewire;

it('renders teacher dashboard and scopes data strictly to enrolled students', function () {
    $teacher = User::factory()->create([
        'name' => 'Julian Casablancas',
        'first_name' => 'Julian',
        'role' => 'teacher',
        'onboarding_completed_at' => now(),
    ]);

    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Class 10-A',
        'code' => 'AUR99A',
    ]);

    // Student A enrolled in teacher's class
    $studentA = User::factory()->create([
        'name' => 'Alex Turner',
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
    $studentA->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);

    CheckIn::create([
        'user_id' => $studentA->id,
        'mood' => 'calm',
        'wellbeing_index' => 85,
        'check_in_date' => today(),
    ]);

    // Independent Student B (NOT enrolled in any class)
    $studentB = User::factory()->create([
        'name' => 'Maya Independent',
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    CheckIn::create([
        'user_id' => $studentB->id,
        'mood' => 'stressed',
        'wellbeing_index' => 30,
        'check_in_date' => today(),
    ]);

    // Test TeacherDashboard component
    Livewire::actingAs($teacher)
        ->test(TeacherDashboard::class)
        ->assertViewHas('studentsCount', 1)
        ->assertViewHas('wellbeingScore', 85)
        ->assertSee('Class 10-A')
        ->assertSee('AUR99A');
});

it('allows student to safely join classroom via code and leave in settings', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Biologi 1',
        'code' => 'BIO101',
    ]);

    $student = User::factory()->create(['role' => 'student']);

    expect($student->enrolledClassrooms()->count())->toBe(0);

    // Join classroom
    Livewire::actingAs($student)
        ->test(Settings::class)
        ->set('classCode', 'BIO101')
        ->call('joinClassroom')
        ->assertSet('classroomMessage', 'Berhasil bergabung ke Biologi 1 (Guru: '.$teacher->name.').');

    expect($student->fresh()->enrolledClassrooms()->count())->toBe(1);

    // Leave classroom
    Livewire::actingAs($student)
        ->test(Settings::class)
        ->call('leaveClassroom', $classroom->id)
        ->assertSet('classroomMessage', 'Kamu telah keluar dari kelas tersebut.');

    expect($student->fresh()->enrolledClassrooms()->count())->toBe(0);
});

it('renders student wellbeing list only for teacher with enrolled students', function () {
    $teacher = User::factory()->create(['role' => 'teacher', 'onboarding_completed_at' => now()]);
    $classroom = Classroom::create([
        'teacher_id' => $teacher->id,
        'name' => 'Class 10-B',
        'code' => 'AUR99B',
    ]);

    $student = User::factory()->create(['name' => 'Miles Kane', 'role' => 'student']);
    $student->enrolledClassrooms()->attach($classroom->id, ['joined_at' => now()]);

    $independent = User::factory()->create(['name' => 'Secret Teen', 'role' => 'student']);

    Livewire::actingAs($teacher)
        ->test(StudentWellbeing::class)
        ->assertSee('Miles Kane')
        ->assertSee('Class 10-B')
        ->assertDontSee('Secret Teen');
});

it('allows teacher to create a new classroom with auto-generated code', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    Livewire::actingAs($teacher)
        ->test(TeacherDashboard::class)
        ->set('newClassName', 'Kelas 11 RPL')
        ->set('newSchoolName', 'SMK Negeri 1')
        ->call('createClassroom')
        ->assertHasNoErrors();

    $created = Classroom::where('teacher_id', $teacher->id)->first();
    expect($created)->not->toBeNull()
        ->and($created->name)->toBe('Kelas 11 RPL')
        ->and($created->code)->toStartWith('AUR-');
});

it('allows user to log out from settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test(Settings::class);

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
