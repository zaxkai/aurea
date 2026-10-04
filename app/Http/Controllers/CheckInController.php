<?php

namespace App\Http\Controllers;

use App\Notifications\AppNotification;
use App\Services\PatternDetectionService;
use App\Services\WellbeingScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function __construct(
        private WellbeingScoringService $scoringService,
        private PatternDetectionService $patternService,
    ) {}

    /**
     * Store a new daily check-in, calculate the Well-being Index, and generate insights.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mood' => ['required', 'in:energetic,calm,neutral,stressed,exhausted'],
            'who5_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'sleep_duration' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'physical_activity_duration' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'screen_time_duration' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        // Prevent duplicate check-in for the same day
        $existingCheckIn = $user->checkIns()
            ->where('check_in_date', today())
            ->first();

        if ($existingCheckIn) {
            $checkIn = $existingCheckIn;
            $checkIn->update($validated);
        } else {
            $checkIn = $user->checkIns()->create([
                ...$validated,
                'check_in_date' => today(),
            ]);
        }

        // Calculate Well-being Index
        $wellbeingIndex = $this->scoringService->calculateIndex($checkIn);

        // Generate insight
        $insightData = $this->scoringService->generateInsight($checkIn);

        // Detect multi-day patterns
        $patternData = $this->patternService->analyze($user);

        // Combine insights
        $combinedInsight = $insightData['insight'];
        if (! empty($patternData['patterns'])) {
            $combinedInsight .= ' '.implode(' ', $patternData['patterns']);
        }

        // Update the check-in with calculated values
        $checkIn->update([
            'wellbeing_index' => $wellbeingIndex,
            'ai_insight' => $combinedInsight,
        ]);

        // Update user streak
        $this->updateStreak($user);

        return redirect()->route('dashboard')->with([
            'checkin_success' => true,
            'wellbeing_index' => $wellbeingIndex,
            'wellbeing_category' => $checkIn->fresh()->wellbeing_category,
            'recommendation' => $insightData['recommendation'],
            'warning_level' => $patternData['warning_level'],
        ]);
    }

    /**
     * Update the user's daily streak counter.
     */
    private function updateStreak(mixed $user): void
    {
        $lastCheckInDate = $user->last_check_in_date;

        if ($lastCheckInDate && $lastCheckInDate->isYesterday()) {
            $user->increment('current_streak');
        } elseif (! $lastCheckInDate || ! $lastCheckInDate->isToday()) {
            $user->current_streak = 1;
        }

        $user->last_check_in_date = today();
        $user->save();

        if (in_array($user->current_streak, [3, 7, 14, 30, 60, 100])) {
            $user->notify(new AppNotification(
                '🔥 '.$user->current_streak.' Days Streak!',
                "You're on fire! You've checked in for {$user->current_streak} consecutive days.",
                'fire',
                'warning'
            ));
        }
    }
}
