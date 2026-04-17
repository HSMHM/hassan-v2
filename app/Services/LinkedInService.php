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
        if ($imageUrl) {
            return $this->shareWithImage($text, $imageUrl);
        }

        return $this->shareTextOnly($text, $articleUrl, $articleTitle);
    }

    private function shareWithImage(string $text, string $imageUrl): array
    {
        $assetUrn = $this->uploadImage($imageUrl);

        $payload = [
            'author' => $this->personUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $text],
                    'shareMediaCategory' => 'IMAGE',
                    'media' => [
                        [
                            'status' => 'READY',
                            'media' => $assetUrn,
                        ],
                    ],
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        return $this->postUgc($payload);
    }

    private function shareTextOnly(string $text, ?string $articleUrl = null, ?string $articleTitle = null): array
    {
        $mediaCategory = 'NONE';
        $media = [];

        if ($articleUrl) {
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

        return $this->postUgc($payload);
    }

    private function uploadImage(string $imageUrl): string
    {
        $imageData = @file_get_contents($imageUrl);
        if ($imageData === false || $imageData === '') {
            throw new \RuntimeException("LinkedIn: failed to download image from {$imageUrl}");
        }

        $registerResponse = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ])->post('https://api.linkedin.com/v2/assets?action=registerUpload', [
            'registerUploadRequest' => [
                'owner' => $this->personUrn,
                'recipes' => ['urn:li:digitalmediaRecipe:feedshare-image'],
                'serviceRelationships' => [
                    [
                        'identifier' => 'urn:li:userGeneratedContent',
                        'relationshipType' => 'OWNER',
                    ],
                ],
                'supportedUploadMechanism' => ['SYNCHRONOUS_UPLOAD'],
            ],
        ]);

        if (! $registerResponse->successful()) {
            Log::error('LinkedIn register upload failed', ['body' => $registerResponse->body()]);
            throw new \RuntimeException('LinkedIn register upload failed: '.$registerResponse->body());
        }

        $registerData = $registerResponse->json();
        $uploadUrl = $registerData['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
        $assetUrn = $registerData['value']['asset'] ?? null;

        if (! $uploadUrl || ! $assetUrn) {
            throw new \RuntimeException('LinkedIn register upload: missing uploadUrl or asset in response');
        }

        $uploadResponse = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/octet-stream',
        ])->withBody($imageData, 'application/octet-stream')
            ->post($uploadUrl);

        if (! $uploadResponse->successful()) {
            Log::error('LinkedIn image upload failed', ['status' => $uploadResponse->status()]);
            throw new \RuntimeException('LinkedIn image upload failed: '.$uploadResponse->status());
        }

        Log::info('LinkedIn image uploaded', ['asset' => $assetUrn]);

        return (string) $assetUrn;
    }

    private function postUgc(array $payload): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json',
            'X-Restli-Protocol-Version' => '2.0.0',
        ])->post('https://api.linkedin.com/v2/ugcPosts', $payload);

        if (! $response->successful()) {
            Log::error('LinkedIn post failed', ['body' => $response->body()]);
            throw new \RuntimeException('LinkedIn API error: '.$response->body());
        }

        Log::info('LinkedIn published successfully', ['result' => $response->json()]);

        return $response->json();
    }
}
