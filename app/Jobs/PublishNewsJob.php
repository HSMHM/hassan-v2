<?php

namespace App\Jobs;

use App\Models\NewsPost;
use App\Services\OgImageService;
use App\Services\SocialPublishService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 300;

    public function __construct(
        private int $postId,
        private array $platforms = ['twitter', 'instagram', 'linkedin', 'snapchat', 'whatsapp'],
    ) {}

    public function handle(SocialPublishService $publisher, WhatsAppService $wa, OgImageService $og): void
    {
        $post = NewsPost::findOrFail($this->postId);

        if ($post->status === 'published') {
            return;
        }

        $post->update(['status' => 'publishing']);

        // Auto-generate the horizontal OG image if missing.
        // The vertical story image is generated on-demand inside SocialPublishService.
        if (! $post->og_image) {
            try {
                $ogPath = $og->generateOg(
                    $post->title_ar,
                    $post->source_title ?: 'almalki.sa',
                    $post->id
                );
                $post->update(['og_image' => $ogPath]);
                $post->refresh();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('OG image generation failed', [
                    'post_id' => $post->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $results = $publisher->publish($post, $this->platforms);

        $allOk = collect($results)->every(fn ($r) => ($r['status'] ?? '') === 'published');
        $anyOk = collect($results)->contains(fn ($r) => ($r['status'] ?? '') === 'published');

        $post->update([
            'platform_status' => $results,
            'status' => $allOk ? 'published' : ($anyOk ? 'partial' : 'failed'),
            'published_at' => now(),
        ]);

        $msg = $allOk ? "✅ تم النشر بنجاح!\n\n" : "⚠️ نتائج النشر:\n\n";
        foreach ($results as $platform => $r) {
            $icon = ($r['status'] ?? '') === 'published' ? '✅' : '❌';
            $msg .= "{$icon} {$platform}";
            if (($r['status'] ?? '') === 'failed') {
                $msg .= " — {$r['error']}";
            }
            $msg .= "\n";
        }
        $msg .= "\n📖 AR: {$post->getArticleUrl('ar')}";
        $msg .= "\n📖 EN: {$post->getArticleUrl('en')}";

        $wa->sendMessage($msg);
    }
}
