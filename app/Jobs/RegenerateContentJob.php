<?php

namespace App\Jobs;

use App\Models\NewsPost;
use App\Services\GeminiService;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegenerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public function __construct(private int $postId, private string $instructions) {}

    public function handle(GeminiService $gemini, TelegramService $telegram): void
    {
        $post = NewsPost::findOrFail($this->postId);

        $currentCaptions = is_array($post->platform_captions) ? $post->platform_captions : [];

        $system = <<<'PROMPT'
Edit this news post per instructions. Preserve Hassan Almalki's voice:
- platform_captions.twitter_ar & instagram_ar: Saudi Najdi colloquial, first-person
- platform_captions.linkedin_en: inspiring English, first-person

Return ONLY valid JSON (no markdown) with any subset of: title_ar, title_en,
social_post_ar, social_post_en, content_ar, content_en, excerpt_ar, excerpt_en,
platform_captions (object with twitter_ar, instagram_ar, linkedin_en).

Keep [ARTICLE_URL_AR] / [ARTICLE_URL_EN] placeholders literal.
PROMPT;

        $user = "Current:\n"
            ."Title AR: {$post->title_ar}\n"
            ."Title EN: {$post->title_en}\n"
            ."Twitter AR: ".($currentCaptions['twitter_ar'] ?? $post->social_post_ar)."\n"
            ."Instagram AR: ".($currentCaptions['instagram_ar'] ?? $post->social_post_ar)."\n"
            ."LinkedIn EN: ".($currentCaptions['linkedin_en'] ?? $post->social_post_en)."\n\n"
            ."Edit: {$this->instructions}";

        $raw = $gemini->ask($system, $user);

        $text = preg_replace('/```json\s*|\s*```/', '', $raw);
        $data = json_decode(trim($text), true);

        if ($data) {
            $updates = array_intersect_key($data, array_flip([
                'title_ar', 'title_en', 'social_post_ar', 'social_post_en',
                'content_ar', 'content_en', 'excerpt_ar', 'excerpt_en',
            ]));

            if (isset($data['platform_captions']) && is_array($data['platform_captions'])) {
                $updates['platform_captions'] = array_merge($currentCaptions, array_intersect_key(
                    $data['platform_captions'],
                    array_flip(['twitter_ar', 'instagram_ar', 'linkedin_en'])
                ));
            }

            $post->update($updates);

            $telegram->sendNewsForApproval($post);
        } else {
            $telegram->notify('❌ فشل التعديل. حاول مرة أخرى.');
        }
    }
}
