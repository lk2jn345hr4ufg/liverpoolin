<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Wraps the Google Gemini "generateContent" REST API.
 *
 * The model rewrites the article AND picks the most relevant category in a
 * single call — classification costs nothing extra this way. The returned
 * slug is validated against the categories table; an unknown or missing
 * slug simply yields a null category rather than an error.
 */
class GeminiEditor
{
    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = null,
    ) {
        $this->apiKey = $apiKey
            ?: $this->readKey(Setting::get('gemini_api_key'))
            ?: config('services.gemini.key');

        $this->model = $model
            ?: Setting::get('gemini_model')
            ?: config('services.gemini.model', 'gemini-3.5-flash-lite');

        if (empty($this->apiKey)) {
            throw new RuntimeException('Gemini API key is not set. Add it in Admin → Settings.');
        }
    }

    private function readKey(?string $stored): ?string
    {
        if (blank($stored)) {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException $e) {
            return $stored; // legacy plaintext
        }
    }

    /**
     * @return array{title:string, content:string, category_id:?int}
     */
    public function edit(string $sourceTitle, string $sourceBody): array
    {
        $prompt = Setting::get('edit_prompt', 'Rewrite the following news in your own words.');

        // Append the category instruction. Kept out of the editable prompt so
        // the admin can't accidentally break classification while tweaking tone.
        $categories = Category::promptList();

        $categoryInstruction = $categories === '' ? '' : <<<TXT


--- КАТЕГОРИИ ---
Выбери ОДНУ наиболее подходящую категорию из списка ниже и верни её slug в поле "category".
{$categories}

Формат ответа — СТРОГО JSON без markdown:
{"title": "...", "content": "...", "category": "slug-из-списка"}
TXT;

        $userContent = $prompt
            . "\n\n--- SOURCE TITLE ---\n" . $sourceTitle
            . "\n\n--- SOURCE BODY ---\n" . $sourceBody
            . $categoryInstruction;

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            $this->model
        );

        $response = Http::timeout(60)
            ->retry(2, 1500)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->post($endpoint, [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [['text' => $userContent]],
                ]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini API error', ['body' => $response->body()]);
            throw new RuntimeException('Gemini API request failed: ' . $response->status());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (empty($text)) {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        return $this->parse($text, $sourceTitle);
    }

    private function parse(string $text, string $fallbackTitle): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?/i', '', $clean);
        $clean = preg_replace('/```$/', '', trim($clean));

        $data = json_decode(trim($clean), true);

        if (is_array($data) && ! empty($data['content'])) {
            return [
                'title'       => trim($data['title'] ?? $fallbackTitle),
                'content'     => trim($data['content']),
                'category_id' => $this->resolveCategory($data['category'] ?? null),
            ];
        }

        return [
            'title'       => $fallbackTitle,
            'content'     => trim($text),
            'category_id' => null,
        ];
    }

    /**
     * Map the AI's slug to a real category id. Tolerates the model returning
     * the display name instead of the slug. Unknown values -> null.
     */
    private function resolveCategory(?string $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        $category = Category::where('slug', $value)->first()
            ?? Category::where('name', $value)->first();

        if (! $category) {
            Log::info('Gemini returned an unknown category', ['value' => $value]);
        }

        return $category?->id;
    }
}
