<?php

namespace App\Models;

use App\Notifications\AppNotification;
use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    protected $fillable = ['user_id', 'growth_percentage'];

    public static function growthStageForCompletedItems(int $completedItems): int
    {
        return match (true) {
            $completedItems >= 10 => 4,
            $completedItems >= 6 => 3,
            $completedItems >= 3 => 2,
            $completedItems >= 1 => 1,
            default => 0,
        };
    }

    public function completedGrowthItemsCount(): int
    {
        $user = $this->user;
        $completedTasks = $user->tasks()->where('is_done', true)->count();
        $completedHabits = $user->habits()
            ->where('is_active', true)
            ->whereHas('logs', function ($query): void {
                $query
                    ->whereDate('log_date', today())
                    ->whereColumn('habit_logs.value_logged', '>=', 'habits.target_value');
            })
            ->count();

        return $completedTasks + $completedHabits;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refreshGrowthPercentage(): void
    {
        $user = $this->user;
        $habits = $user->habits()
            ->where('is_active', true)
            ->with(['logs' => fn ($query) => $query->whereDate('log_date', today())])
            ->get();
        $tasks = $user->tasks()->get(['is_done']);
        $totalItems = $habits->count() + $tasks->count();

        if ($totalItems === 0) {
            return;
        }

        $completedHabits = $habits->filter(function (Habit $habit): bool {
            $log = $habit->logs->first();

            return $log !== null && $log->value_logged >= $habit->target_value;
        })->count();
        $completedTasks = $tasks->where('is_done', true)->count();

        $oldPercentage = $this->growth_percentage;
        $this->growth_percentage = (int) round((($completedHabits + $completedTasks) / $totalItems) * 100);

        if ($this->isDirty('growth_percentage')) {
            $this->save();

            $oldStage = self::growthStageForCompletedItems((int) round(($oldPercentage / 100) * $totalItems));
            $newStage = self::growthStageForCompletedItems((int) round(($this->growth_percentage / 100) * $totalItems));

            if ($newStage > $oldStage) {
                $this->user->notify(new AppNotification(
                    '🌳 Tree Leveled Up!',
                    'Your Habit Growth Tree just reached a new stage. Keep it up!',
                    'tree',
                    'success',
                    '/habit-growth-tree'
                ));
            }
        }
    }
}
