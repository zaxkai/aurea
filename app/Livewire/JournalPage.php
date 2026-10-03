<?php

namespace App\Livewire;

use App\Models\Journal;
use App\Services\ChatbotService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JournalPage extends Component
{
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
            'emoji' => '☺',
            'background' => 'bg-mood-calm',
            'color' => '#ed62e8',
            'score' => 2,
            'mascot' => 'maskot-journal4.png',
            'title' => 'Balanced day',
        ],
        'neutral' => [
            'label' => 'Neutral',
            'emoji' => '😐',
            'background' => 'bg-mood-neutral',
            'color' => '#31dfa0',
            'score' => 3,
            'mascot' => 'maskot-journal1.png',
            'title' => 'A quiet reflection',
        ],
        'stressed' => [
            'label' => 'Stressed',
            'emoji' => '☹',
            'background' => 'bg-mood-stressed',
            'color' => '#ff3e9a',
            'score' => 4,
            'mascot' => 'maskot-journal3.png',
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

        auth()->user()->journals()->create([
            'title' => $this->moods[$validated['mood']]['title'],
            'content' => trim($validated['content']),
            'mood' => $validated['mood'],
            'summary' => $this->summary,
            'advice' => $this->advice,
            'journal_date' => today(),
        ]);

        $this->closeModals();
        $this->mood = 'neutral';
        $this->content = '';
        $this->summary = '';
        $this->advice = '';

        session()->flash('journal-saved', 'Your journal has been saved.');
    }

    public function render(): View
    {
        $journals = auth()->user()->journals()
            ->latest('created_at')
            ->limit(20)
            ->get();

        $journalsByDate = $journals->groupBy(fn (Journal $journal): string => ($journal->journal_date ?? $journal->created_at)->toDateString()
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

        return view('livewire.journal-page', [
            'journals' => $journals,
            'chartPoints' => $chartPoints,
        ]);
    }
}
