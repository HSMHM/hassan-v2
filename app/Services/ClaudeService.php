<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.anthropic.model');
        $this->baseUrl = config('services.anthropic.base_url');
        $this->maxTokens = config('services.anthropic.max_tokens');
    }

    public function ask(string $system, string $user, ?string $model = null): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->baseUrl}/messages", [
            'model' => $model ?? $this->model,
            'max_tokens' => $this->maxTokens,
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
        ]);

        if (! $response->successful()) {
            Log::error('Claude API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Claude API failed: '.$response->body());
        }

        return $response->json();
    }

    public function askWithWebSearch(string $system, string $user, ?string $model = null): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->baseUrl}/messages", [
            'model' => $model ?? $this->model,
            'max_tokens' => $this->maxTokens,
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
            'tools' => [['type' => 'web_search_20250305', 'name' => 'web_search']],
        ]);

        if (! $response->successful()) {
            Log::error('Claude API error', ['body' => $response->body()]);
            throw new \RuntimeException('Claude API failed: '.$response->body());
        }

        return $response->json();
    }

    public function extractText(array $response): string
    {
        return collect($response['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");
    }
}
