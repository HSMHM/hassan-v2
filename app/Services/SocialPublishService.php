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
        $baseUrl = rtrim(config('app.url'), '/');

        $captions = $this->resolveCaptions($post);

        // Generate all image formats
        $this->ensureImages($post);

        $ogImageAr = $post->og_image ? $baseUrl.$post->og_image : null;
        $ogImageEn = $post->og_image_en ? $baseUrl.$post->og_image_en : $ogImageAr;
        $tallImage = $post->tall_image ? $baseUrl.$post->tall_image : $ogImageAr;

        // Twitter — Najdi Arabic + horizontal image
        if (in_array('twitter', $platforms, true)) {
            $results['twitter'] = $ogImageAr
                ? $this->tryPublish('Twitter', fn () => $this->twitter->tweetWithImage($captions['twitter_ar'], $ogImageAr))
                : $this->tryPublish('Twitter', fn () => $this->twitter->tweet($captions['twitter_ar']));
            sleep(5);
        }

        // Instagram — tall image + Najdi Arabic caption (same voice as Twitter, longer allowed)
        if (in_array('instagram', $platforms, true)) {
            $igImage = $tallImage ?? $ogImageAr;
            $results['instagram'] = $igImage
                ? $this->tryPublish('Instagram', fn () => $this->instagram->postImage($igImage, $captions['instagram_ar']))
                : ['status' => 'skipped', 'reason' => 'Image generation failed'];
            sleep(5);
        }

        // LinkedIn — English inspiring voice + horizontal English image
        if (in_array('linkedin', $platforms, true)) {
            $results['linkedin'] = $this->tryPublish('LinkedIn', fn () => $this->linkedin->sharePost($captions['linkedin_en'], $post->getArticleUrl('en'), $post->title_en, $ogImageEn));
            sleep(5);
        }

        // Snapchat — vertical story image
        if (in_array('snapchat', $platforms, true)) {
            try {
                $storyPath = $this->og->generateStory($post->title_ar, 'almalki.sa', $post->id);
                $storyUrl = $baseUrl.$storyPath;
                $results['snapchat'] = $this->tryPublish('Snapchat', fn () => $this->snapchat->postStory($storyUrl));
            } catch (\Throwable $e) {
                Log::warning('Snapchat story image failed', ['error' => $e->getMessage()]);
                $results['snapchat'] = ['status' => 'skipped', 'reason' => 'Story image failed'];
            }
            sleep(5);
        }

        // WhatsApp Status — tall image + link
        if (in_array('whatsapp', $platforms, true)) {
            $caption = "📖 {$post->getArticleUrl('ar')}";
            $waImage = $tallImage ?? $ogImageAr;
            $results['whatsapp_status'] = $waImage
                ? $this->tryPublish('WhatsApp Status', fn () => $this->whatsapp->postImageStatus($waImage, $caption))
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

    /**
     * Resolve final captions per platform with URL placeholders replaced.
     * Falls back to social_post_ar/en when platform_captions is missing.
     */
    private function resolveCaptions(NewsPost $post): array
    {
        $urlAr = $post->getArticleUrl('ar');
        $urlEn = $post->getArticleUrl('en');

        $platform = $post->platform_captions ?? [];
        $fallbackAr = (string) $post->social_post_ar;

        $resolve = fn (?string $text, string $url, string $placeholder) => $text
            ? str_replace($placeholder, $url, $text)
            : null;

        return [
            'twitter_ar' => $resolve($platform['twitter_ar'] ?? null, $urlAr, '[ARTICLE_URL_AR]')
                ?? str_replace('[ARTICLE_URL_AR]', $urlAr, $fallbackAr),
            'instagram_ar' => $resolve($platform['instagram_ar'] ?? null, $urlAr, '[ARTICLE_URL_AR]')
                ?? str_replace('[ARTICLE_URL_AR]', $urlAr, $fallbackAr),
            'linkedin_en' => $resolve($platform['linkedin_en'] ?? null, $urlEn, '[ARTICLE_URL_EN]')
                ?? ($post->title_en."\n\n".$post->excerpt_en."\n\n📖 ".$urlEn),
        ];
    }

    private function ensureImages(NewsPost $post): void
    {
        $subtitle = $post->source_title ?: 'almalki.sa';

        if (! $post->og_image) {
            try {
                $post->update(['og_image' => $this->og->generateOg($post->title_ar, $subtitle, $post->id)]);
            } catch (\Throwable $e) {
                Log::warning('OG image (AR) failed', ['error' => $e->getMessage()]);
            }
        }

        if (! $post->og_image_en) {
            try {
                $post->update(['og_image_en' => $this->og->generateOgEn($post->title_en, $subtitle, $post->id)]);
            } catch (\Throwable $e) {
                Log::warning('OG image (EN) failed', ['error' => $e->getMessage()]);
            }
        }

        if (! $post->tall_image) {
            try {
                $post->update(['tall_image' => $this->og->generateTall($post->title_ar, $subtitle, $post->id)]);
            } catch (\Throwable $e) {
                Log::warning('Tall image failed', ['error' => $e->getMessage()]);
            }
        }

        if (! $post->tall_image_en) {
            try {
                $post->update(['tall_image_en' => $this->og->generateTallEn($post->title_en, $subtitle, $post->id)]);
            } catch (\Throwable $e) {
                Log::warning('Tall image (EN) failed', ['error' => $e->getMessage()]);
            }
        }

        $post->refresh();
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
