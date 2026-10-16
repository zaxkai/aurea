<?php

namespace App\Livewire;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public bool $isOpen = false;

    /**
     * Get role-aware searchable app pages.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPages(): array
    {
        $isTeacher = auth()->check() && auth()->user()->isTeacher();

        if ($isTeacher) {
            return [
                [
                    'id' => 'dashboard',
                    'title' => 'Dashboard',
                    'category' => 'Navigation',
                    'description' => 'Teacher overview, classroom statistics & alerts',
                    'route' => 'dashboard',
                    'badge' => '/dashboard',
                    'icon' => 'home',
                    'keywords' => ['dashboard', 'home', 'beranda', 'teacher', 'guru', 'overview', 'ringkasan', 'kelas'],
                ],
                [
                    'id' => 'teacher-students',
                    'title' => 'Student Wellbeing',
                    'category' => 'Management',
                    'description' => 'Monitor student mental health and wellbeing indicators',
                    'route' => 'teacher.students',
                    'badge' => '/teacher/students',
                    'icon' => 'students',
                    'keywords' => [
                        'student wellbeing', 'student', 'students', 'wellbeing', 'wel being',
                        'well being', 'welbeing', 'wel', 'being', 'siswa', 'murid',
                        'pantau', 'monitoring', 'kesehatan mental', 'kelas', 'teacher', 'guru',
                    ],
                ],
                [
                    'id' => 'profile',
                    'title' => 'Profile',
                    'category' => 'Navigation',
                    'description' => 'View profile details, classroom stats & account info',
                    'route' => 'profile',
                    'badge' => '/profile',
                    'icon' => 'profile',
                    'keywords' => ['profile', 'profil', 'akun', 'account', 'guru', 'teacher', 'bio'],
                ],
                [
                    'id' => 'settings',
                    'title' => 'Settings',
                    'category' => 'Navigation',
                    'description' => 'Profile, password & account preferences',
                    'route' => 'settings',
                    'badge' => '/settings',
                    'icon' => 'settings',
                    'keywords' => ['settings', 'pengaturan', 'profile', 'profil', 'akun', 'password'],
                ],
            ];
        }

        return [
            [
                'id' => 'dashboard',
                'title' => 'Dashboard',
                'category' => 'Navigation',
                'description' => 'Home overview, daily habit tracker & check-ins',
                'route' => 'dashboard',
                'badge' => '/dashboard',
                'icon' => 'home',
                'keywords' => ['dashboard', 'home', 'beranda', 'habit', 'tracker', 'streak', 'today', 'check-in', 'overview'],
            ],
            [
                'id' => 'journal',
                'title' => 'Journal',
                'category' => 'Navigation',
                'description' => 'Write your thoughts, daily reflections & mood logs',
                'route' => 'journal',
                'badge' => '/journal',
                'icon' => 'journal',
                'keywords' => ['journal', 'jurnal', 'diary', 'write', 'tulis', 'mood', 'catatan', 'refleksi', 'notes'],
            ],
            [
                'id' => 'habit-growth-tree',
                'title' => 'Habit Growth Tree',
                'category' => 'Navigation',
                'description' => 'See your tree bloom as you complete habits',
                'route' => 'habit-growth-tree',
                'badge' => '/habit-growth-tree',
                'icon' => 'tree',
                'keywords' => ['habit growth tree', 'tree', 'pohon', 'growth', 'resilience', 'kebiasaan', 'pertumbuhan'],
            ],
            [
                'id' => 'ai-aurea',
                'title' => 'AI Aurea',
                'category' => 'Navigation',
                'description' => 'AI companion for counseling, mental wellness & chat',
                'route' => 'ai-aurea',
                'badge' => '/ai-aurea',
                'icon' => 'ai',
                'keywords' => ['ai aurea', 'ai', 'aurea', 'chat', 'bot', 'curhat', 'counseling', 'tanya', 'konseling'],
            ],
            [
                'id' => 'premium',
                'title' => 'Premium Plan',
                'category' => 'Navigation',
                'description' => 'Unlock unlimited habits and unlimited AI chats',
                'route' => 'premium',
                'badge' => '/premium',
                'icon' => 'star',
                'keywords' => ['premium', 'pro', 'upgrade', 'langganan', 'unlimited'],
            ],
            [
                'id' => 'profile',
                'title' => 'Profile',
                'category' => 'Navigation',
                'description' => 'Personal profile and wellbeing overview',
                'route' => 'profile',
                'badge' => '/profile',
                'icon' => 'profile',
                'keywords' => ['profile', 'profil', 'akun', 'account', 'bio'],
            ],
            [
                'id' => 'settings',
                'title' => 'Settings',
                'category' => 'Navigation',
                'description' => 'Profile, password & account preferences',
                'route' => 'settings',
                'badge' => '/settings',
                'icon' => 'settings',
                'keywords' => ['settings', 'pengaturan', 'profile', 'profil', 'akun', 'password'],
            ],
        ];
    }

    public function updatedQuery(): void
    {
        $this->isOpen = filled(trim($this->query));
    }

    public function resetSearch(): void
    {
        $this->query = '';
        $this->isOpen = false;
    }

    public function selectPage(string $routeName): mixed
    {
        $this->resetSearch();

        return redirect()->route($routeName);
    }

    public function selectStudent(int|string $student): mixed
    {
        $this->resetSearch();

        if (is_numeric($student)) {
            $user = User::find($student);
            $search = $user ? $user->name : '';
        } else {
            $search = $student;
        }

        return redirect()->route('teacher.students', ['search' => $search]);
    }

    public function selectJournal(int $journalId): mixed
    {
        $this->resetSearch();

        return redirect()->route('journal', ['highlight' => $journalId]);
    }

    public function submitSearch(): mixed
    {
        $trimmed = mb_strtolower(trim($this->query));
        if (! filled($trimmed)) {
            return null;
        }

        $pages = $this->searchPages($trimmed);
        if ($pages->isNotEmpty()) {
            return $this->selectPage($pages->first()['route']);
        }

        if (auth()->check() && auth()->user()->isTeacher()) {
            $students = $this->searchStudents($trimmed);
            if ($students->isNotEmpty()) {
                return $this->selectStudent($students->first()['name']);
            }

            return redirect()->route('teacher.students', ['search' => $this->query]);
        }

        $journals = $this->searchJournals($trimmed);
        if ($journals->isNotEmpty()) {
            return $this->selectJournal($journals->first()->id);
        }

        return redirect()->route('journal', ['search' => $this->query]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function searchPages(string $term): Collection
    {
        $normalizedTerm = str_replace('-', ' ', $term);
        $tokens = array_values(array_filter(explode(' ', $normalizedTerm)));

        return collect($this->getPages())->filter(function (array $page) use ($term, $tokens): bool {
            $haystack = mb_strtolower(
                $page['title'].' '.
                $page['description'].' '.
                implode(' ', $page['keywords'])
            );

            // 1. Direct substring match
            if (str_contains($haystack, $term)) {
                return true;
            }

            // 2. Multi-word token match (e.g. "student wel being", "habit tree")
            if (count($tokens) > 1) {
                $allMatch = true;
                foreach ($tokens as $token) {
                    if (! str_contains($haystack, $token)) {
                        $allMatch = false;
                        break;
                    }
                }
                if ($allMatch) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /**
     * @return Collection<int, array{id: int, name: string, email: string, classroom: string}>
     */
    protected function searchStudents(string $term): Collection
    {
        if (! auth()->check() || ! auth()->user()->isTeacher()) {
            return collect();
        }

        $teacher = auth()->user();
        $classroomIds = $teacher->taughtClassrooms()->pluck('id');

        if ($classroomIds->isEmpty()) {
            return collect();
        }

        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return DB::table('classroom_student')
            ->join('users', 'classroom_student.student_id', '=', 'users.id')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->whereIn('classroom_student.classroom_id', $classroomIds)
            ->where(function ($query) use ($like, $term) {
                $query->where('users.name', $like, '%'.$term.'%')
                    ->orWhere('users.first_name', $like, '%'.$term.'%')
                    ->orWhere('users.last_name', $like, '%'.$term.'%')
                    ->orWhere('users.email', $like, '%'.$term.'%')
                    ->orWhere('classrooms.name', $like, '%'.$term.'%');
            })
            ->select(
                'users.id as student_id',
                'users.name as student_name',
                'users.email as student_email',
                'classrooms.name as classroom_name'
            )
            ->get()
            ->unique('student_id')
            ->take(6)
            ->map(fn ($row) => [
                'id' => (int) $row->student_id,
                'name' => (string) $row->student_name,
                'email' => (string) $row->student_email,
                'classroom' => (string) $row->classroom_name,
            ])
            ->values();
    }

    /**
     * @return Collection<int, Journal>
     */
    protected function searchJournals(string $term): Collection
    {
        if (! auth()->check() || auth()->user()->isTeacher()) {
            return collect();
        }

        return auth()->user()->journals()
            ->latest('created_at')
            ->limit(100)
            ->get()
            ->filter(function (Journal $journal) use ($term): bool {
                $content = (string) $journal->content;
                $title = (string) $journal->title;
                $summary = (string) $journal->summary;
                $advice = (string) $journal->advice;
                $mood = (string) $journal->mood;
                $date = $journal->journal_date ? $journal->journal_date->format('M j Y F d') : '';

                return str_contains(mb_strtolower($title), $term)
                    || str_contains(mb_strtolower($content), $term)
                    || str_contains(mb_strtolower($summary), $term)
                    || str_contains(mb_strtolower($advice), $term)
                    || str_contains(mb_strtolower($mood), $term)
                    || str_contains(mb_strtolower($date), $term);
            })
            ->take(6)
            ->values();
    }

    public function getSnippet(string $content, string $term): string
    {
        $clean = trim((string) preg_replace('/\s+/', ' ', strip_tags($content)));
        if ($term === '') {
            return Str::limit($clean, 90);
        }

        $pos = mb_stripos($clean, $term);
        if ($pos === false) {
            return Str::limit($clean, 90);
        }

        $start = max(0, $pos - 25);
        $snippet = mb_substr($clean, $start, 90);
        if ($start > 0) {
            $snippet = '...'.$snippet;
        }
        if ($start + 90 < mb_strlen($clean)) {
            $snippet .= '...';
        }

        return $snippet;
    }

    public function render(): View
    {
        $trimmed = mb_strtolower(trim($this->query));
        $matchedPages = collect();
        $matchedJournals = collect();
        $matchedStudents = collect();

        if (filled($trimmed)) {
            $matchedPages = $this->searchPages($trimmed);
            if (auth()->check() && auth()->user()->isTeacher()) {
                $matchedStudents = $this->searchStudents($trimmed);
            } else {
                $matchedJournals = $this->searchJournals($trimmed);
            }
        }

        return view('livewire.global-search', [
            'matchedPages' => $matchedPages,
            'matchedJournals' => $matchedJournals,
            'matchedStudents' => $matchedStudents,
            'isTeacher' => auth()->check() && auth()->user()->isTeacher(),
            'moodColors' => [
                'energetic' => 'bg-mood-energetic text-navy',
                'calm' => 'bg-mood-calm text-navy',
                'neutral' => 'bg-mood-neutral text-navy',
                'stressed' => 'bg-mood-stressed text-white',
                'exhausted' => 'bg-mood-exhausted text-navy',
            ],
        ]);
    }
}
