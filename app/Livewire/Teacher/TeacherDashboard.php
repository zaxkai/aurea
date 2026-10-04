<?php

namespace App\Livewire\Teacher;

use App\Models\CheckIn;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TeacherDashboard extends Component
{
    public ?int $selectedClassroomId = null;

    public bool $showCreateModal = false;

    public string $newClassName = '';

    public string $newSchoolName = '';

    public string $newClassDescription = '';

    public function createClassroom(): void
    {
        $this->validate([
            'newClassName' => 'required|string|max:100',
            'newSchoolName' => 'nullable|string|max:100',
            'newClassDescription' => 'nullable|string|max:255',
        ]);

        Classroom::create([
            'teacher_id' => auth()->id(),
            'name' => $this->newClassName,
            'school_name' => $this->newSchoolName,
            'code' => Classroom::generateUniqueCode(),
            'description' => $this->newClassDescription,
        ]);

        $this->reset(['newClassName', 'newSchoolName', 'newClassDescription', 'showCreateModal']);
        session()->flash('success', 'Classroom successfully created!');
    }

    public function render()
    {
        $teacher = auth()->user();

        // 1. All classrooms owned by this teacher
        $classrooms = $teacher->taughtClassrooms()->withCount('students')->get();

        // 2. Determine target classroom IDs based on filter
        $targetClassroomIds = $this->selectedClassroomId
            ? $classrooms->where('id', $this->selectedClassroomId)->pluck('id')
            : $classrooms->pluck('id');

        // 3. Strictly scoped student IDs belonging to the teacher's classrooms
        $studentIds = DB::table('classroom_student')
            ->whereIn('classroom_id', $targetClassroomIds)
            ->pluck('student_id')
            ->unique()
            ->values();

        $studentsCount = $studentIds->count();

        // 4. Wellbeing Score (average wellbeing_index from last 30 days)
        $avgWellbeing = CheckIn::whereIn('user_id', $studentIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('wellbeing_index');
        $wellbeingScore = $avgWellbeing !== null ? (int) round($avgWellbeing) : 70;

        // 5. Need Attention calculation
        // Students with recent low wellbeing (< 60) or multiple stressed/exhausted moods in last 7 days
        $needAttentionCount = 0;
        if ($studentsCount > 0) {
            $attentionStudents = CheckIn::whereIn('user_id', $studentIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->where(function ($query) {
                    $query->where('wellbeing_index', '<', 60)
                        ->orWhereIn('mood', ['stressed', 'exhausted']);
                })
                ->distinct('user_id')
                ->count('user_id');

            $needAttentionCount = $attentionStudents;
        }

        // 6. Mood Overview (Weekly percentages)
        $weeklyCheckins = CheckIn::whereIn('user_id', $studentIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        $totalCheckins = $weeklyCheckins->count();

        $moodStats = [
            'calm' => ['label' => 'Calm', 'emoji' => '😌', 'pct' => 0, 'count' => 0],
            'neutral' => ['label' => 'Neutral', 'emoji' => '😐', 'pct' => 0, 'count' => 0],
            'stressed' => ['label' => 'Stressed', 'emoji' => '😰', 'pct' => 0, 'count' => 0],
            'exhausted' => ['label' => 'Exhausted', 'emoji' => '😫', 'pct' => 0, 'count' => 0],
        ];

        if ($totalCheckins > 0) {
            foreach ($moodStats as $key => &$stat) {
                $stat['count'] = $weeklyCheckins->where('mood', $key)->count();
                $stat['pct'] = (int) round(($stat['count'] / $totalCheckins) * 100);
            }
            unset($stat);
        } else {
            // Default placeholder representation if no checkins yet
            $moodStats['calm']['pct'] = 28;
            $moodStats['neutral']['pct'] = 42;
            $moodStats['stressed']['pct'] = 19;
            $moodStats['exhausted']['pct'] = 11;
        }

        return view('livewire.teacher.teacher-dashboard', [
            'classrooms' => $classrooms,
            'studentsCount' => $studentsCount,
            'wellbeingScore' => $wellbeingScore,
            'needAttentionCount' => $needAttentionCount,
            'moodStats' => $moodStats,
            'totalCheckins' => $totalCheckins,
        ]);
    }
}
