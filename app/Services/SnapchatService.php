<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Snapchat Public Profile API.
 *
 * Story flow (per https://developers.snap.com/api/marketing-api/Public-Profile-API/ProfileAssetManagement):
 *   1. AES-256-CBC encrypt the image with a random key + IV.
 *   2. POST /public_profiles/{pid}/media   → returns media_id + add_path.
 *   3. Multipart POST {add_path} action=ADD with the encrypted file.
 *   4. Multipart POST {add_path} action=FINALIZE.
 *   5. POST /public_profiles/{pid}/stories with {media_id}.
 */
class SnapchatService
{
    private const API_BASE = 'https://businessapi.snapchat.com';

    private string $accessToken;

    private string $profileId;

    public function __construct()
    {
        $this->accessToken = (string) config('services.snapchat.access_token');
        $this->profileId = (string) config('services.snapchat.profile_id');
    }

    public function postStory(string $mediaUrl, string $caption = ''): array
    {
        $this->assertConfigured();

        $mediaId = $this->uploadImage($mediaUrl);

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->post(self::API_BASE."/v1/public_profiles/{$this->profileId}/stories", [
                'media_id' => $mediaId,
            ]);

        if (! $response->successful()) {
            Log::error('Snapchat story post failed', ['body' => $response->body()]);
            throw new RuntimeException('Snapchat story post failed: '.$response->body());
        }

        return $response->json() ?? [];
    }

    private function uploadImage(string $mediaUrl): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'snap_');
        $encPath = $tempPath.'.enc';

        try {
            $plain = @file_get_contents($mediaUrl);
            if ($plain === false || $plain === '') {
                throw new RuntimeException("Failed to download media from {$mediaUrl}");
            }
            file_put_contents($tempPath, $plain);

            // 1. Encrypt AES-256-CBC
            $key = random_bytes(32);
            $iv = random_bytes(16);
            $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            if ($cipher === false) {
                throw new RuntimeException('AES encryption failed');
            }
            file_put_contents($encPath, $cipher);

            // 2. Create media container
            $create = Http::withToken($this->accessToken)
                ->acceptJson()
                ->asJson()
                ->post(self::API_BASE."/v1/public_profiles/{$this->profileId}/media", [
                    'type' => 'IMAGE',
                    'name' => 'og-story-'.now()->format('Ymd-His'),
                    'key' => base64_encode($key),
                    'iv' => base64_encode($iv),
                ]);

            if (! $create->successful()) {
                Log::error('Snapchat create media failed', ['body' => $create->body()]);
                throw new RuntimeException('Snapchat create media failed: '.$create->body());
            }

            $body = $create->json();
            $mediaId = $body['media']['id'] ?? $body['id'] ?? null;
            $addPath = $body['media']['add_path'] ?? $body['add_path'] ?? null;

            if (! $mediaId || ! $addPath) {
                throw new RuntimeException('Snapchat create media: missing id/add_path in response — '.$create->body());
            }

            $uploadUrl = str_starts_with($addPath, 'http') ? $addPath : self::API_BASE.'/'.ltrim($addPath, '/');

            // 3. Multipart ADD
            $add = Http::withToken($this->accessToken)
                ->asMultipart()
                ->attach('file', file_get_contents($encPath), 'image.enc.jpg')
                ->post($uploadUrl, [
                    ['name' => 'action', 'contents' => 'ADD'],
                    ['name' => 'part_number', 'contents' => '1'],
                ]);

            // Workaround: Laravel Http merges `attach` with multipart fields — build via Guzzle-style call
            if (! $add->successful()) {
                // Fallback: plain guzzle-style multipart
                $add = Http::withToken($this->accessToken)
                    ->attach('file', file_get_contents($encPath), 'image.enc.jpg')
                    ->post($uploadUrl, [
                        'action' => 'ADD',
                        'part_number' => '1',
                    ]);
            }

            if (! $add->successful()) {
                Log::error('Snapchat multipart ADD failed', ['body' => $add->body(), 'url' => $uploadUrl]);
                throw new RuntimeException('Snapchat upload ADD failed: '.$add->body());
            }

            // 4. Multipart FINALIZE
            $finalize = Http::withToken($this->accessToken)
                ->asMultipart()
                ->post($uploadUrl, [
                    ['name' => 'action', 'contents' => 'FINALIZE'],
                ]);

            if (! $finalize->successful()) {
                Log::error('Snapchat multipart FINALIZE failed', ['body' => $finalize->body()]);
                throw new RuntimeException('Snapchat upload FINALIZE failed: '.$finalize->body());
            }

            return (string) $mediaId;
        } finally {
            @unlink($tempPath);
            @unlink($encPath);
        }
    }

    /**
     * Exchange the stored refresh_token for a new access_token.
     * Returns the full response body so callers can see expiry + any new refresh_token.
     */
    public function refreshToken(): ?array
    {
        $refresh = (string) config('services.snapchat.refresh_token');
        if (! $refresh) {
            Log::error('Snapchat refresh failed: SNAPCHAT_REFRESH_TOKEN not set');

            return null;
        }

        $response = Http::asForm()->post('https://accounts.snapchat.com/login/oauth2/access_token', [
            'grant_type' => 'refresh_token',
            'client_id' => config('services.snapchat.client_id'),
            'client_secret' => config('services.snapchat.client_secret'),
            'refresh_token' => $refresh,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Snapchat token refresh failed', ['body' => $response->body()]);

        return null;
    }

    private function assertConfigured(): void
    {
        $missing = [];
        if (! $this->accessToken) {
            $missing[] = 'SNAPCHAT_ACCESS_TOKEN';
        }
        if (! $this->profileId) {
            $missing[] = 'SNAPCHAT_PROFILE_ID';
        }
        if ($missing) {
            throw new RuntimeException('Snapchat not configured: '.implode(', ', $missing));
        }
    }
}
