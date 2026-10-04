<?php

namespace App\Livewire\Teacher;

use App\Models\CheckIn;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StudentWellbeing extends Component
{
    public string $search = '';

    public ?int $selectedClassroomId = null;

    public string $statusFilter = 'all'; // all, attention, healthy

    public function render()
    {
        $teacher = auth()->user();
        $classrooms = $teacher->taughtClassrooms()->get();

        $targetClassroomIds = $this->selectedClassroomId
            ? $classrooms->where('id', $this->selectedClassroomId)->pluck('id')
            : $classrooms->pluck('id');

        // Retrieve pivot records with classroom info
        $pivotQuery = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->whereIn('classroom_student.classroom_id', $targetClassroomIds)
            ->select('classroom_student.student_id', 'classrooms.name as classroom_name', 'classroom_student.joined_at');

        $pivots = $pivotQuery->get()->groupBy('student_id');
        $studentIds = $pivots->keys();

        // Fetch students strictly within the teacher's classrooms
        $studentsQuery = User::whereIn('id', $studentIds);

        if (! empty($this->search)) {
            $studentsQuery->where(function ($q) {
                $q->where('name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('first_name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('email', 'ilike', '%'.$this->search.'%');
            });
        }

        $students = $studentsQuery->get()->map(function ($student) use ($pivots) {
            $classroomInfo = $pivots->get($student->id)?->first();

            // Latest checkin for this student
            $latestCheckin = CheckIn::where('user_id', $student->id)
                ->latest('created_at')
                ->first();

            // Average wellbeing index over the past 30 days
            $avgWellbeing = CheckIn::where('user_id', $student->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->avg('wellbeing_index');

            $score = $avgWellbeing !== null ? (int) round($avgWellbeing) : ($latestCheckin?->wellbeing_index ?? 75);

            // Need attention if score < 60 or latest mood is stressed/exhausted
            $needsAttention = ($score < 60) || in_array($latestCheckin?->mood, ['stressed', 'exhausted']);

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'avatar_url' => $student->avatar_url,
                'classroom_name' => $classroomInfo->classroom_name ?? 'N/A',
                'joined_at' => $classroomInfo->joined_at ?? $student->created_at,
                'latest_mood' => $latestCheckin?->mood ?? 'none',
                'latest_mood_emoji' => match ($latestCheckin?->mood) {
                    'calm' => '😌 Calm',
                    'neutral' => '😐 Neutral',
                    'energetic' => '😄 Energetic',
                    'stressed' => '😰 Stressed',
                    'exhausted' => '😫 Exhausted',
                    default => '— No check-in yet',
                },
                'wellbeing_score' => $score,
                'needs_attention' => $needsAttention,
                'last_active' => $latestCheckin?->created_at?->diffForHumans() ?? 'No activity yet',
            ];
        });

        if ($this->statusFilter === 'attention') {
            $students = $students->filter(fn ($s) => $s['needs_attention']);
        } elseif ($this->statusFilter === 'healthy') {
            $students = $students->filter(fn ($s) => ! $s['needs_attention']);
        }

        return view('livewire.teacher.student-wellbeing', [
            'classrooms' => $classrooms,
            'students' => $students,
        ]);
    }
}
