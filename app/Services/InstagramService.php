<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    private string $accessToken;

    private string $accountId;

    public function __construct()
    {
        $this->accessToken = config('services.instagram.access_token');
        $this->accountId = config('services.instagram.account_id');
    }

    public function postImage(string $imageUrl, string $caption): array
    {
        $containerResponse = Http::post(
            "https://graph.instagram.com/v21.0/{$this->accountId}/media",
            [
                'image_url' => $imageUrl,
                'caption' => $caption,
                'access_token' => $this->accessToken,
            ]
        );

        if (! $containerResponse->successful()) {
            throw new \RuntimeException('Instagram container failed: '.$containerResponse->body());
        }

        $containerId = $containerResponse->json('id');

        $this->waitForContainer($containerId);

        $publishResponse = Http::post(
            "https://graph.instagram.com/v21.0/{$this->accountId}/media_publish",
            [
                'creation_id' => $containerId,
                'access_token' => $this->accessToken,
            ]
        );

        if (! $publishResponse->successful()) {
            throw new \RuntimeException('Instagram publish failed: '.$publishResponse->body());
        }

        return $publishResponse->json();
    }

    private function waitForContainer(string $containerId, int $maxAttempts = 10): void
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(3);

            $status = Http::get(
                "https://graph.instagram.com/v21.0/{$containerId}",
                [
                    'fields' => 'status_code',
                    'access_token' => $this->accessToken,
                ]
            );

            $code = $status->json('status_code');

            if ($code === 'FINISHED') {
                return;
            }
            if ($code === 'ERROR') {
                throw new \RuntimeException('Instagram container processing error');
            }
        }

        throw new \RuntimeException('Instagram container processing timeout');
    }

    public function refreshToken(): ?string
    {
        $response = Http::get('https://graph.instagram.com/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            $newToken = $response->json('access_token');
            Log::info('Instagram token refreshed', ['expires_in' => $response->json('expires_in')]);

            return $newToken;
        }

        Log::error('Instagram token refresh failed', ['body' => $response->body()]);

        return null;
    }
}
