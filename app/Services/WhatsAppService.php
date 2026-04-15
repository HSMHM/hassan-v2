<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp HTTP API client — powered by WAHA (self-hosted).
 * https://waha.devlike.pro/docs/how-to/status/
 *
 * Public surface matches the previous Whapi.cloud implementation, so no
 * callers need to change:
 *   - postTextStatus(string $text): array
 *   - postImageStatus(string $imageUrl, string $caption = ''): array
 */
class WhatsAppService
{
    private string $baseUrl;

    private string $session;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.waha.base_url'), '/');
        $this->session = (string) config('services.waha.session', 'default');
        $this->apiKey = (string) config('services.waha.api_key', '');
    }

    public function postTextStatus(string $text): array
    {
        $response = $this->request()->post($this->endpoint('status/text'), [
            'text' => $text,
            'backgroundColor' => (string) config('services.waha.status_bg', '#121212'),
            'font' => (int) config('services.waha.status_font', 1),
        ]);

        if (! $response->successful()) {
            Log::error('WAHA text status failed', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }

    public function postImageStatus(string $imageUrl, string $caption = ''): array
    {
        $response = $this->request()->post($this->endpoint('status/image'), [
            'file' => [
                'mimetype' => 'image/jpeg',
                'url' => $imageUrl,
            ],
            'caption' => $caption,
        ]);

        if (! $response->successful()) {
            Log::error('WAHA image status failed', ['body' => $response->body()]);
        }

        return $response->json() ?? [];
    }

    private function request()
    {
        $client = Http::acceptJson()->asJson();

        if ($this->apiKey !== '') {
            $client = $client->withHeaders(['X-Api-Key' => $this->apiKey]);
        }

        return $client;
    }

    private function endpoint(string $path): string
    {
        return "{$this->baseUrl}/api/{$this->session}/{$path}";
    }
}
