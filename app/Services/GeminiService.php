<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    private string $apiKey;

    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.api_key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.5-pro');
    }

    public function ask(string $system, string $user, ?string $model = null): string
    {
        return $this->call($system, $user, $model ?? $this->model, withSearch: false);
    }

    public function askWithWebSearch(string $system, string $user, ?string $model = null): string
    {
        return $this->call($system, $user, $model ?? $this->model, withSearch: true);
    }

    private function call(string $system, string $user, string $model, bool $withSearch): string
    {
        try {
            return $this->callModel($system, $user, $model, $withSearch);
        } catch (\RuntimeException $e) {
            $fallback = config('services.gemini.fallback_model', 'gemini-2.5-pro');
            if ($model === $fallback) {
                throw $e;
            }

            Log::warning('Gemini falling back to alternate model', [
                'primary' => $model,
                'fallback' => $fallback,
                'error' => mb_substr($e->getMessage(), 0, 200),
            ]);

            return $this->callModel($system, $user, $fallback, $withSearch);
        }
    }

    private function callModel(string $system, string $user, string $model, bool $withSearch): string
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
                'maxOutputTokens' => 8192,
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

            $status = $response->status();
            $body = $response->body();
            $isRateLimit = $status === 429 || str_contains($body, 'RESOURCE_EXHAUSTED');
            $isServerError = $status >= 500 && $status < 600;
            $shouldRetry = $isRateLimit || $isServerError;

            if (! $shouldRetry || $attempt >= $maxAttempts) {
                Log::error('Gemini API error', ['status' => $status, 'body' => $body]);
                throw new \RuntimeException('Gemini API failed: '.$body);
            }

            $wait = $this->parseRetryDelay($response->json()) ?? ($backoff[$attempt - 1] ?? 60);
            Log::warning('Gemini transient error — retrying', [
                'status' => $status,
                'attempt' => $attempt,
                'wait_seconds' => $wait,
            ]);
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
