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
    ) {}

    public function publish(NewsPost $post, array $platforms): array
    {
        $results = [];

        $socialAr = str_replace('[ARTICLE_URL_AR]', $post->getArticleUrl('ar'), $post->social_post_ar);
        $socialEn = str_replace('[ARTICLE_URL_EN]', $post->getArticleUrl('en'), $post->social_post_en);

        $ogImage = $post->og_image
            ? (str_starts_with($post->og_image, 'http') ? $post->og_image : rtrim(config('app.url'), '/').$post->og_image)
            : null;

        if (in_array('twitter', $platforms, true)) {
            $results['twitter_ar'] = $this->tryPublish('Twitter AR', fn () => $this->twitter->tweet($socialAr));
            sleep(5);
            $results['twitter_en'] = $this->tryPublish('Twitter EN', fn () => $this->twitter->tweet($socialEn));
            sleep(5);
        }

        if (in_array('instagram', $platforms, true)) {
            if ($ogImage) {
                $igCaption = "{$socialAr}\n\n---\n\n{$socialEn}";
                $results['instagram'] = $this->tryPublish('Instagram', fn () => $this->instagram->postImage($ogImage, $igCaption));
                sleep(5);
            } else {
                $results['instagram'] = ['status' => 'skipped', 'reason' => 'No image available'];
            }
        }

        if (in_array('linkedin', $platforms, true)) {
            $results['linkedin_ar'] = $this->tryPublish('LinkedIn AR', fn () => $this->linkedin->sharePost($socialAr, $post->getArticleUrl('ar'), $post->title_ar));
            sleep(5);
            $results['linkedin_en'] = $this->tryPublish('LinkedIn EN', fn () => $this->linkedin->sharePost($socialEn, $post->getArticleUrl('en'), $post->title_en));
            sleep(5);
        }

        if (in_array('snapchat', $platforms, true)) {
            if ($ogImage) {
                $results['snapchat'] = $this->tryPublish('Snapchat', fn () => $this->snapchat->postStory($ogImage, $post->title_ar));
                sleep(5);
            } else {
                $results['snapchat'] = ['status' => 'skipped', 'reason' => 'No image available'];
            }
        }

        if (in_array('whatsapp', $platforms, true)) {
            $statusText = "📢 {$post->title_ar}\n\n{$post->excerpt_ar}\n\n📖 {$post->getArticleUrl('ar')}";
            $results['whatsapp_status'] = $this->tryPublish('WhatsApp Status', fn () => $this->whatsapp->postTextStatus($statusText));
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
