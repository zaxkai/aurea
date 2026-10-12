<?php

namespace App\Console\Commands;

use App\Models\CheckIn;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendWeeklyTeacherSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurea:send-weekly-teacher-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly wellbeing summary notifications to teachers with enrolled students';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $teachers = User::where('role', 'teacher')->get();
        $sentCount = 0;

        foreach ($teachers as $teacher) {
            $classroomIds = $teacher->taughtClassrooms()->pluck('id');
            if ($classroomIds->isEmpty()) {
                continue;
            }

            $studentIds = DB::table('classroom_student')
                ->whereIn('classroom_id', $classroomIds)
                ->pluck('student_id')
                ->unique();

            $totalStudents = $studentIds->count();
            if ($totalStudents === 0) {
                continue;
            }

            // Average wellbeing index of enrolled students over the last 7 days
            $avgWellbeing = CheckIn::whereIn('user_id', $studentIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->avg('wellbeing_index');

            $avgScore = $avgWellbeing !== null ? (int) round($avgWellbeing) : 70;

            // Students needing attention
            $attentionStudentIds = CheckIn::whereIn('user_id', $studentIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->where(function ($query) {
                    $query->where('wellbeing_index', '<', 60)
                        ->orWhereIn('mood', ['stressed', 'exhausted']);
                })
                ->pluck('user_id')
                ->unique();

            $attentionCount = $attentionStudentIds->count();
            $healthyCount = max(0, $totalStudents - $attentionCount);

            $message = "Minggu ini: Rata-rata wellbeing siswa {$avgScore}%. {$healthyCount} siswa kondisi stabil, {$attentionCount} siswa memerlukan perhatian.";

            $teacher->notify(new AppNotification(
                '📊 Rekap Mingguan Kelas',
                $message,
                'chart',
                $attentionCount > 0 ? 'warning' : 'success',
                route('teacher.students', ['statusFilter' => $attentionCount > 0 ? 'attention' : 'all'])
            ));

            $sentCount++;
            $this->info("Sent weekly summary to teacher: {$teacher->name} ({$teacher->email})");
        }

        $this->info("Successfully sent weekly summaries to {$sentCount} teacher(s).");

        return self::SUCCESS;
    }
}
