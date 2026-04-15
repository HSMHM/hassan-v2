<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini API wrapper.
 *
 * Endpoint: POST /v1beta/models/{model}:generateContent
 * Auth:     header `x-goog-api-key: GEMINI_API_KEY`
 *
 * Search grounding: pass `tools: [{google_search: {}}]` — Google Search is
 * performed by the model automatically, no extra token cost to us.
 */
class GeminiService
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    private string $apiKey;

    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.api_key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.5-flash-lite');
    }

    /**
     * Plain text generation.
     */
    public function ask(string $system, string $user, ?string $model = null): string
    {
        return $this->call($system, $user, $model ?? $this->model, withSearch: false);
    }

    /**
     * Generation with Google Search grounding.
     */
    public function askWithWebSearch(string $system, string $user, ?string $model = null): string
    {
        return $this->call($system, $user, $model ?? $this->model, withSearch: true);
    }

    private function call(string $system, string $user, string $model, bool $withSearch): string
    {
        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $system]],
            ],
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $user]],
            ]],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 4096,
            ],
        ];

        if ($withSearch) {
            $payload['tools'] = [['google_search' => new \stdClass]];
        }

        return $this->callWithRetry(function () use ($model, $payload) {
            return Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(180)->post(self::BASE_URL."/models/{$model}:generateContent", $payload);
        });
    }

    /**
     * Retry on 429. Gemini returns the exact wait time inside the error body
     * (error.details[].retryDelay, e.g. "31s"). Use that when present; fall
     * back to exponential backoff otherwise.
     */
    private function callWithRetry(callable $request, int $maxAttempts = 3): string
    {
        $attempt = 0;
        $backoff = [15, 30, 60];

        while (true) {
            $response = $request();
            $attempt++;

            if ($response->successful()) {
                $data = $response->json();

                return collect($data['candidates'][0]['content']['parts'] ?? [])
                    ->pluck('text')
                    ->filter()
                    ->implode("\n");
            }

            $body = $response->body();
            $isRateLimit = $response->status() === 429 || str_contains($body, 'RESOURCE_EXHAUSTED');

            if (! $isRateLimit || $attempt >= $maxAttempts) {
                Log::error('Gemini API error', ['status' => $response->status(), 'body' => $body]);
                throw new \RuntimeException('Gemini API failed: '.$body);
            }

            $wait = $this->parseRetryDelay($response->json()) ?? ($backoff[$attempt - 1] ?? 60);
            Log::warning('Gemini rate limit — retrying', ['attempt' => $attempt, 'wait_seconds' => $wait]);
            sleep($wait);
        }
    }

    private function parseRetryDelay(?array $body): ?int
    {
        foreach ($body['error']['details'] ?? [] as $detail) {
            if (($detail['@type'] ?? '') === 'type.googleapis.com/google.rpc.RetryInfo'
                && preg_match('/^(\d+)s$/', $detail['retryDelay'] ?? '', $m)) {
                return ((int) $m[1]) + 2;
            }
        }

        return null;
    }
}
