<?php

namespace Database\Seeders;

use App\Models\CheckIn;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create or update Julian Casablancas
        $user = User::updateOrCreate(
            ['email' => 'julian@aurea.my.id'],
            [
                'name' => 'Julian Casablancas',
                'password' => Hash::make('password'),
                'current_streak' => 30,
                'longest_streak' => 30,
                'last_check_in_date' => today(),
            ]
        );

        // Also create a test user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Julian Casablancas',
                'password' => Hash::make('password'),
                'current_streak' => 30,
                'longest_streak' => 30,
                'last_check_in_date' => today(),
            ]
        );

        // 2. Initialize Tree at 30%
        Tree::updateOrCreate(
            ['user_id' => $user->id],
            ['growth_percentage' => 30]
        );

        // 3. Create Checklist Habits
        $h1 = Habit::updateOrCreate(
            ['user_id' => $user->id, 'name' => "Write 3 things I'm grateful for"],
            [
                'type' => 'checklist',
                'target_value' => 1,
                'unit' => 'times',
                'is_active' => true,
            ]
        );

        $h2 = Habit::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Tidy up the workspace'],
            [
                'type' => 'checklist',
                'target_value' => 1,
                'unit' => 'times',
                'is_active' => true,
            ]
        );

        // 4. Create Progress Habits
        $h3 = Habit::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Drink 2L of water'],
            [
                'type' => 'progress',
                'target_value' => 2000,
                'unit' => 'ML',
                'is_active' => true,
            ]
        );

        $h4 = Habit::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Walk around the neighborhood'],
            [
                'type' => 'progress',
                'target_value' => 5000,
                'unit' => 'Steps',
                'is_active' => true,
            ]
        );

        // 5. Seed today's habit logs
        HabitLog::updateOrCreate(
            ['habit_id' => $h3->id, 'log_date' => today()],
            ['value_logged' => 500]
        );

        HabitLog::updateOrCreate(
            ['habit_id' => $h4->id, 'log_date' => today()],
            ['value_logged' => 300]
        );

        // 6. Seed past check-ins for the calendar grid
        $sampleMoods = [
            'energetic', 'calm', 'neutral', 'stressed', 'exhausted',
            'calm', 'energetic', 'neutral', 'neutral', 'calm',
            'stressed', 'exhausted', 'energetic', 'calm', 'calm',
            'neutral', 'energetic', 'energetic', 'neutral', 'calm',
        ];

        for ($i = 20; $i >= 1; $i--) {
            $date = today()->subDays($i);
            $mood = $sampleMoods[20 - $i] ?? 'calm';

            CheckIn::updateOrCreate(
                ['user_id' => $user->id, 'check_in_date' => $date],
                [
                    'mood' => $mood,
                    'who5_score' => rand(60, 90),
                    'sleep_duration' => rand(7, 9) + (rand(0, 5) / 10),
                    'physical_activity_duration' => rand(30, 75),
                    'screen_time_duration' => rand(2, 5) + (rand(0, 5) / 10),
                    'wellbeing_index' => rand(70, 95),
                    'ai_insight' => 'Pola tidur dan aktivitas fisikmu seimbang.',
                    'note' => 'Catatan harian '.$date->format('d M'),
                ]
            );
        }

        // Today's check-in
        CheckIn::updateOrCreate(
            ['user_id' => $user->id, 'check_in_date' => today()],
            [
                'mood' => 'calm',
                'who5_score' => 75,
                'sleep_duration' => 8.0,
                'physical_activity_duration' => 45,
                'screen_time_duration' => 3.5,
                'wellbeing_index' => 84,
                'ai_insight' => 'Durasi tidurmu optimal. Pertahankan konsistensi ini menjelang malam!',
                'note' => 'Feeling good and productive today!',
            ]
        );
    }
}
