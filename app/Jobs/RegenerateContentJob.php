<?php

namespace App\Jobs;

use App\Models\NewsPost;
use App\Services\ClaudeService;
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

    public function handle(ClaudeService $claude, TelegramService $telegram): void
    {
        $post = NewsPost::findOrFail($this->postId);

        $response = $claude->ask(
            'Edit this news post per instructions. Return same JSON keys: title_ar, title_en, social_post_ar, social_post_en, content_ar, content_en, excerpt_ar, excerpt_en.',
            "Current:\nTitle AR: {$post->title_ar}\nTitle EN: {$post->title_en}\nSocial AR: {$post->social_post_ar}\nSocial EN: {$post->social_post_en}\n\nEdit: {$this->instructions}"
        );

        $text = preg_replace('/```json\s*|\s*```/', '', $claude->extractText($response));
        $data = json_decode(trim($text), true);

        if ($data) {
            $post->update(array_intersect_key($data, array_flip([
                'title_ar', 'title_en', 'social_post_ar', 'social_post_en',
                'content_ar', 'content_en', 'excerpt_ar', 'excerpt_en',
            ])));

            $telegram->sendNewsForApproval($post);
        } else {
            $telegram->notify('❌ فشل التعديل. حاول مرة أخرى.');
        }
    }
}
