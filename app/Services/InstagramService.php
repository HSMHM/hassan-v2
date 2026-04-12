<?php

namespace App\Services;

use App\Models\PlatformToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    private const API_VERSION = 'v25.0';

    private string $accessToken;

    private string $accountId;

    public function __construct()
    {
        $this->accessToken = PlatformToken::tokenFor('instagram') ?: (string) config('services.instagram.access_token');
        $this->accountId = config('services.instagram.account_id');
    }

    public function hasAccessToken(): bool
    {
        return $this->accessToken !== '';
    }

    public function accessTokenSource(): string
    {
        return PlatformToken::tokenFor('instagram') ? 'database' : 'env';
    }

    public function storeAccessToken(string $token, ?int $expiresIn = null): void
    {
        PlatformToken::saveToken(
            'instagram',
            $token,
            null,
            $expiresIn ? now()->addSeconds($expiresIn) : now()->addDays(60)
        );

        $this->accessToken = $token;
    }

    public function postImage(string $imageUrl, string $caption): array
    {
        if (! $this->hasAccessToken()) {
            throw new \RuntimeException('Instagram access token is missing. Store a valid token first.');
        }

        $containerResponse = Http::post(
            "https://graph.instagram.com/".self::API_VERSION."/{$this->accountId}/media",
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
            "https://graph.instagram.com/".self::API_VERSION."/{$this->accountId}/media_publish",
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
                "https://graph.instagram.com/".self::API_VERSION."/{$containerId}",
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
        if (! $this->hasAccessToken()) {
            Log::warning('Instagram token refresh skipped because no access token is configured');

            return null;
        }

        $response = Http::get('https://graph.instagram.com/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            $newToken = $response->json('access_token');
            Log::info('Instagram token refreshed', ['expires_in' => $response->json('expires_in')]);

            if ($newToken) {
                $this->storeAccessToken($newToken, (int) $response->json('expires_in', 0));
            }

            return $newToken;
        }

        Log::error('Instagram token refresh failed', ['body' => $response->body()]);

        return null;
    }
}
