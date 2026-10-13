<?php

namespace App\Livewire;

use App\Models\CheckIn;
use App\Models\Tree;
use App\Models\User;
use App\Services\HabitRecommendationService;
use App\Services\PatternDetectionService;
use App\Services\WellbeingScoringService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public array $moods = [
        'energetic' => [
            'label' => 'Energetic',
            'emoji' => '😄',
            'mascot' => 'maskot-journal2.png',
            'active_class' => 'bg-[#EAF67A] text-navy border-[#A3BA0D] ring-2 ring-lime-400/30 scale-105 shadow-sm font-bold',
            'idle_class' => 'bg-white text-gray-700 border-gray-200/90 hover:bg-lime-50/70 hover:border-lime-300 hover:text-lime-950 shadow-2xs',
            'bg' => 'bg-mood-energetic',
            'text' => 'text-gray-900',
            'border' => 'border-mood-energetic',
        ],
        'calm' => [
            'label' => 'Calm',
            'emoji' => '😌',
            'mascot' => 'maskot-journal3.png',
            'active_class' => 'bg-[#FBCFE8] text-pink-950 border-pink-400 ring-2 ring-pink-400/30 scale-105 shadow-sm font-bold',
            'idle_class' => 'bg-white text-gray-700 border-gray-200/90 hover:bg-pink-50/70 hover:border-pink-300 hover:text-pink-950 shadow-2xs',
            'bg' => 'bg-mood-calm',
            'text' => 'text-white',
            'border' => 'border-mood-calm',
        ],
        'neutral' => [
            'label' => 'Neutral',
            'emoji' => '😐',
            'mascot' => 'maskot-journal3.png',
            'active_class' => 'bg-[#A7F3D0] text-emerald-950 border-emerald-400 ring-2 ring-emerald-400/30 scale-105 shadow-sm font-bold',
            'idle_class' => 'bg-white text-gray-700 border-gray-200/90 hover:bg-emerald-50/70 hover:border-emerald-300 hover:text-emerald-950 shadow-2xs',
            'bg' => 'bg-mood-neutral',
            'text' => 'text-white',
            'border' => 'border-mood-neutral',
        ],
        'stressed' => [
            'label' => 'Stressed',
            'emoji' => '😣',
            'mascot' => 'maskot-journal1.png',
            'active_class' => 'bg-[#FECDD3] text-rose-950 border-rose-400 ring-2 ring-rose-400/30 scale-105 shadow-sm font-bold',
            'idle_class' => 'bg-white text-gray-700 border-gray-200/90 hover:bg-rose-50/70 hover:border-rose-300 hover:text-rose-950 shadow-2xs',
            'bg' => 'bg-mood-stressed',
            'text' => 'text-white',
            'border' => 'border-mood-stressed',
        ],
        'exhausted' => [
            'label' => 'Exhausted',
            'emoji' => '😩',
            'mascot' => 'maskot-journal4.png',
            'active_class' => 'bg-[#BAE6FD] text-cyan-950 border-cyan-400 ring-2 ring-cyan-400/30 scale-105 shadow-sm font-bold',
            'idle_class' => 'bg-white text-gray-700 border-gray-200/90 hover:bg-cyan-50/70 hover:border-cyan-300 hover:text-cyan-950 shadow-2xs',
            'bg' => 'bg-mood-exhausted',
            'text' => 'text-white',
            'border' => 'border-mood-exhausted',
        ],
    ];

    public string $note = '';

    public int $calendarMonth;

    public int $calendarYear;

    public ?string $todayMood = null;

    // Detailed Check-in metrics for Wellbeing Index
    public bool $showDetailedModal = false;

    public bool $showAddHabitModal = false;

    public bool $showHabitUpgradeModal = false;

    public string $customHabitName = '';

    public int $customHabitTarget = 1;

    public string $customHabitUnit = 'kali';

    public float $sleepDuration = 7.5;

    public int $physicalActivityDuration = 45;

    public float $screenTimeDuration = 4.0;

    public int $who5Score = 50;

    public ?CheckIn $todayCheckIn = null;

    public ?string $aiInsight = null;

    public ?string $aiRecommendation = null;

    public string $warningLevel = 'none';

    public function mount(HabitRecommendationService $habitRecommendationService): void
    {
        $this->calendarMonth = today()->month;
        $this->calendarYear = today()->year;
        $this->loadTodayCheckIn();
        $habitRecommendationService->ensureFor(auth()->user());
    }

    public function previousMonth(): void
    {
        $month = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $month->month;
        $this->calendarYear = $month->year;
    }

    public function nextMonth(): void
    {
        $month = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $month->month;
        $this->calendarYear = $month->year;
    }

    public function loadTodayCheckIn(): void
    {
        $user = auth()->user();
        $this->todayCheckIn = $user->checkIns()
            ->whereDate('check_in_date', today())
            ->first();

        if ($this->todayCheckIn) {
            $this->todayMood = $this->todayCheckIn->mood;
            $this->note = $this->todayCheckIn->note ?? '';
            $this->sleepDuration = (float) ($this->todayCheckIn->sleep_duration ?? 7.5);
            $this->physicalActivityDuration = (int) ($this->todayCheckIn->physical_activity_duration ?? 45);
            $this->screenTimeDuration = (float) ($this->todayCheckIn->screen_time_duration ?? 4.0);
            $this->who5Score = (int) ($this->todayCheckIn->who5_score ?? 50);
            $this->aiInsight = $this->todayCheckIn->ai_insight;
        }
    }

    public function checkIn(string $mood): void
    {
        $this->todayMood = $mood;
        $this->saveCheckIn();
    }

    public function submitQuickNote(): void
    {
        $this->note = trim($this->note);
        $this->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        if (! $this->todayMood) {
            $this->todayMood = 'neutral';
        }

        $this->saveCheckIn();

        session()->flash('ai-aurea-prompt', $this->note);
        $this->redirect(route('ai-aurea'), navigate: true);
    }

    public function openDetailedModal(): void
    {
        $this->showDetailedModal = true;
    }

    public function closeDetailedModal(): void
    {
        $this->showDetailedModal = false;
    }

    public function saveDetailedCheckIn(
        WellbeingScoringService $scoringService,
        PatternDetectionService $patternService
    ): void {
        $user = auth()->user();

        $checkIn = $user->checkIns()->updateOrCreate(
            ['check_in_date' => today()],
            [
                'mood' => $this->todayMood ?? 'neutral',
                'note' => $this->note,
                'who5_score' => $this->who5Score,
                'sleep_duration' => $this->sleepDuration,
                'physical_activity_duration' => $this->physicalActivityDuration,
                'screen_time_duration' => $this->screenTimeDuration,
            ]
        );

        // Compute Index & Insights using Services from Nomor 1
        $wellbeingIndex = $scoringService->calculateIndex($checkIn);
        $insightData = $scoringService->generateInsight($checkIn);
        $patternData = $patternService->analyze($user);

        $combinedInsight = $insightData['insight'];
        if (! empty($patternData['patterns'])) {
            $combinedInsight .= ' '.implode(' ', $patternData['patterns']);
        }

        $checkIn->update([
            'wellbeing_index' => $wellbeingIndex,
            'ai_insight' => $combinedInsight,
        ]);

        $this->aiInsight = $combinedInsight;
        $this->aiRecommendation = $insightData['recommendation'];
        $this->warningLevel = $patternData['warning_level'];

        $this->updateStreak($user);
        $this->loadTodayCheckIn();
        $this->showDetailedModal = false;

        session()->flash('message', 'Daily check-in & Well-being Index berhasil diperbarui!');
    }

    protected function saveCheckIn(): void
    {
        $user = auth()->user();

        $checkIn = $user->checkIns()->updateOrCreate(
            ['check_in_date' => today()],
            [
                'mood' => $this->todayMood,
                'note' => $this->note,
                'who5_score' => $this->who5Score,
                'sleep_duration' => $this->sleepDuration,
                'physical_activity_duration' => $this->physicalActivityDuration,
                'screen_time_duration' => $this->screenTimeDuration,
            ]
        );

        $scoringService = app(WellbeingScoringService::class);
        $patternService = app(PatternDetectionService::class);

        $wellbeingIndex = $scoringService->calculateIndex($checkIn);
        $insightData = $scoringService->generateInsight($checkIn);
        $patternData = $patternService->analyze($user);

        $combinedInsight = $insightData['insight'];
        if (! empty($patternData['patterns'])) {
            $combinedInsight .= ' '.implode(' ', $patternData['patterns']);
        }

        $checkIn->update([
            'wellbeing_index' => $wellbeingIndex,
            'ai_insight' => $combinedInsight,
        ]);

        $this->aiInsight = $combinedInsight;
        $this->aiRecommendation = $insightData['recommendation'];
        $this->warningLevel = $patternData['warning_level'];

        $this->updateStreak($user);
        $this->loadTodayCheckIn();
    }

    protected function updateStreak($user): void
    {
        $yesterday = today()->subDay();

        if ($user->last_check_in_date?->isSameDay($yesterday)) {
            $user->current_streak += 1;
        } elseif (! $user->last_check_in_date?->isSameDay(today())) {
            $user->current_streak = 1;
        }

        $user->longest_streak = max($user->longest_streak, $user->current_streak);
        $user->last_check_in_date = today();
        $user->save();
    }

    public function incrementHabit(int $habitId): void
    {
        $habit = auth()->user()->habits()->findOrFail($habitId);
        $log = $habit->logs()->firstOrNew(['log_date' => today()]);

        if ($habit->type === 'checklist' || $habit->target_value <= 1) {
            $log->value_logged = ($log->value_logged ?? 0) >= $habit->target_value ? 0 : $habit->target_value;
        } else {
            $step = max(1, (int) round($habit->target_value / 4));
            $current = $log->value_logged ?? 0;
            $log->value_logged = min($habit->target_value, $current + $step);
        }

        $log->save();
    }

    public function decrementHabit(int $habitId): void
    {
        $habit = auth()->user()->habits()->findOrFail($habitId);
        $log = $habit->logs()->firstOrNew(['log_date' => today()]);

        if ($habit->type === 'checklist' || $habit->target_value <= 1) {
            $log->value_logged = 0;
        } else {
            $step = max(1, (int) round($habit->target_value / 4));
            $current = $log->value_logged ?? 0;
            $log->value_logged = max(0, $current - $step);
        }

        $log->save();
    }

    public function toggleHabit(int $habitId): void
    {
        $habit = auth()->user()->habits()->findOrFail($habitId);
        $log = $habit->logs()->firstOrNew(['log_date' => today()]);

        if ($habit->type === 'checklist' || $habit->target_value <= 1) {
            $log->value_logged = ($log->value_logged ?? 0) >= $habit->target_value ? 0 : $habit->target_value;
        } else {
            $step = max(1, (int) round($habit->target_value / 4));
            $current = $log->value_logged ?? 0;
            if ($current >= $habit->target_value) {
                $log->value_logged = 0;
            } else {
                $log->value_logged = min($habit->target_value, $current + $step);
            }
        }

        $log->save();
    }

    public function openAddHabitModal(): void
    {
        $this->resetValidation();
        $this->customHabitName = '';
        $this->customHabitTarget = 1;
        $this->customHabitUnit = 'kali';

        $user = auth()->user();

        if (! $user->isPremium() && $this->activeProgressHabitCount($user) >= $this->freeHabitLimit()) {
            $this->showAddHabitModal = false;
            $this->showHabitUpgradeModal = true;

            return;
        }

        $this->showHabitUpgradeModal = false;
        $this->showAddHabitModal = true;
    }

    public function closeAddHabitModal(): void
    {
        $this->showAddHabitModal = false;
        $this->resetValidation();
    }

    public function closeHabitUpgradeModal(): void
    {
        $this->showHabitUpgradeModal = false;
    }

    public function addCustomHabit(): void
    {
        $validated = $this->validate([
            'customHabitName' => ['required', 'string', 'max:80'],
            'customHabitTarget' => ['required', 'integer', 'min:1', 'max:10000'],
            'customHabitUnit' => ['required', 'string', 'max:24'],
        ], [
            'customHabitName.required' => 'Nama habit belum diisi.',
            'customHabitName.max' => 'Nama habit maksimal :max karakter.',
            'customHabitTarget.required' => 'Target harian belum diisi.',
            'customHabitTarget.integer' => 'Target harian harus berupa angka.',
            'customHabitTarget.min' => 'Target harian minimal :min.',
            'customHabitTarget.max' => 'Target harian maksimal :max.',
            'customHabitUnit.required' => 'Satuan belum diisi.',
            'customHabitUnit.max' => 'Satuan maksimal :max karakter.',
        ]);

        $freeHabitLimit = $this->freeHabitLimit();
        $created = DB::transaction(function () use ($validated, $freeHabitLimit): bool {
            $user = User::query()->lockForUpdate()->findOrFail(auth()->id());

            if (! $user->isPremium() && $this->activeProgressHabitCount($user) >= $freeHabitLimit) {
                return false;
            }

            $user->habits()->create([
                'name' => trim($validated['customHabitName']),
                'type' => 'progress',
                'target_value' => $validated['customHabitTarget'],
                'unit' => trim($validated['customHabitUnit']),
                'source' => 'user',
                'recommendation_key' => null,
                'is_active' => true,
            ]);

            return true;
        });

        if (! $created) {
            $this->showAddHabitModal = false;
            $this->showHabitUpgradeModal = true;

            return;
        }

        $this->showHabitUpgradeModal = false;
        $this->closeAddHabitModal();
        session()->flash('habit-message', 'Habit personal berhasil ditambahkan.');
    }

    public function removeCustomHabit(int $habitId): void
    {
        $habit = auth()->user()->habits()
            ->where('type', 'progress')
            ->where('source', 'user')
            ->findOrFail($habitId);

        $habit->update(['is_active' => false]);
        session()->flash('habit-message', 'Habit personal dinonaktifkan.');
    }

    protected function refreshTreeGrowth(): void
    {
        $user = auth()->user();
        $tree = $user->tree()->firstOrCreate([], ['growth_percentage' => 30]);
        $tree->refreshGrowthPercentage();
    }

    protected function activeProgressHabitCount(User $user): int
    {
        return $user->habits()
            ->where('is_active', true)
            ->where('type', 'progress')
            ->count();
    }

    protected function freeHabitLimit(): int
    {
        return (int) config('premium.free_limits.max_habits', 3);
    }

    public function getCalendarDays(): array
    {
        $user = auth()->user();
        $days = [];

        $monthStart = Carbon::create($this->calendarYear, $this->calendarMonth, 1);
        $startDate = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $endDate = $startDate->copy()->addDays(41);

        $checkIns = $user->checkIns()
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->get(['check_in_date', 'mood'])
            ->keyBy(fn ($c) => $c->check_in_date->format('Y-m-d'));

        for ($i = 0; $i < 42; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $checkIn = $checkIns->get($dateStr);

            $days[] = [
                'date' => $date,
                'is_today' => $date->isToday(),
                'is_future' => $date->isFuture(),
                'is_current_month' => $date->month === $this->calendarMonth,
                'mood' => $checkIn?->mood,
                'color_class' => match ($checkIn?->mood) {
                    'energetic' => 'bg-mood-energetic',
                    'calm' => 'bg-mood-calm',
                    'neutral' => 'bg-mood-neutral',
                    'stressed' => 'bg-mood-stressed',
                    'exhausted' => 'bg-mood-exhausted',
                    default => $date->isFuture() ? 'bg-gray-50 border border-dashed border-gray-200' : 'bg-white border border-gray-200',
                },
            ];
        }

        return $days;
    }

    public function render()
    {
        $user = auth()->user();
        $isPremium = $user->isPremium();
        $freeHabitLimit = $this->freeHabitLimit();

        // Single query for all active habits with today's logs
        $allActiveHabits = $user->habits()
            ->where('is_active', true)
            ->with(['logs' => fn ($q) => $q->whereDate('log_date', today())])
            ->get();

        $checklistHabits = $allActiveHabits
            ->where('type', 'checklist')
            ->where('source', 'system')
            ->values();

        $progressHabitsCollection = $allActiveHabits
            ->where('type', 'progress')
            ->whereIn('source', ['system', 'user'])
            ->sortBy(fn ($h) => $h->source.'_'.$h->id)
            ->values();

        $progressHabits = $isPremium ? $progressHabitsCollection : $progressHabitsCollection->take($freeHabitLimit);

        $tree = $user->tree()->firstOrCreate([], ['growth_percentage' => 30]);
        $tree->setRelation('user', $user);
        $tree->refreshGrowthPercentage($allActiveHabits);
        $completedGrowthItemsCount = $tree->completedGrowthItemsCount();

        return view('livewire.dashboard', [
            'moods' => $this->moods,
            'todayMood' => $this->todayMood,
            'checklistHabits' => $checklistHabits,
            'progressHabits' => $progressHabits,
            'isPremium' => $isPremium,
            'freeHabitLimit' => $freeHabitLimit,
            'activeHabitCount' => $progressHabits->count(),
            'tree' => $tree,
            'completedGrowthItemsCount' => $completedGrowthItemsCount,
            'treeStage' => Tree::growthStageForCompletedItems($completedGrowthItemsCount),
            'calendarDays' => $this->getCalendarDays(),
            'calendarTitle' => Carbon::create($this->calendarYear, $this->calendarMonth, 1)->format('F Y'),
        ]);
    }
}
