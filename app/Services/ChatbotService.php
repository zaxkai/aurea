<?php

namespace App\Services;

use App\Exceptions\AiPromptLimitReachedException;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
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
                    ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                        'system_instruction' => ['parts' => [['text' => $this->getSystemPrompt()]]],
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
        $apiKey = config('services.gemini.key');

        if (! $apiKey) {
            return $fallback;
        }

        $prompt = "Mood yang dipilih: {$mood}\n\nCatatan jurnal pengguna:\n{$journalContent}\n\nBuat ringkasan yang setia pada isi catatan dan satu saran kecil yang hangat, praktis, tidak menghakimi, serta tidak mendiagnosis. Balas hanya JSON dengan properti string summary dan advice.";

        foreach ($this->models as $model) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['x-goog-api-key' => $apiKey])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                        'system_instruction' => ['parts' => [['text' => 'Kamu adalah Aurea, pendamping journaling yang hangat untuk remaja Indonesia. Jaga privasi, jangan mendiagnosis atau menyarankan obat. Jika catatan mengandung risiko menyakiti diri, sarankan menghubungi orang dewasa tepercaya atau bantuan darurat.']]],
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

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Kamu adalah Aurea, teman ngobrol AI untuk remaja Indonesia yang lagi menghadapi stres atau masalah emosi.
Gaya: hangat, tidak menghakimi, bahasa santai tapi sopan.
Aturan:
- Jangan mendiagnosis dan jangan menyarankan obat.
- Dengarkan dulu, validasi perasaan, ajukan satu pertanyaan terbuka.
- Sarankan coping sederhana (napas, journaling, cerita ke orang tepercaya).
- Kalau user menyebut ingin menyakiti diri atau mengakhiri hidup, tanggapi dengan tenang dan peduli, dorong dia menghubungi orang dewasa tepercaya atau layanan darurat/profesional, dan jangan lanjut ke topik lain.
PROMPT;
    }
}
