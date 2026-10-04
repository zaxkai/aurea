<?php

namespace App\Services;

use App\Exceptions\AiPromptLimitReachedException;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    private const array ENGLISH_LANGUAGE_CUES = [
        'am', 'because', 'but', 'feel', 'for', 'have', 'hello', 'i', 'is',
        'me', 'my', 'school', 'so', 'teacher', 'the', 'this', 'tired', 'to',
        'was', 'with', 'you', 'your',
    ];

    private const array INDONESIAN_LANGUAGE_CUES = [
        'aku', 'anda', 'banget', 'belum', 'cerita', 'dan', 'dengan', 'di',
        'guru', 'ini', 'itu', 'kamu', 'karena', 'lagi', 'lelah', 'mau',
        'merasa', 'nggak', 'saya', 'sih', 'tidak', 'untuk', 'yang',
    ];

    // Coba model utama dulu, kalau gagal (limit/overload) coba yang berikutnya
    protected array $models = [
        'gemini-3.5-flash',
        'gemini-3.5-flash-lite',
        'gemini-2.5-flash',
    ];

    public function chat(User $user, string $userMessage, array $history = []): string
    {
        $isPremium = $user->isPremium();
        $usageDate = now('Asia/Jakarta')->toDateString();

        if (! $isPremium && $this->usageCount($user, $usageDate) >= $this->dailyPromptLimit()) {
            throw new AiPromptLimitReachedException('Kuota chat AI hari ini sudah habis.');
        }

        $contents = [];
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        foreach ($this->models as $model) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['x-goog-api-key' => env('GEMINI_API_KEY')])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                        'system_instruction' => ['parts' => [['text' => $this->getSystemPrompt($userMessage)]]],
                        'contents' => $contents,
                        'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 500],
                    ]);
            } catch (\Throwable $e) {
                Log::error("Chatbot error pakai model {$model}: ".$e->getMessage());

                continue;
            }

            if (in_array($response->status(), [429, 503])) {
                Log::warning("Model {$model} kena limit/overload, coba model berikutnya");

                continue;
            }

            if (! $response->successful()) {
                Log::warning("Model {$model} mengembalikan status {$response->status()}");

                continue;
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            if (is_string($text) && trim($text) !== '') {
                if (! $isPremium) {
                    $user->aiUsageLogs()->create(['usage_date' => $usageDate]);
                }

                return $text;
            }
        }

        // Semua model gagal
        return 'Maaf, Aurea lagi ramai banget sekarang 💙 Coba chat lagi sebentar lagi ya.';
    }

    public function dailyPromptLimit(): int
    {
        return (int) config('premium.free_limits.ai_prompts_per_day', 5);
    }

    public function remainingPrompts(User $user): ?int
    {
        if ($user->isPremium()) {
            return null;
        }

        $usageDate = now('Asia/Jakarta')->toDateString();

        return max(0, $this->dailyPromptLimit() - $this->usageCount($user, $usageDate));
    }

    private function usageCount(User $user, string $usageDate): int
    {
        return $user->aiUsageLogs()
            ->whereDate('usage_date', $usageDate)
            ->count();
    }

    /**
     * @return array{summary: string, advice: string}
     */
    public function summarizeJournal(string $journalContent, string $mood): array
    {
        $fallback = [
            'summary' => Str::limit(trim($journalContent), 220),
            'advice' => 'Coba beri dirimu waktu sejenak untuk memahami perasaan ini. Kamu tidak harus menyelesaikan semuanya sekaligus.',
        ];
        $apiKey = env('GEMINI_API_KEY');

        if (! $apiKey) {
            return $fallback;
        }

        $prompt = "Mood yang dipilih: {$mood}\n\nCatatan jurnal pengguna:\n{$journalContent}\n\nBuat ringkasan yang setia pada isi catatan dan satu saran kecil yang hangat, praktis, tidak menghakimi, serta tidak mendiagnosis. Balas hanya JSON dengan properti string summary dan advice.";

        foreach ($this->models as $model) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['x-goog-api-key' => $apiKey])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                        'system_instruction' => ['parts' => [['text' => 'Kamu adalah Aurea, pendamping journaling yang hangat untuk remaja. Gunakan bahasa utama yang sama dengan isi catatan jurnal untuk summary dan advice; abaikan bahasa instruksi, label mood, dan properti JSON. Jaga privasi, jangan mendiagnosis atau menyarankan obat. Jika catatan mengandung risiko menyakiti diri, sarankan menghubungi orang dewasa tepercaya atau bantuan darurat.']]],
                        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'maxOutputTokens' => 400,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                if (in_array($response->status(), [429, 503])) {
                    continue;
                }

                $result = json_decode($response->json('candidates.0.content.parts.0.text', ''), true);

                if (is_array($result) && filled($result['summary'] ?? null) && filled($result['advice'] ?? null)) {
                    return [
                        'summary' => trim($result['summary']),
                        'advice' => trim($result['advice']),
                    ];
                }
            } catch (\Throwable $exception) {
                Log::warning("Journal summary failed with model {$model}: ".$exception->getMessage());
            }
        }

        return $fallback;
    }

    protected function getSystemPrompt(string $latestUserMessage): string
    {
        $responseLanguage = $this->detectResponseLanguage($latestUserMessage);
        $languageInstruction = $responseLanguage
            ? "REQUIRED RESPONSE LANGUAGE: {$responseLanguage}. Write your entire response only in {$responseLanguage}. Do not use another language."
            : 'Reply entirely in the primary language used in the latest user message. Ignore the language of earlier conversation turns.';

        $prompt = <<<'PROMPT'
You are Aurea, a warm and supportive AI companion for teenagers dealing with stress or emotional difficulties.
LANGUAGE — highest priority:
- Reply entirely in the primary language used in the user's latest message.
- Determine the language from the latest message alone. Ignore the language of this system prompt, earlier messages, and the user's profile or locale.
- If the latest message is in English, reply in English. If it is in Indonesian, reply in Indonesian.
- For a message mixing languages, reply in the language used most.
STYLE: Be warm, nonjudgmental, casual, and respectful.
SAFETY AND SUPPORT:
- Do not diagnose or recommend medication.
- Listen first, validate the user's feelings, and ask one open-ended question.
- Suggest simple coping strategies such as breathing, journaling, or talking to someone they trust.
- If the user mentions self-harm or suicide, respond calmly and compassionately, encourage them to contact a trusted adult or emergency/professional support, and do not change the subject.
PROMPT;

        return $languageInstruction."\n\n".$prompt;
    }

    private function detectResponseLanguage(string $message): ?string
    {
        $tokens = preg_split('/[^\p{L}]+/u', Str::lower($message), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $englishScore = count(array_intersect($tokens, self::ENGLISH_LANGUAGE_CUES));
        $indonesianScore = count(array_intersect($tokens, self::INDONESIAN_LANGUAGE_CUES));

        if ($englishScore > $indonesianScore) {
            return 'English';
        }

        if ($indonesianScore > $englishScore) {
            return 'Indonesian';
        }

        return null;
    }
}
