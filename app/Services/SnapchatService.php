<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SnapchatService
{
    private string $accessToken;

    private string $organizationId;

    private string $profileId;

    public function __construct()
    {
        $this->accessToken = config('services.snapchat.access_token');
        $this->organizationId = config('services.snapchat.organization_id');
        $this->profileId = config('services.snapchat.profile_id');
    }

    public function postStory(string $mediaUrl, string $caption = ''): array
    {
        $mediaId = $this->uploadMedia($mediaUrl);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json',
        ])->post("https://businessapi.snapchat.com/v1/public_profiles/{$this->profileId}/stories", [
            'media_id' => $mediaId,
        ]);

        if (! $response->successful()) {
            Log::error('Snapchat story failed', ['body' => $response->body()]);
            throw new \RuntimeException('Snapchat API error: '.$response->body());
        }

        return $response->json();
    }

    public function postSpotlight(string $videoUrl, string $caption = ''): array
    {
        $mediaId = $this->uploadMedia($videoUrl);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json',
        ])->post("https://adsapi.snapchat.com/v1/organizations/{$this->organizationId}/public_profiles/{$this->profileId}/spotlights", [
            'spotlights' => [
                [
                    'media_id' => $mediaId,
                    'caption' => $caption,
                ],
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Snapchat spotlight failed: '.$response->body());
        }

        return $response->json();
    }

    private function uploadMedia(string $mediaUrl): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'snap_');
        file_put_contents($tempPath, file_get_contents($mediaUrl));

        $response = Http::withHeaders(['Authorization' => "Bearer {$this->accessToken}"])
            ->attach('file', file_get_contents($tempPath), basename($mediaUrl))
            ->post("https://adsapi.snapchat.com/v1/organizations/{$this->organizationId}/media");

        @unlink($tempPath);

        if (! $response->successful()) {
            throw new \RuntimeException('Snapchat media upload failed: '.$response->body());
        }

        return (string) ($response->json('media.0.id') ?? $response->json('id'));
    }

    public function refreshToken(): ?string
    {
        $response = Http::asForm()->post('https://accounts.snapchat.com/login/oauth2/access_token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.snapchat.client_id'),
            'client_secret' => config('services.snapchat.client_secret'),
            'refresh_token' => $this->accessToken,
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('Snapchat token refresh failed');

        return null;
    }
}
