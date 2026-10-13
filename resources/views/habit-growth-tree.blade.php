<x-app-layout>
    @php
        $treeStages = [
            ['name' => 'Seed', 'description' => 'A small step can start massive growth.'],
            ['name' => 'Sprout', 'description' => 'The habit of completing tasks begins to take root.'],
            ['name' => 'Sapling', 'description' => 'Your consistency starts growing new branches.'],
            ['name' => 'Blooming', 'description' => 'Your dedication shows in a thriving tree.'],
            ['name' => 'Lush', 'description' => 'See the results of all the goals you have accomplished.'],
        ];
        $currentStage = $treeStages[$treeStage];
        $stageMilestones = [0, 1, 3, 6, 10];
        $nextStageTaskCount = $stageMilestones[$treeStage + 1] ?? null;
        $tasksUntilNextStage = $nextStageTaskCount === null
            ? 0
            : max(0, $nextStageTaskCount - $completedGrowthItemsCount);
        $stageProgress = $nextStageTaskCount === null
            ? 100
            : (int) round((($completedGrowthItemsCount - $stageMilestones[$treeStage]) / ($nextStageTaskCount - $stageMilestones[$treeStage])) * 100);
    @endphp

    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-navy tracking-tight">Habit Growth Tree</h1>
        <p class="mt-1 text-sm text-gray-500">Every habit you complete today and every finished task helps your tree grow and flourish.</p>
    </div>

    <div class="grid max-w-5xl grid-cols-1 gap-7 md:grid-cols-2">
        <section class="flex flex-col items-center justify-center rounded-3xl border border-gray-100 bg-white p-6 text-center shadow-sm sm:p-8">
            <div class="flex flex-wrap items-center justify-center gap-3">
                <h2 class="text-lg font-bold text-navy">Personal Resilience Tree</h2>
                <span class="rounded-full bg-aurea/20 px-3 py-1 text-xs font-bold text-navy">{{ $currentStage['name'] }}</span>
            </div>

            <x-growth-tree :stage="$treeStage" />

            <p class="mt-2 text-sm text-gray-500">{{ $currentStage['description'] }}</p>
            <p class="mt-4 text-2xl font-extrabold text-navy">{{ $completedGrowthItemsCount }} <span class="text-sm font-semibold text-gray-500">targets completed</span></p>

            @if ($nextStageTaskCount !== null)
                <div class="mt-4 w-full max-w-sm">
                    <div class="mb-2 flex items-center justify-between gap-3 text-xs font-semibold text-gray-500">
                        <span>{{ $tasksUntilNextStage }} {{ \Illuminate\Support\Str::plural('task', $tasksUntilNextStage) }} to {{ $treeStages[$treeStage + 1]['name'] }}</span>
                        <span>{{ $stageProgress }}%</span>
                    </div>
                    <div
                        class="h-2.5 overflow-hidden rounded-full bg-gray-100"
                        role="progressbar"
                        aria-label="Progress to next tree stage"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $stageProgress }}"
                    >
                        <div class="h-full rounded-full bg-aurea transition-all duration-500" style="width: {{ $stageProgress }}%"></div>
                    </div>
                </div>
            @else
                <p class="mt-4 rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">Your tree is flourishing. Keep up your consistency!</p>
            @endif
        </section>

        <section class="flex flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
            <div>
                <h2 class="mb-2 text-lg font-bold text-navy">Growth Journey</h2>
                <p class="mb-5 text-sm text-gray-500">Every completed daily habit and finished task unlocks a new stage in your tree's journey.</p>

                <ol class="space-y-3">
                    @foreach ($treeStages as $index => $stage)
                        <li class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $index <= $treeStage ? 'border-aurea/50 bg-aurea/10' : 'border-gray-100 bg-gray-50' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $index <= $treeStage ? 'bg-navy text-white' : 'bg-white text-gray-400' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-navy">{{ $stage['name'] }}</span>
                                <span class="block text-xs text-gray-500">
                                    @if ($index === 0)
                                        Start here
                                    @else
                                        {{ $stageMilestones[$index] }} targets completed
                                    @endif
                                </span>
                            </span>
                            @if ($index <= $treeStage)
                                <span class="text-xs font-semibold text-emerald-700">Achieved</span>
                            @endif
                        </li>
                    @endforeach
                </ol>

                <h3 class="mb-3 mt-7 text-sm font-bold text-navy">Active Habits</h3>
                <div class="space-y-3">
                    @forelse (auth()->user()->habits as $habit)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-3.5">
                            <span class="text-xs font-bold text-navy">{{ $habit->name }}</span>
                            <span class="shrink-0 rounded-full bg-navy px-2.5 py-1 text-xs font-semibold text-white">{{ $habit->target_value }} {{ $habit->unit }}</span>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-gray-50 p-4 text-sm text-gray-500">No active habits yet.</p>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('dashboard') }}" class="mt-6 w-full rounded-full bg-navy py-3 text-center text-xs font-bold text-white transition hover:bg-navy/90">
                Back to Dashboard
            </a>
        </section>
    </div>
</x-app-layout>
