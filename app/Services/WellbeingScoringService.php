<?php

namespace App\Services;

use App\Models\CheckIn;

class WellbeingScoringService
{
    /**
     * Calculate the final Well-being Index based on evidence-based indicators.
     *
     * Framework:
     * - Mental Well-being (WHO-5): 0-100 (Weight: 40%)
     * - Sleep Duration (National Sleep Foundation for Teens 8-10h): 0-100 (Weight: 20%)
     * - Physical Activity (WHO recommends 60+ mins): 0-100 (Weight: 20%)
     * - Screen Time: 0-100 (Weight: 20%)
     */
    public function calculateIndex(CheckIn $checkIn): int
    {
        $who5Score = $checkIn->who5_score ?? 0;

        $sleepScore = $this->calculateSleepScore($checkIn->sleep_duration ?? 0);
        $activityScore = $this->calculateActivityScore($checkIn->physical_activity_duration ?? 0);
        $screenTimeScore = $this->calculateScreenTimeScore($checkIn->screen_time_duration ?? 0);

        // Weighted Average Calculation
        $finalIndex = ($who5Score * 0.40) +
                      ($sleepScore * 0.20) +
                      ($activityScore * 0.20) +
                      ($screenTimeScore * 0.20);

        return (int) round($finalIndex);
    }

    /**
     * Generate AI-like insight and recommendation based on the current data and past patterns.
     */
    public function generateInsight(CheckIn $checkIn): array
    {
        $insights = [];
        $recommendations = [];

        // Sleep pattern detection
        if ($checkIn->sleep_duration < 7) {
            $insights[] = 'Durasi tidurmu kurang dari batas ideal untuk usiamu.';
            $recommendations[] = 'Cobalah untuk tidur lebih awal agar mencapai 8 jam istirahat.';
        }

        // Screen time vs Sleep correlation logic
        if ($checkIn->screen_time_duration > 5 && $checkIn->sleep_duration < 7) {
            $insights[] = 'Tingginya screen time tampaknya memengaruhi durasi tidurmu belakangan ini.';
            $recommendations[] = 'Kurangi penggunaan gadget setidaknya 1 jam sebelum tidur.';
        }

        // Activity logic
        if ($checkIn->physical_activity_duration < 30) {
            $insights[] = 'Aktivitas fisikmu tergolong rendah hari ini.';
            $recommendations[] = 'Sempatkan jalan santai atau peregangan ringan selama 15-30 menit besok.';
        }

        if (empty($insights)) {
            $insights[] = 'Pola harianmu terlihat sangat seimbang. Pertahankan!';
            $recommendations[] = 'Lanjutkan rutinitas baikmu dan pastikan tetap terhidrasi.';
        }

        return [
            'insight' => implode(' ', $insights),
            'recommendation' => implode(' ', $recommendations),
        ];
    }

    private function calculateSleepScore(float $hours): int
    {
        // Teenagers (14-17 years): 8-10 hours is optimal
        if ($hours >= 8 && $hours <= 10) {
            return 100;
        }
        if ($hours >= 7 && $hours < 8) {
            return 80;
        }
        if ($hours > 10 && $hours <= 11) {
            return 80;
        }
        if ($hours >= 6 && $hours < 7) {
            return 60;
        }

        return 40; // < 6 hours or > 11 hours
    }

    private function calculateActivityScore(int $minutes): int
    {
        // WHO recommends at least 60 mins of moderate-to-vigorous physical activity daily
        if ($minutes >= 60) {
            return 100;
        }
        if ($minutes >= 45) {
            return 80;
        }
        if ($minutes >= 30) {
            return 60;
        }
        if ($minutes >= 15) {
            return 40;
        }

        return 20;
    }

    private function calculateScreenTimeScore(float $hours): int
    {
        // Ideal recreational screen time is often cited as < 2 hours
        if ($hours <= 2) {
            return 100;
        }
        if ($hours <= 4) {
            return 80;
        }
        if ($hours <= 6) {
            return 50;
        }

        return 20;
    }
}
