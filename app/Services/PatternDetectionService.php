<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class PatternDetectionService
{
    /**
     * Analyze patterns from the last N days of check-in data.
     *
     * @return array{patterns: array<string>, warning_level: string, trends: array<string, string>}
     */
    public function analyze(User $user, int $days = 7): array
    {
        $checkIns = $user->checkIns()
            ->where('check_in_date', '>=', now()->subDays($days))
            ->orderBy('check_in_date')
            ->get();

        if ($checkIns->count() < 3) {
            return [
                'patterns' => [__('Not enough data to detect a multi-day pattern. Keep checking in daily!')],
                'warning_level' => 'none',
                'trends' => [],
            ];
        }

        $patterns = [];
        $trends = [];

        $trends['sleep'] = $this->detectTrend($checkIns, 'sleep_duration');
        $trends['activity'] = $this->detectTrend($checkIns, 'physical_activity_duration');
        $trends['screen_time'] = $this->detectTrend($checkIns, 'screen_time_duration');
        $trends['wellbeing'] = $this->detectTrend($checkIns, 'wellbeing_index');

        // Pattern: Sleep declining + Screen time increasing
        if ($trends['sleep'] === 'declining' && $trends['screen_time'] === 'increasing') {
            $patterns[] = __('Your sleep duration is decreasing while your screen time is increasing.');
        }

        // Pattern: Low activity + declining wellbeing
        if ($trends['activity'] === 'declining' && $trends['wellbeing'] === 'declining') {
            $patterns[] = __('Your physical activity is dropping along with your overall well-being. These might be connected.');
        }

        // Pattern: Sleep declining + Wellbeing declining
        if ($trends['sleep'] === 'declining' && $trends['wellbeing'] === 'declining') {
            $patterns[] = __('Your declining sleep pattern seems to correlate with a drop in your well-being index.');
        }

        // Pattern: Consistent stressed/exhausted mood
        $recentMoods = $checkIns->pluck('mood')->toArray();
        $negativeMoodCount = count(array_filter($recentMoods, fn (string $m) => in_array($m, ['stressed', 'exhausted'])));
        if ($negativeMoodCount >= ceil($checkIns->count() * 0.6)) {
            $patterns[] = __('You have been consistently feeling stressed or exhausted. Please consider talking to someone you trust.');
        }

        // Pattern: Everything improving
        if ($trends['sleep'] === 'improving' && $trends['wellbeing'] === 'improving') {
            $patterns[] = __('Your sleep patterns and well-being are both showing signs of improvement. Great job!');
        }

        $warningLevel = $this->determineWarningLevel($trends, $patterns);

        if (empty($patterns)) {
            // Only congratulate if the average wellbeing is good. Otherwise, just state it's stable.
            $avgWellbeing = $checkIns->pluck('wellbeing_index')->filter()->average() ?? 0;
            if ($avgWellbeing >= 70) {
                $patterns[] = __('Your daily patterns look stable and healthy. Keep up the consistency!');
            } else {
                $patterns[] = __('Your daily patterns have been stable, but there is room for improvement in your habits.');
            }
        }

        return [
            'patterns' => $patterns,
            'warning_level' => $warningLevel,
            'trends' => $trends,
        ];
    }

    /**
     * Detect trend direction for a specific metric.
     */
    private function detectTrend(Collection $checkIns, string $field): string
    {
        $values = $checkIns->pluck($field)->filter()->values();

        if ($values->count() < 3) {
            return 'insufficient';
        }

        $halfPoint = (int) floor($values->count() / 2);
        $firstHalf = $values->slice(0, $halfPoint)->average();
        $secondHalf = $values->slice($halfPoint)->average();

        if ($firstHalf == 0) {
            return 'stable';
        }

        $changePercent = (($secondHalf - $firstHalf) / $firstHalf) * 100;

        // For screen_time, "increasing" is negative; for sleep/activity, "declining" is negative
        $isNegativeMetric = $field === 'screen_time_duration';

        if (abs($changePercent) < 10) {
            return 'stable';
        }

        if ($changePercent > 0) {
            return $isNegativeMetric ? 'increasing' : 'improving';
        }

        return $isNegativeMetric ? 'decreasing' : 'declining';
    }

    /**
     * Determine the Early Warning level based on combined trends.
     */
    private function determineWarningLevel(array $trends, array $patterns): string
    {
        $negativeSignals = 0;

        if (($trends['sleep'] ?? '') === 'declining') {
            $negativeSignals++;
        }
        if (($trends['wellbeing'] ?? '') === 'declining') {
            $negativeSignals++;
        }
        if (($trends['screen_time'] ?? '') === 'increasing') {
            $negativeSignals++;
        }
        if (($trends['activity'] ?? '') === 'declining') {
            $negativeSignals++;
        }

        if ($negativeSignals >= 3) {
            return 'high';
        }
        if ($negativeSignals >= 2) {
            return 'medium';
        }
        if ($negativeSignals >= 1) {
            return 'low';
        }

        return 'none';
    }
}
