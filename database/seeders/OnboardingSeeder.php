<?php

namespace Database\Seeders;

use App\Models\OnboardingOption;
use App\Models\OnboardingQuestion;
use Illuminate\Database\Seeder;

class OnboardingSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'key' => 'overthink',
                'question' => "What's been making you overthink the most lately?",
                'order' => 1,
                'options' => [
                    'School & heavy workload',
                    'Friendships & relationships',
                    'Worrying about the future',
                    'Pressure from parents',
                    'Feeling insecure',
                ],
            ],
            [
                'key' => 'sleep',
                'question' => 'How has your sleep been recently?',
                'order' => 2,
                'options' => [
                    'Sleeping well',
                    'Hard to fall asleep',
                    'Waking up often',
                    'Sleeping too much',
                    'Nightmares',
                ],
            ],
            [
                'key' => 'mood',
                'question' => 'How would you describe your overall mood?',
                'order' => 3,
                'options' => [
                    'Generally happy',
                    'Anxious or stressed',
                    'Sad or down',
                    'Numb or empty',
                    'Easily irritated',
                ],
            ],
            [
                'key' => 'coping',
                'question' => 'What do you usually do when you feel stressed?',
                'order' => 4,
                'options' => [
                    'Talk to someone',
                    'Listen to music',
                    'Isolate myself',
                    'Scroll social media',
                    'Play games',
                ],
            ],
            [
                'key' => 'goal',
                'question' => 'What do you hope to achieve with Aurea?',
                'order' => 5,
                'options' => [
                    'Manage my stress',
                    'Understand my emotions',
                    'Build better habits',
                    'Improve my sleep',
                    'Just looking around',
                ],
            ],
        ];

        foreach ($questions as $qData) {
            $question = OnboardingQuestion::firstOrCreate([
                'key' => $qData['key'],
            ], [
                'question' => $qData['question'],
                'order' => $qData['order'],
                'is_active' => true,
            ]);

            foreach ($qData['options'] as $index => $optionText) {
                OnboardingOption::firstOrCreate([
                    'question_id' => $question->id,
                    'label' => $optionText,
                ], [
                    'value' => strtolower(str_replace(' ', '_', $optionText)),
                    'order' => $index + 1,
                ]);
            }
        }
    }
}
