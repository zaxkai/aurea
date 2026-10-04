<div class="space-y-6 max-w-6xl mx-auto pb-12 pt-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-[#000F2E] tracking-tight">Student Wellbeing</h1>
            <p class="text-gray-500 font-medium text-sm mt-1">
                Monitor and support students in your enrolled classrooms
            </p>
        </div>

        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 text-xs font-bold text-gray-600 hover:text-[#000F2E] bg-white border border-gray-200 px-4 py-2.5 rounded-2xl shadow-xs transition-colors self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <!-- Privacy Banner -->
    <div class="bg-emerald-50/70 border border-emerald-200/60 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shrink-0 text-sm">
            🛡️
        </div>
        <div class="text-xs text-emerald-900 leading-relaxed">
            <span class="font-bold">Student Privacy Boundary Active:</span>
            As a teacher, you have access to student emotional check-in trends and aggregated wellbeing scores for guidance. Personal journal entries and AI chat conversations are strictly confidential and inaccessible to protect student trust.
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <div class="flex-1 max-w-md relative">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search student by name or email..."
                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-xs sm:text-sm font-medium text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]" />
        </div>

        <div class="flex items-center gap-2">
            <!-- Classroom filter -->
            <select wire:model.live="selectedClassroomId"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]">
                <option value="">All Classrooms</option>
                @foreach ($classrooms as $cls)
                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                @endforeach
            </select>

            <!-- Status filter -->
            <select wire:model.live="statusFilter"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-[#000F2E] focus:outline-none focus:ring-2 focus:ring-[#000F2E]">
                <option value="all">All Status</option>
                <option value="attention">⚠️ Need Attention</option>
                <option value="healthy">🌱 Healthy / On Track</option>
            </select>
        </div>
    </div>

    <!-- Students Table / List -->
    <div class="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm">
        @if ($students->isEmpty())
            <div class="py-16 text-center space-y-3">
                <div class="text-4xl">📚</div>
                <h3 class="text-base font-bold text-[#000F2E]">No Students Found</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    @if ($classrooms->isEmpty())
                        You have not created any classrooms yet. Create a classroom and share the code with students.
                    @else
                        No students match your filter or have joined this classroom yet.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3.5 px-6">Student</th>
                            <th class="py-3.5 px-4">Classroom</th>
                            <th class="py-3.5 px-4">Latest Mood</th>
                            <th class="py-3.5 px-4">Wellbeing Score</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-6 text-right">Last Check-in</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($students as $student)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-6 flex items-center gap-3">
                                    <img src="{{ $student['avatar_url'] }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0 ring-1 ring-gray-100">
                                    <div>
                                        <div class="font-bold text-[#000F2E]">{{ $student['name'] }}</div>
                                        <div class="text-[11px] text-gray-400 font-medium">{{ $student['email'] }}</div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-semibold text-gray-700">
                                    <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-lg text-xs font-bold">
                                        {{ $student['classroom_name'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-[#000F2E]">
                                    {{ $student['latest_mood_emoji'] }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-gray-100 h-2 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $student['wellbeing_score'] < 60 ? 'bg-amber-500' : 'bg-emerald-500' }}"
                                                 style="width: {{ $student['wellbeing_score'] }}%"></div>
                                        </div>
                                        <span class="font-bold text-xs {{ $student['wellbeing_score'] < 60 ? 'text-amber-600' : 'text-[#000F2E]' }}">
                                            {{ $student['wellbeing_score'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if ($student['needs_attention'])
                                        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200/80 px-2.5 py-1 rounded-xl text-[11px] font-bold">
                                            <span>⚠️</span>
                                            <span>Need Attention</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2.5 py-1 rounded-xl text-[11px] font-bold">
                                            <span>🌱</span>
                                            <span>Healthy</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right text-gray-400 font-medium text-xs">
                                    {{ $student['last_active'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
