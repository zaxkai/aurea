<?php

namespace App\Livewire\Teacher;

use App\Models\CheckIn;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class StudentWellbeing extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public ?int $selectedClassroomId = null;

    #[Url]
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
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $studentsQuery->where(function ($q) use ($like) {
                $q->where('name', $like, '%'.$this->search.'%')
                    ->orWhere('first_name', $like, '%'.$this->search.'%')
                    ->orWhere('email', $like, '%'.$this->search.'%');
            });
        }

        $studentList = $studentsQuery->get();
        $fetchedStudentIds = $studentList->pluck('id');

        // Batch pre-fetch latest check-ins to eliminate N+1 queries
        $latestCheckins = CheckIn::whereIn('user_id', $fetchedStudentIds)
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        // Batch pre-fetch 30-day average wellbeing scores
        $avgWellbeings = CheckIn::whereIn('user_id', $fetchedStudentIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('user_id')
            ->select('user_id', DB::raw('AVG(wellbeing_index) as avg_score'))
            ->pluck('avg_score', 'user_id');

        $students = $studentList->map(function ($student) use ($pivots, $latestCheckins, $avgWellbeings) {
            $classroomInfo = $pivots->get($student->id)?->first();

            $latestCheckin = $latestCheckins->get($student->id);
            $avgWellbeing = $avgWellbeings->get($student->id);

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
