<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    private string $apiKey;

    private string $apiSecret;

    private string $accessToken;

    private string $accessTokenSecret;

    public function __construct()
    {
        $this->apiKey = config('services.twitter.api_key');
        $this->apiSecret = config('services.twitter.api_secret');
        $this->accessToken = config('services.twitter.access_token');
        $this->accessTokenSecret = config('services.twitter.access_token_secret');
    }

    public function tweet(string $text): array
    {
        $url = 'https://api.x.com/2/tweets';
        $oauthHeader = $this->buildOAuthHeader('POST', $url, []);

        $response = Http::withHeaders([
            'Authorization' => $oauthHeader,
            'Content-Type' => 'application/json',
        ])->post($url, ['text' => $text]);

        if (! $response->successful()) {
            Log::error('Twitter post failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Twitter API error: '.$response->body());
        }

        return $response->json('data') ?? [];
    }

    public function tweetWithImage(string $text, string $imagePath): array
    {
        $mediaId = $this->uploadMedia($imagePath);

        $url = 'https://api.x.com/2/tweets';
        $oauthHeader = $this->buildOAuthHeader('POST', $url, []);

        $response = Http::withHeaders([
            'Authorization' => $oauthHeader,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'text' => $text,
            'media' => ['media_ids' => [$mediaId]],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Twitter tweet+image failed: '.$response->body());
        }

        return $response->json('data') ?? [];
    }

    private function uploadMedia(string $imagePath): string
    {
        $url = 'https://upload.twitter.com/1.1/media/upload.json';
        $tempPath = null;

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $tempPath = tempnam(sys_get_temp_dir(), 'tw_img_');
            file_put_contents($tempPath, file_get_contents($imagePath));
            $imagePath = $tempPath;
        }

        $oauthHeader = $this->buildOAuthHeader('POST', $url, []);

        $response = Http::withHeaders(['Authorization' => $oauthHeader])
            ->attach('media', file_get_contents($imagePath), 'image.jpg')
            ->post($url);

        if ($tempPath) {
            @unlink($tempPath);
        }

        if (! $response->successful()) {
            throw new \RuntimeException('Twitter media upload failed: '.$response->body());
        }

        return (string) $response->json('media_id_string');
    }

    private function buildOAuthHeader(string $method, string $url, array $extraParams = []): string
    {
        $oauth = [
            'oauth_consumer_key' => $this->apiKey,
            'oauth_nonce' => bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => (string) time(),
            'oauth_token' => $this->accessToken,
            'oauth_version' => '1.0',
        ];

        $allParams = array_merge($oauth, $extraParams);
        ksort($allParams);

        $baseString = strtoupper($method).'&'
            .rawurlencode($url).'&'
            .rawurlencode(http_build_query($allParams, '', '&', PHP_QUERY_RFC3986));

        $signingKey = rawurlencode($this->apiSecret).'&'.rawurlencode($this->accessTokenSecret);
        $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));

        $parts = [];
        foreach ($oauth as $key => $value) {
            $parts[] = rawurlencode($key).'="'.rawurlencode($value).'"';
        }

        return 'OAuth '.implode(', ', $parts);
    }
}
