<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;

    private string $baseUrl;

    public function __construct()
    {
        $this->token = config('services.whapi.token');
        $this->baseUrl = rtrim(config('services.whapi.base_url'), '/');
    }

    /**
     * Post a text status (WhatsApp Story).
     */
    public function postTextStatus(string $text): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/stories/send/text", [
            'caption' => $text,
        ]);

        if (! $response->successful()) {
            Log::error('WhatsApp status failed', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }

    /**
     * Post an image status with caption.
     */
    public function postImageStatus(string $imageUrl, string $caption = ''): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/stories/send/media", [
            'media' => ['link' => $imageUrl],
            'caption' => $caption,
        ]);

        if (! $response->successful()) {
            Log::error('WhatsApp status failed', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }
}
