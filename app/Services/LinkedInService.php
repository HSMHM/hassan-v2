<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInService
{
    private string $accessToken;

    private string $personUrn;

    public function __construct()
    {
        $this->accessToken = config('services.linkedin.access_token');
        $this->personUrn = config('services.linkedin.person_urn');
    }

    public function sharePost(string $text, ?string $articleUrl = null, ?string $articleTitle = null, ?string $imageUrl = null): array
    {
        $mediaCategory = 'NONE';
        $media = [];

        if ($imageUrl) {
            $mediaCategory = 'IMAGE';
            $media[] = [
                'status' => 'READY',
                'originalUrl' => $imageUrl,
                'title' => ['text' => $articleTitle ?? ''],
            ];
        } elseif ($articleUrl) {
            $mediaCategory = 'ARTICLE';
            $media[] = [
                'status' => 'READY',
                'originalUrl' => $articleUrl,
                'title' => ['text' => $articleTitle ?? ''],
            ];
        }

        $payload = [
            'author' => $this->personUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $text],
                    'shareMediaCategory' => $mediaCategory,
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        if ($media) {
            $payload['specificContent']['com.linkedin.ugc.ShareContent']['media'] = $media;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ])->post('https://api.linkedin.com/v2/ugcPosts', $payload);

        if (! $response->successful()) {
            Log::error('LinkedIn post failed', ['body' => $response->body()]);
            throw new \RuntimeException('LinkedIn API error: '.$response->body());
        }

        return $response->json();
    }
}
