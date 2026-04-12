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

        // OG image — generate if missing so Instagram/Snapchat never skip.
        if (! $post->og_image) {
            try {
                $ogPath = $this->og->generateOg($post->title_ar, $post->source_title ?: 'almalki.sa', $post->id);
                $post->update(['og_image' => $ogPath]);
            } catch (\Throwable $e) {
                Log::warning('OG image fallback generation failed', ['error' => $e->getMessage()]);
            }
        }
        $ogImage = $post->og_image
            ? (str_starts_with($post->og_image, 'http') ? $post->og_image : rtrim(config('app.url'), '/').$post->og_image)
            : null;

        if (in_array('twitter', $platforms, true)) {
            $results['twitter_ar'] = $ogImage
                ? $this->tryPublish('Twitter AR', fn () => $this->twitter->tweetWithImage($socialAr, $ogImage))
                : $this->tryPublish('Twitter AR', fn () => $this->twitter->tweet($socialAr));
            sleep(5);
            $results['twitter_en'] = $ogImage
                ? $this->tryPublish('Twitter EN', fn () => $this->twitter->tweetWithImage($socialEn, $ogImage))
                : $this->tryPublish('Twitter EN', fn () => $this->twitter->tweet($socialEn));
            sleep(5);
        }

        if (in_array('instagram', $platforms, true)) {
            $igCaption = "{$socialAr}\n\n---\n\n{$socialEn}";
            $results['instagram'] = $ogImage
                ? $this->tryPublish('Instagram', fn () => $this->instagram->postImage($ogImage, $igCaption))
                : ['status' => 'skipped', 'reason' => 'OG image generation failed'];
            sleep(5);
        }

        if (in_array('linkedin', $platforms, true)) {
            $liTextAr = "{$post->title_ar}\n\n{$post->excerpt_ar}\n\n📖 {$post->getArticleUrl('ar')}";
            $liTextEn = "{$post->title_en}\n\n{$post->excerpt_en}\n\n📖 {$post->getArticleUrl('en')}";

            $results['linkedin_ar'] = $this->tryPublish('LinkedIn AR', fn () => $this->linkedin->sharePost($liTextAr, $post->getArticleUrl('ar'), $post->title_ar, $ogImage));
            sleep(5);
            $results['linkedin_en'] = $this->tryPublish('LinkedIn EN', fn () => $this->linkedin->sharePost($liTextEn, $post->getArticleUrl('en'), $post->title_en, $ogImage));
            sleep(5);
        }

        if (in_array('snapchat', $platforms, true)) {
            // Snapchat stories require 9:16 vertical image.
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

        if (in_array('whatsapp', $platforms, true)) {
            $caption = "📖 {$post->getArticleUrl('ar')}";
            $results['whatsapp_status'] = $ogImage
                ? $this->tryPublish('WhatsApp Status', fn () => $this->whatsapp->postImageStatus($ogImage, $caption))
                : $this->tryPublish('WhatsApp Status', fn () => $this->whatsapp->postTextStatus($caption));
        }

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
