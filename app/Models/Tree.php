<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    protected $fillable = ['user_id', 'growth_percentage'];

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

        $this->growth_percentage = (int) round((($completedHabits + $completedTasks) / $totalItems) * 100);

        if ($this->isDirty('growth_percentage')) {
            $this->save();
        }
    }
}
