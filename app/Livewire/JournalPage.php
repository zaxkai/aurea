<?php

namespace App\Livewire;

use App\Models\Journal;
use App\Notifications\AppNotification;
use App\Services\ChatbotService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class JournalPage extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public ?int $highlight = null;

    public bool $showWriteModal = false;

    public bool $showSummaryModal = false;

    public string $mood = 'neutral';

    public string $content = '';

    public string $summary = '';

    public string $advice = '';

    public array $moods = [
        'energetic' => [
            'label' => 'Energetic',
            'emoji' => '☺',
            'background' => 'bg-mood-energetic',
            'color' => '#d6e600',
            'score' => 1,
            'mascot' => 'maskot-journal2.png',
            'title' => 'A brighter day',
        ],
        'calm' => [
            'label' => 'Calm',
            'emoji' => '😌',
            'background' => 'bg-mood-calm',
            'color' => '#ed62e8',
            'score' => 2,
            'mascot' => 'maskot-journal3.png',
            'title' => 'Balanced day',
        ],
        'neutral' => [
            'label' => 'Neutral',
            'emoji' => '😐',
            'background' => 'bg-mood-neutral',
            'color' => '#31dfa0',
            'score' => 3,
            'mascot' => 'maskot-journal3.png',
            'title' => 'A quiet reflection',
        ],
        'stressed' => [
            'label' => 'Stressed',
            'emoji' => '☹',
            'background' => 'bg-mood-stressed',
            'color' => '#ff3e9a',
            'score' => 4,
            'mascot' => 'maskot-journal1.png',
            'title' => 'Stressed mind',
        ],
        'exhausted' => [
            'label' => 'Exhausted',
            'emoji' => '☹',
            'background' => 'bg-mood-exhausted',
            'color' => '#2ed5d5',
            'score' => 5,
            'mascot' => 'maskot-journal4.png',
            'title' => 'A day to pause',
        ],
    ];

    public function openWriteModal(): void
    {
        $this->resetValidation();
        $this->mood = 'neutral';
        $this->content = '';
        $this->summary = '';
        $this->advice = '';
        $this->showWriteModal = true;
    }

    public function closeModals(): void
    {
        $this->showWriteModal = false;
        $this->showSummaryModal = false;
        $this->resetValidation();
    }

    public function generateSummary(ChatbotService $chatbot): void
    {
        $validated = $this->validate([
            'mood' => ['required', 'in:energetic,calm,neutral,stressed,exhausted'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $result = $chatbot->summarizeJournal(trim($validated['content']), $validated['mood']);
        $this->summary = $result['summary'];
        $this->advice = $result['advice'];
        $this->showWriteModal = false;
        $this->showSummaryModal = true;
    }

    public function returnToWriting(): void
    {
        $this->showSummaryModal = false;
        $this->showWriteModal = true;
    }

    public function saveJournal(): void
    {
        $validated = $this->validate([
            'mood' => ['required', 'in:energetic,calm,neutral,stressed,exhausted'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        if ($this->summary === '' || $this->advice === '') {
            $this->addError('content', 'Buat ringkasan terlebih dahulu sebelum menyimpan jurnal.');
            $this->showSummaryModal = false;
            $this->showWriteModal = true;

            return;
        }

        $isFirstJournal = auth()->user()->journals()->count() === 0;

        auth()->user()->journals()->create([
            'title' => $this->moods[$validated['mood']]['title'],
            'content' => trim($validated['content']),
            'mood' => $validated['mood'],
            'summary' => $this->summary,
            'advice' => $this->advice,
            'journal_date' => today(),
        ]);

        if ($isFirstJournal) {
            auth()->user()->notify(new AppNotification(
                '⭐ First Journal!',
                'You wrote your first journal. Keep nurturing your mind.',
                'star',
                'success'
            ));
        }

        $this->closeModals();
        $this->mood = 'neutral';
        $this->content = '';
        $this->summary = '';
        $this->advice = '';

        session()->flash('journal-saved', 'Your journal has been saved.');
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->highlight = null;
    }

    public function render(): View
    {
        $allJournals = auth()->user()->journals()
            ->latest('created_at')
            ->limit(50)
            ->get();

        $journalsByDate = $allJournals->groupBy(fn (Journal $journal): string => ($journal->journal_date ?? $journal->created_at)->toDateString()
        )->map(fn ($entries) => $entries->first());

        $chartPoints = collect(range(9, 0))
            ->map(function (int $daysAgo, int $index) use ($journalsByDate): array {
                $date = today()->subDays($daysAgo);
                $journal = $journalsByDate->get($date->toDateString());
                $mood = $journal?->mood;
                $moodData = $this->moods[$mood] ?? null;

                return [
                    'x' => 48 + ($index * 77),
                    'y' => $moodData ? 18 + ($moodData['score'] * 24) : null,
                    'label' => $date->format('M j'),
                    'mood' => $mood,
                    'color' => $moodData['color'] ?? '#dce3f1',
                ];
            })
            ->all();

        $filteredJournals = $allJournals;
        if (filled(trim($this->search))) {
            $term = mb_strtolower(trim($this->search));
            $filteredJournals = $allJournals->filter(function (Journal $journal) use ($term): bool {
                return str_contains(mb_strtolower((string) $journal->title), $term)
                    || str_contains(mb_strtolower((string) $journal->content), $term)
                    || str_contains(mb_strtolower((string) $journal->summary), $term)
                    || str_contains(mb_strtolower((string) $journal->advice), $term)
                    || str_contains(mb_strtolower((string) $journal->mood), $term)
                    || ($journal->journal_date && str_contains(mb_strtolower($journal->journal_date->format('M j Y F d')), $term));
            });
        }

        return view('livewire.journal-page', [
            'journals' => $filteredJournals,
            'chartPoints' => $chartPoints,
            'highlightedJournalId' => $this->highlight,
        ]);
    }
}
