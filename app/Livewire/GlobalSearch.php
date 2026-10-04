<?php

namespace App\Livewire;

use App\Models\Journal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public bool $isOpen = false;

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $pages = [
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
        return collect($this->pages)->filter(function (array $page) use ($term): bool {
            if (str_contains(mb_strtolower($page['title']), $term)) {
                return true;
            }
            if (str_contains(mb_strtolower($page['description']), $term)) {
                return true;
            }
            foreach ($page['keywords'] as $keyword) {
                if (str_contains(mb_strtolower($keyword), $term)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /**
     * @return Collection<int, Journal>
     */
    protected function searchJournals(string $term): Collection
    {
        if (! auth()->check()) {
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

        if (filled($trimmed)) {
            $matchedPages = $this->searchPages($trimmed);
            $matchedJournals = $this->searchJournals($trimmed);
        }

        return view('livewire.global-search', [
            'matchedPages' => $matchedPages,
            'matchedJournals' => $matchedJournals,
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
