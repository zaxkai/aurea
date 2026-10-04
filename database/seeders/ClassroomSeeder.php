<?php

namespace Database\Seeders;

use App\Models\CheckIn;
use App\Models\Classroom;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Teacher Julian (matching the mockup)
        $teacher = User::updateOrCreate(
            ['email' => 'julian@aurea.my.id'],
            [
                'name' => 'Julian Casablancas',
                'first_name' => 'Julian',
                'last_name' => 'Casablancas',
                'role' => 'teacher',
                'password' => Hash::make('password'),
                'onboarding_completed_at' => now(),
            ]
        );

        // 2. Create Classrooms for Teacher Julian
        $classroomA = Classroom::firstOrCreate(
            ['code' => 'AUR10A'],
            [
                'teacher_id' => $teacher->id,
                'name' => 'Kelas 10 IPA 1',
                'school_name' => 'SMA Negeri Aurea',
                'description' => 'Kelas Bimbingan Konseling dan Kesejahteraan Remaja 10 IPA 1',
            ]
        );

        $classroomB = Classroom::firstOrCreate(
            ['code' => 'AUR10B'],
            [
                'teacher_id' => $teacher->id,
                'name' => 'Kelas 10 IPS 2',
                'school_name' => 'SMA Negeri Aurea',
                'description' => 'Kelas Bimbingan Konseling 10 IPS 2',
            ]
        );

        // 3. Create Sample Enrolled Students
        $studentsData = [
            ['name' => 'Alex Turner', 'first' => 'Alex', 'last' => 'Turner', 'email' => 'alex@student.aurea.test', 'mood' => 'calm', 'wellbeing' => 85, 'class' => $classroomA],
            ['name' => 'Miles Kane', 'first' => 'Miles', 'last' => 'Kane', 'email' => 'miles@student.aurea.test', 'mood' => 'neutral', 'wellbeing' => 72, 'class' => $classroomA],
            ['name' => 'Albert Hammond', 'first' => 'Albert', 'last' => 'Hammond', 'email' => 'albert@student.aurea.test', 'mood' => 'stressed', 'wellbeing' => 54, 'class' => $classroomA],
            ['name' => 'Nikolai Fraiture', 'first' => 'Nikolai', 'last' => 'Fraiture', 'email' => 'nikolai@student.aurea.test', 'mood' => 'neutral', 'wellbeing' => 70, 'class' => $classroomA],
            ['name' => 'Fabrizio Moretti', 'first' => 'Fabrizio', 'last' => 'Moretti', 'email' => 'fabrizio@student.aurea.test', 'mood' => 'calm', 'wellbeing' => 88, 'class' => $classroomA],
            ['name' => 'Nick Valensi', 'first' => 'Nick', 'last' => 'Valensi', 'email' => 'nick@student.aurea.test', 'mood' => 'exhausted', 'wellbeing' => 48, 'class' => $classroomB],
            ['name' => 'Clairo Cottrill', 'first' => 'Clairo', 'last' => 'Cottrill', 'email' => 'clairo@student.aurea.test', 'mood' => 'calm', 'wellbeing' => 90, 'class' => $classroomB],
            ['name' => 'Phoebe Bridgers', 'first' => 'Phoebe', 'last' => 'Bridgers', 'email' => 'phoebe@student.aurea.test', 'mood' => 'stressed', 'wellbeing' => 52, 'class' => $classroomB],
        ];

        foreach ($studentsData as $data) {
            $student = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'first_name' => $data['first'],
                    'last_name' => $data['last'],
                    'role' => 'student',
                    'password' => Hash::make('password'),
                    'onboarding_completed_at' => now(),
                ]
            );

            // Enroll student to classroom
            $targetClass = $data['class'];
            if (! $student->enrolledClassrooms()->where('classroom_id', $targetClass->id)->exists()) {
                $student->enrolledClassrooms()->attach($targetClass->id, ['joined_at' => now()->subDays(rand(1, 14))]);
            }

            // Create past check-ins
            CheckIn::updateOrCreate(
                ['user_id' => $student->id, 'check_in_date' => today()],
                [
                    'mood' => $data['mood'],
                    'wellbeing_index' => $data['wellbeing'],
                    'sleep_duration' => 7.5,
                    'physical_activity_duration' => 45,
                    'screen_time_duration' => 3.5,
                    'note' => 'Daily check-in',
                ]
            );
        }

        // 4. Create an Independent Teenager (NOT connected to any classroom/teacher)
        $independentTeen = User::updateOrCreate(
            ['email' => 'remaja.bebas@gmail.com'],
            [
                'name' => 'Maya Independent',
                'first_name' => 'Maya',
                'last_name' => 'Independent',
                'role' => 'student',
                'password' => Hash::make('password'),
                'onboarding_completed_at' => now(),
            ]
        );

        // Maya has check-ins and secret private journals
        CheckIn::updateOrCreate(
            ['user_id' => $independentTeen->id, 'check_in_date' => today()],
            [
                'mood' => 'stressed',
                'wellbeing_index' => 40,
                'note' => 'Sangat rahasia, data personal Maya.',
            ]
        );

        Journal::updateOrCreate(
            ['user_id' => $independentTeen->id, 'title' => 'Catatan Rahasia Maya'],
            [
                'content' => 'Ini curhat pribadi Maya yang tidak boleh dilihat oleh guru manapun.',
                'mood' => 'stressed',
                'journal_date' => today(),
            ]
        );
    }
}
