<?php

namespace App\Services;

use App\Models\NewsPost;
use Illuminate\Support\Facades\Log;

class SocialPublishService
{
    public function __construct(
        private TwitterService $twitter,
        private InstagramService $instagram,
        private LinkedInService $linkedin,
        private SnapchatService $snapchat,
        private WhatsAppService $whatsapp,
        private OgImageService $og,
    ) {}

    public function publish(NewsPost $post, array $platforms): array
    {
        $results = [];

        $socialAr = str_replace('[ARTICLE_URL_AR]', $post->getArticleUrl('ar'), $post->social_post_ar);
        $socialEn = str_replace('[ARTICLE_URL_EN]', $post->getArticleUrl('en'), $post->social_post_en);

        // Generate OG images if missing
        if (! $post->og_image) {
            try {
                $ogPath = $this->og->generateOg($post->title_ar, $post->source_title ?: 'almalki.sa', $post->id);
                $post->update(['og_image' => $ogPath]);
            } catch (\Throwable $e) {
                Log::warning('OG image (AR) generation failed', ['error' => $e->getMessage()]);
            }
        }

        if (! $post->og_image_en) {
            try {
                $ogEnPath = $this->og->generateOgEn($post->title_en, $post->source_title ?: 'almalki.sa', $post->id);
                $post->update(['og_image_en' => $ogEnPath]);
            } catch (\Throwable $e) {
                Log::warning('OG image (EN) generation failed', ['error' => $e->getMessage()]);
            }
        }

        $ogImageAr = $post->og_image
            ? (str_starts_with($post->og_image, 'http') ? $post->og_image : rtrim(config('app.url'), '/').$post->og_image)
            : null;

        $ogImageEn = $post->og_image_en
            ? (str_starts_with($post->og_image_en, 'http') ? $post->og_image_en : rtrim(config('app.url'), '/').$post->og_image_en)
            : $ogImageAr;

        // Twitter — Arabic only + image
        if (in_array('twitter', $platforms, true)) {
            $results['twitter'] = $ogImageAr
                ? $this->tryPublish('Twitter', fn () => $this->twitter->tweetWithImage($socialAr, $ogImageAr))
                : $this->tryPublish('Twitter', fn () => $this->twitter->tweet($socialAr));
            sleep(5);
        }

        // Instagram — Arabic + English caption + Arabic image
        if (in_array('instagram', $platforms, true)) {
            $igCaption = "{$socialAr}\n\n---\n\n{$socialEn}";
            $results['instagram'] = $ogImageAr
                ? $this->tryPublish('Instagram', fn () => $this->instagram->postImage($ogImageAr, $igCaption))
                : ['status' => 'skipped', 'reason' => 'OG image generation failed'];
            sleep(5);
        }

        // LinkedIn — English only + English image
        if (in_array('linkedin', $platforms, true)) {
            $liText = "{$post->title_en}\n\n{$post->excerpt_en}\n\n📖 {$post->getArticleUrl('en')}";
            $results['linkedin'] = $this->tryPublish('LinkedIn', fn () => $this->linkedin->sharePost($liText, $post->getArticleUrl('en'), $post->title_en, $ogImageEn));
            sleep(5);
        }

        // Snapchat — vertical story image
        if (in_array('snapchat', $platforms, true)) {
            try {
                $storyPath = $this->og->generateStory($post->title_ar, 'almalki.sa', $post->id);
                $storyUrl = rtrim(config('app.url'), '/').$storyPath;
                $results['snapchat'] = $this->tryPublish('Snapchat', fn () => $this->snapchat->postStory($storyUrl));
            } catch (\Throwable $e) {
                Log::warning('Snapchat story image failed', ['error' => $e->getMessage()]);
                $results['snapchat'] = ['status' => 'skipped', 'reason' => 'Story image failed'];
            }
            sleep(5);
        }

        // WhatsApp Status — Arabic image + link
        if (in_array('whatsapp', $platforms, true)) {
            $caption = "📖 {$post->getArticleUrl('ar')}";
            $results['whatsapp_status'] = $ogImageAr
                ? $this->tryPublish('WhatsApp Status', fn () => $this->whatsapp->postImageStatus($ogImageAr, $caption))
                : $this->tryPublish('WhatsApp Status', fn () => $this->whatsapp->postTextStatus($caption));
        }

        // Website
        $results['website'] = [
            'status' => 'published',
            'url_ar' => $post->getArticleUrl('ar'),
            'url_en' => $post->getArticleUrl('en'),
        ];

        return $results;
    }

    private function tryPublish(string $name, callable $action): array
    {
        try {
            $result = $action();
            Log::info("{$name} published successfully", ['result' => $result]);

            return ['status' => 'published', 'data' => $result];
        } catch (\Throwable $e) {
            Log::error("{$name} publish failed", ['error' => $e->getMessage()]);

            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}
