<?php

use App\Models\OnboardingQuestion;
use App\Models\OnboardingAnswer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.empty')] class extends Component
{
    public int $step = 0;
    public $questions;
    public array $answers = [];

    public function mount()
    {
        if (Auth::user()->onboarding_completed_at) {
            $this->redirect(route('dashboard'));
        }

        $this->questions = OnboardingQuestion::with('options')->orderBy('order')->get();

        if ($this->questions->isEmpty()) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'OnboardingSeeder']);
            $this->questions = OnboardingQuestion::with('options')->orderBy('order')->get();
        }
    }

    public function next()
    {
        if ($this->step == 0) {
            $this->step = 1;
        } elseif ($this->step <= $this->questions->count()) {
            $currentQuestion = $this->questions[$this->step - 1];
            if (!isset($this->answers[$currentQuestion->id])) {
                return;
            }

            if ($this->step == $this->questions->count()) {
                foreach ($this->answers as $questionId => $optionId) {
                    OnboardingAnswer::updateOrCreate(
                        ['user_id' => Auth::id(), 'question_id' => $questionId],
                        ['option_id' => $optionId]
                    );
                }
                $user = Auth::user();
                $user->onboarding_completed_at = now();
                $user->save();

                $this->redirect(route('dashboard'), navigate: true);
            } else {
                $this->step++;
            }
        }
    }

    public function prev()
    {
        if ($this->step > 0) {
            $this->step--;
        }
    }

    public function selectOption($questionId, $optionId)
    {
        $this->answers[$questionId] = $optionId;
    }
}; ?>

