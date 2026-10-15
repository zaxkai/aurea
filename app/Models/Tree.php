<?php

namespace App\Models;

use App\Notifications\AppNotification;
use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    protected $fillable = ['user_id', 'growth_percentage', 'skin'];

    public const AVAILABLE_SKINS = [
        'default' => [
            'key' => 'default',
            'name' => 'Classic Aurea',
            'tag' => 'Free • Default',
            'desc' => 'Original Aurea tree with vibrant green leaves and a lush canopy.',
            'is_premium' => false,
            'color' => '#10B981',
            'icon' => '🌳',
        ],
        'sakura' => [
            'key' => 'sakura',
            'name' => 'Cherry Blossom',
            'tag' => '⭐ Premium Exclusive',
            'desc' => 'Japanese sakura tree with calming falling pink petals.',
            'is_premium' => true,
            'color' => '#EC4899',
            'icon' => '🌸',
        ],
        'cemara' => [
            'key' => 'cemara',
            'name' => 'Pine Tree',
            'tag' => '⭐ Premium Exclusive',
            'desc' => 'Sturdy evergreen pine tree symbolizing resilience and peace of mind.',
            'is_premium' => true,
            'color' => '#059669',
            'icon' => '🌲',
        ],
        'gugur' => [
            'key' => 'gugur',
            'name' => 'Autumn Season',
            'tag' => '⭐ Premium Exclusive',
            'desc' => 'Warm golden maple foliage with an aesthetic, reflective mood.',
            'is_premium' => true,
            'color' => '#EA580C',
            'icon' => '🍂',
        ],
        'natal' => [
            'key' => 'natal',
            'name' => 'Christmas Tree',
            'tag' => '⭐ Premium Exclusive',
            'desc' => 'Festive holiday tree adorned with golden star, lights, and joyful gifts.',
            'is_premium' => true,
            'color' => '#DC2626',
            'icon' => '🎄',
        ],
        'buah' => [
            'key' => 'buah',
            'name' => 'Apple Fruit Tree',
            'tag' => '⭐ Premium Exclusive',
            'desc' => 'Lush fruit tree laden with sweet, ripe red apples.',
            'is_premium' => true,
            'color' => '#E11D48',
            'icon' => '🍎',
        ],
    ];

    public function getSkinAttribute(?string $value): string
    {
        return array_key_exists($value ?? '', self::AVAILABLE_SKINS) ? $value : 'default';
    }

    public function getSkinDetailsAttribute(): array
    {
        return self::AVAILABLE_SKINS[$this->skin] ?? self::AVAILABLE_SKINS['default'];
    }

    public function imagePathForStage(int $stage): string
    {
        $skin = $this->skin;
        if ($skin !== 'default' && file_exists(public_path("images/habit-growth-tree/skins/{$skin}/pohon{$stage}.png"))) {
            return asset("images/habit-growth-tree/skins/{$skin}/pohon{$stage}.png");
        }

        return asset("images/habit-growth-tree/pohon{$stage}.png");
    }

    protected ?int $cachedCompletedCount = null;

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
        if ($this->cachedCompletedCount !== null) {
            return $this->cachedCompletedCount;
        }

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

        return $this->cachedCompletedCount = ($completedTasks + $completedHabits);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refreshGrowthPercentage(?iterable $habits = null): void
    {
        $user = $this->relationLoaded('user') ? $this->user : ($this->user()->first());
        if (! $user) {
            return;
        }

        if ($habits === null) {
            $habits = $user->habits()
                ->where('is_active', true)
                ->with(['logs' => fn ($query) => $query->whereDate('log_date', today())])
                ->get();
        } else {
            $habits = collect($habits);
        }

        $tasks = $user->tasks()->get(['is_done']);
        $totalItems = $habits->count() + $tasks->count();

        $completedHabits = $habits->filter(function (Habit $habit): bool {
            $log = $habit->logs->first();

            return $log !== null && $log->value_logged >= $habit->target_value;
        })->count();
        $completedTasks = $tasks->where('is_done', true)->count();

        $this->cachedCompletedCount = $completedHabits + $completedTasks;

        if ($totalItems === 0) {
            return;
        }

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
