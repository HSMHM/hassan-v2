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

    public function sharePost(string $text, ?string $articleUrl = null, ?string $articleTitle = null): array
    {
        $payload = [
            'author' => $this->personUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $text],
                    'shareMediaCategory' => $articleUrl ? 'ARTICLE' : 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        if ($articleUrl) {
            $payload['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [
                [
                    'status' => 'READY',
                    'originalUrl' => $articleUrl,
                    'title' => ['text' => $articleTitle ?? ''],
                ],
            ];
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