<div class="min-h-dvh w-full bg-[#F1F3F8] flex md:h-dvh md:min-h-0" wire:key="step-{{ $step }}">
    {{-- Main Card --}}
    <div class="bg-[#F1F3F8] w-full min-h-dvh flex flex-col relative md:h-dvh md:min-h-0 md:overflow-hidden">

        @if ($step === 0)
            {{-- ============ INTRO SCREEN ============ --}}
            {{-- Logo top-right --}}
            <div class="absolute right-4 top-4 z-10 sm:right-8 sm:top-6">
                <div class="flex items-center space-x-1.5">
                    <img src="{{ asset('images/logo_aurea.png') }}" alt="Aurea logo" class="w-8 h-8 object-cover rounded-full" />
                    <span class="text-xl font-bold text-gray-900 tracking-tight lowercase" style="font-family: 'Outfit', sans-serif;">aurea</span>
                </div>
            </div>

            {{-- Center content --}}
            <div class="flex flex-1 flex-col items-center justify-center px-5 py-20 sm:px-8 md:py-0">
                {{-- Speech Bubble --}}
                <div class="relative mb-6 max-w-full">
                    <div class="rounded-2xl bg-white px-4 py-3.5 shadow-[0_3px_8px_rgba(15,23,42,0.08)] sm:px-7">
                        <p class="text-center text-base font-semibold text-gray-900 sm:text-lg">Let's set up your profile!</p>
                    </div>
                    {{-- Bubble tail --}}
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-white border-r border-b border-gray-200 rotate-45"></div>
                </div>

                {{-- Mascot --}}
                <div class="mb-6 h-36 w-36 sm:mb-7 sm:h-44 sm:w-44">
                    <img src="{{ asset('images/maskot_aurea.png') }}" alt="Aurea mascot" class="w-full h-full object-contain drop-shadow-[0_8px_20px_rgba(30,168,170,0.22)]" />
                </div>

                {{-- Next Button --}}
                <button wire:click="next" class="rounded-full bg-[#001033] px-16 py-3 text-sm font-medium text-white transition-colors hover:bg-[#162040]">
                    Next
                </button>
            </div>

        @else
            {{-- ============ QUESTION SCREEN ============ --}}
            @php $currentQuestion = $questions[$step - 1]; @endphp

            {{-- Top bar: dots + logo --}}
            <div class="z-10 flex shrink-0 items-center justify-between bg-white px-4 py-3 sm:px-5 sm:py-4">
                {{-- Progress dots --}}
                <div class="flex items-center gap-2">
                    @php
                        $activeProgressStep = min(1 + (int) floor(($step - 1) * 2 / max($questions->count(), 1)), 2);
                    @endphp
                    @for ($i = 0; $i < 3; $i++)
                        <div class="size-3 rounded-full transition-colors duration-300
                            @if ($i < $activeProgressStep) bg-[#29D5D7]
                            @elseif ($i === $activeProgressStep) bg-[#001033]
                            @else bg-[#B6BBC7] @endif
                        "></div>
                    @endfor
                </div>

                {{-- Logo --}}
                <div class="flex items-center space-x-1.5">
                    <img src="{{ asset('images/logo_aurea.png') }}" alt="Aurea logo" class="w-6 h-6 object-cover rounded-full" />
                    <span class="text-lg font-bold text-gray-900 tracking-tight lowercase" style="font-family: 'Outfit', sans-serif;">aurea</span>
                </div>
            </div>

            {{-- Two-panel layout --}}
            <div class="flex flex-col md:flex-1 md:min-h-0 md:flex-row md:overflow-hidden">
                {{-- Left Panel: mascot + question --}}
                <div class="relative flex min-h-[250px] w-full flex-none flex-col items-center justify-center bg-[#F1F3F8] px-4 pb-6 pt-12 sm:min-h-[280px] md:min-h-0 md:w-[45%] md:flex-auto md:px-8 md:py-0">
                    {{-- Back button --}}
                    @if($step > 1)
                        <button wire:click="prev" class="absolute left-4 top-4 flex items-center text-sm font-medium text-gray-400 transition-colors hover:text-gray-700 sm:left-6 sm:top-5">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Back
                        </button>
                    @endif

                    {{-- Speech Bubble --}}
                    <div class="relative mb-3 max-w-full">
                        <div class="w-full max-w-[260px] rounded-2xl bg-white px-4 py-2.5 shadow-[0_3px_8px_rgba(15,23,42,0.08)] sm:px-6 sm:py-4">
                            <p class="text-center text-base font-semibold leading-snug text-gray-900">{{ $currentQuestion->question }}</p>
                        </div>
                        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-white rotate-45 shadow-sm"></div>
                    </div>

                    {{-- Mascot --}}
                    <div class="h-24 w-24 sm:h-28 sm:w-28 md:h-40 md:w-40">
                        <img src="{{ asset('images/maskot_aurea.png') }}" alt="Aurea mascot" class="w-full h-full object-contain" />
                    </div>
                </div>

                {{-- Right Panel: options --}}
                <div class="relative flex w-full flex-col justify-center bg-[#001033] px-5 py-7 md:min-h-0 md:w-[55%] md:overflow-y-auto md:rounded-l-[12px] md:px-10 md:py-8 lg:px-14">
                    <div class="space-y-2.5 w-full max-w-md mx-auto">
                        @foreach($currentQuestion->options as $option)
                            @php $isSelected = ($answers[$currentQuestion->id] ?? null) == $option->id; @endphp
                            <button wire:click="selectOption({{ $currentQuestion->id }}, {{ $option->id }})"
                                    wire:key="option-{{ $option->id }}"
                                    class="w-full text-center py-2.5 px-5 rounded-xl font-medium text-[13px] transition-all duration-200
                                    @if($isSelected) bg-white text-[#001033] shadow-lg scale-[1.02]
                                    @else bg-[#303C5B] text-white/95 hover:bg-[#3B496C] @endif">
                                {{ $option->label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Next / Finish button --}}
                    <div class="mx-auto mt-5 flex w-full max-w-md justify-end md:mt-4">
                        <button wire:click="next"
                                @if(!isset($answers[$currentQuestion->id])) disabled @endif
                                class="rounded-xl bg-[#29D5D7] px-8 py-2.5 text-sm font-medium text-[#001033] transition-all duration-200 hover:bg-[#45dfe0] disabled:cursor-not-allowed disabled:opacity-40">
                            @if($step == $questions->count())
                                Finish
                            @else
                                Next
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
