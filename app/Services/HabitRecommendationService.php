<?php

namespace App\Services;

use App\Models\User;

class HabitRecommendationService
{
    public function ensureFor(User $user): void
    {
        $answers = $user->onboardingAnswers()
            ->with(['question:id,key', 'option:id,label'])
            ->get()
            ->filter(fn ($answer): bool => $answer->question !== null && $answer->option !== null)
            ->mapWithKeys(fn ($answer): array => [
                $answer->question->key => mb_strtolower($answer->option->label),
            ])
            ->all();

        foreach ($this->progressRecommendations($answers) as $key => $habit) {
            $user->habits()->firstOrCreate(
                ['recommendation_key' => $key],
                [
                    ...$habit,
                    'source' => 'system',
                    'recommendation_key' => $key,
                    'is_active' => true,
                ],
            );
        }

        foreach ($this->checklistRecommendations($answers) as $key => $habit) {
            $user->habits()->firstOrCreate(
                ['recommendation_key' => $key],
                [
                    ...$habit,
                    'source' => 'system',
                    'recommendation_key' => $key,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @param  array<string, string>  $answers
     * @return array<string, array{name: string, type: string, target_value: int, unit: string}>
     */
    private function progressRecommendations(array $answers): array
    {
        $goal = $answers['goal'] ?? '';
        $sleep = $answers['sleep'] ?? '';
        $overthinking = $answers['overthink'] ?? '';
        $mood = $answers['mood'] ?? '';
        $sleepConcern = str_contains($sleep, 'hard to fall')
            || str_contains($sleep, 'waking')
            || str_contains($sleep, 'nightmare')
            || str_contains($sleep, 'too much');

        if (str_contains($goal, 'sleep') || $sleepConcern) {
            return [
                'progress_steady_bedtime' => [
                    'name' => 'Keep a consistent bedtime',
                    'type' => 'progress',
                    'target_value' => 1,
                    'unit' => 'night',
                ],
                'progress_wind_down' => [
                    'name' => 'Wind down without screens',
                    'type' => 'progress',
                    'target_value' => 20,
                    'unit' => 'minutes',
                ],
            ];
        }

        if (str_contains($overthinking, 'school') || str_contains($overthinking, 'workload')) {
            return [
                'progress_focus_block' => [
                    'name' => 'Finish one focused study block',
                    'type' => 'progress',
                    'target_value' => 1,
                    'unit' => 'session',
                ],
                'progress_study_break' => [
                    'name' => 'Stretch between study blocks',
                    'type' => 'progress',
                    'target_value' => 5,
                    'unit' => 'minutes',
                ],
            ];
        }

        if (str_contains($goal, 'habit')) {
            return [
                'progress_morning_water' => [
                    'name' => 'Drink water after waking up',
                    'type' => 'progress',
                    'target_value' => 1,
                    'unit' => 'glass',
                ],
                'progress_daily_movement' => [
                    'name' => 'Move your body',
                    'type' => 'progress',
                    'target_value' => 10,
                    'unit' => 'minutes',
                ],
            ];
        }

        if (str_contains($goal, 'emotion') || str_contains($mood, 'sad') || str_contains($mood, 'numb')) {
            return [
                'progress_mood_note' => [
                    'name' => 'Write one line about your mood',
                    'type' => 'progress',
                    'target_value' => 1,
                    'unit' => 'line',
                ],
                'progress_breathing' => [
                    'name' => 'Take a mindful breathing break',
                    'type' => 'progress',
                    'target_value' => 5,
                    'unit' => 'minutes',
                ],
            ];
        }

        return [
            'progress_breathing' => [
                'name' => 'Take a mindful breathing break',
                'type' => 'progress',
                'target_value' => 5,
                'unit' => 'minutes',
            ],
            'progress_daily_movement' => [
                'name' => 'Take a short walk',
                'type' => 'progress',
                'target_value' => 10,
                'unit' => 'minutes',
            ],
        ];
    }

    /**
     * @param  array<string, string>  $answers
     * @return array<string, array{name: string, type: string, target_value: int, unit: string}>
     */
    private function checklistRecommendations(array $answers): array
    {
        $overthinking = $answers['overthink'] ?? '';
        $sleep = $answers['sleep'] ?? '';
        $coping = $answers['coping'] ?? '';
        $mood = $answers['mood'] ?? '';

        $firstTask = str_contains($overthinking, 'school') || str_contains($overthinking, 'workload')
            ? 'Take a short break between study sessions'
            : 'Pause and take five slow breaths';

        $supportTask = str_contains($coping, 'isolate')
            ? 'Reach out to someone you trust'
            : (str_contains($coping, 'music')
                ? 'Listen to a song that helps you reset'
                : (str_contains($coping, 'scroll')
                    ? 'Take a short break from scrolling'
                    : 'Choose one healthy way to recharge'));

        $reflectionTask = str_contains($sleep, 'hard to fall') || str_contains($sleep, 'nightmare')
            ? 'Start a calm bedtime routine'
            : (str_contains($mood, 'anxious') || str_contains($mood, 'stressed')
                ? 'Write one sentence about how you feel'
                : 'Write down one thing that went well today');

        return [
            'checklist_pause' => [
                'name' => $firstTask,
                'type' => 'checklist',
                'target_value' => 1,
                'unit' => 'task',
            ],
            'checklist_coping' => [
                'name' => $supportTask,
                'type' => 'checklist',
                'target_value' => 1,
                'unit' => 'task',
            ],
            'checklist_reflection' => [
                'name' => $reflectionTask,
                'type' => 'checklist',
                'target_value' => 1,
                'unit' => 'task',
            ],
        ];
    }
}
