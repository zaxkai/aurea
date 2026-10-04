<div class="space-y-8 max-w-6xl mx-auto pb-12 pt-2">
    <!-- Header with Title, Classroom Filter, & Create Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#000F2E] tracking-tight">
                Hello, {{ auth()->user()->first_name ?? 'Teacher' }}
            </h1>
            <p class="text-gray-500 font-medium text-sm mt-1">
                See how students are feeling at a glance
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if ($classrooms->isNotEmpty())
                <div class="relative">
                    <select wire:model.live="selectedClassroomId"
                            class="bg-white border border-gray-200 text-[#000F2E] font-semibold text-xs sm:text-sm rounded-2xl py-2.5 px-4 pr-9 focus:ring-2 focus:ring-[#000F2E] focus:outline-none shadow-sm cursor-pointer">
                        <option value="">All Classrooms ({{ $classrooms->sum('students_count') }} Students)</option>
                        @foreach ($classrooms as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }} ({{ $cls->students_count }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="button"
                    wire:click="$set('showCreateModal', true)"
                    class="inline-flex items-center gap-2 bg-[#000F2E] text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-2xl shadow-md hover:bg-[#001744] active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>New Classroom</span>
            </button>
        </div>
    </div>

    <!-- Active Classrooms Banner / Code Sharing (if exists) -->
    @if ($classrooms->isNotEmpty())
        <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                    🏫
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[#000F2E]">Your Classroom Codes for Students</h3>
                    <p class="text-xs text-gray-500">Share these codes with your students so they can connect with your dashboard safely.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2" x-data="{ copiedId: null }">
                @foreach ($classrooms as $cls)
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $cls->code }}'); copiedId = {{ $cls->id }}; setTimeout(() => copiedId = null, 2000)"
                            class="inline-flex items-center gap-2 bg-white border border-blue-200/80 hover:border-blue-400 px-3 py-1.5 rounded-xl shadow-xs text-xs font-semibold text-[#000F2E] transition-all">
                        <span>{{ $cls->name }}:</span>
                        <span class="font-mono font-bold tracking-wider text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">{{ $cls->code }}</span>
                        <span x-show="copiedId === {{ $cls->id }}" x-cloak class="text-emerald-600 font-bold text-[11px]">Copied!</span>
                        <svg x-show="copiedId !== {{ $cls->id }}" class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Top 3 Stat Cards (Matching the user's Mockup) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Students Card -->
        <div class="bg-[#000F2E] text-white rounded-[28px] p-7 flex flex-col items-center justify-center min-h-[190px] shadow-lg shadow-navy/10 relative overflow-hidden group">
            <div class="flex items-center gap-3.5">
                <svg class="w-10 h-10 text-[#4CC9F0]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
                <span class="text-5xl font-black tracking-tight">{{ $studentsCount }}</span>
            </div>
            <div class="text-sm font-semibold text-gray-300 mt-3 tracking-wide">
                Students
            </div>
        </div>

        <!-- Wellbeing Score Card -->
        <div class="bg-[#000F2E] text-white rounded-[28px] p-7 flex flex-col items-center justify-center min-h-[190px] shadow-lg shadow-navy/10 relative overflow-hidden group">
            <div class="text-5xl font-black tracking-tight">
                {{ $wellbeingScore }}%
            </div>
            <div class="text-sm font-semibold text-gray-300 mt-3 tracking-wide">
                Wellbeing Score
            </div>
        </div>

        <!-- Need Attention Card -->
        <div class="bg-[#000F2E] text-white rounded-[28px] p-7 flex flex-col items-center justify-center min-h-[190px] shadow-lg shadow-navy/10 relative overflow-hidden group">
            <div class="flex items-center gap-3.5">
                <svg class="w-9 h-9 text-[#F4B400]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                </svg>
                <span class="text-5xl font-black tracking-tight">{{ $needAttentionCount }}</span>
            </div>
            <div class="text-sm font-semibold text-gray-300 mt-3 tracking-wide">
                Need Attention
            </div>
        </div>
    </div>

    <!-- Mood Overview Section (Matching Mockup) -->
    <div class="space-y-4">
        <div>
            <h2 class="text-2xl font-black text-[#000F2E] tracking-tight">Mood Overview</h2>
            <p class="text-gray-500 font-medium text-xs sm:text-sm">An overview of students' reported moods this week</p>
        </div>

        <div class="bg-[#e4ebfc] rounded-[28px] p-6 sm:p-8 space-y-4 shadow-sm">
            @foreach ($moodStats as $key => $mood)
                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- Mood Label with Emoji -->
                    <div class="flex items-center gap-2.5 w-24 sm:w-28 shrink-0">
                        <span class="text-xl sm:text-2xl select-none">{{ $mood['emoji'] }}</span>
                        <span class="font-bold text-sm sm:text-base text-[#000F2E]">{{ $mood['label'] }}</span>
                    </div>

                    <!-- Progress Bar Container -->
                    <div class="flex-1 bg-white h-7 sm:h-8 rounded-full overflow-hidden p-1 shadow-inner relative">
                        <div class="bg-[#000F2E] h-full rounded-full transition-all duration-700 ease-out"
                             style="width: {{ max(4, $mood['pct']) }}%"></div>
                    </div>

                    <!-- Percentage -->
                    <div class="w-12 sm:w-14 text-right font-bold text-sm sm:text-base text-[#000F2E] shrink-0">
                        {{ $mood['pct'] }}%
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal: Create New Classroom -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/50 backdrop-blur-xs"
             x-data
             @keydown.escape.window="$wire.set('showCreateModal', false)">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-6 relative border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-[#000F2E]">Create New Classroom</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Students will use the generated code to join.</p>
                    </div>
                    <button type="button"
                            wire:click="$set('showCreateModal', false)"
                            class="w-8 h-8 rounded-full text-gray-400 hover:text-navy hover:bg-gray-100 flex items-center justify-center transition-colors">
                        ✕
                    </button>
                </div>

                <form wire:submit="createClassroom" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Classroom Name *</label>
                        <input type="text"
                               wire:model="newClassName"
                               placeholder="e.g. Kelas 10 IPA 1 / Class 10-A"
                               required
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]" />
                        @error('newClassName') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">School Name (Optional)</label>
                        <input type="text"
                               wire:model="newSchoolName"
                               placeholder="e.g. SMA Negeri 1 Jakarta"
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]" />
                        @error('newSchoolName') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Description (Optional)</label>
                        <textarea wire:model="newClassDescription"
                                  rows="2"
                                  placeholder="Notes for this classroom..."
                                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-medium text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                                wire:click="$set('showCreateModal', false)"
                                class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#000F2E] text-white hover:bg-[#001b3d] shadow-md transition-all">
                            Generate Classroom & Code
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
