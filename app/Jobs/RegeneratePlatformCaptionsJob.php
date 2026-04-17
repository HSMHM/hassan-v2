<?php

namespace App\Jobs;

use App\Models\NewsPost;
use App\Services\NewsDiscoveryService;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RegeneratePlatformCaptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public function __construct(
        private int $postId,
        private array $platforms,
        private string $label = '',
    ) {}

    public function handle(NewsDiscoveryService $discovery, TelegramService $telegram): void
    {
        $post = NewsPost::findOrFail($this->postId);

        try {
            $merged = $discovery->regeneratePlatformCaptions($post, $this->platforms);
            $post->update(['platform_captions' => $merged]);
            $telegram->sendNewsForApproval($post->refresh());
        } catch (\Throwable $e) {
            Log::error('RegeneratePlatformCaptionsJob failed', [
                'post_id' => $this->postId,
                'platforms' => $this->platforms,
                'error' => $e->getMessage(),
            ]);
            $label = $this->label !== '' ? $this->label : implode(',', $this->platforms);
            $telegram->notify(
                "❌ فشل تجديد نبرة {$label}:\n<code>"
                .htmlspecialchars(mb_substr($e->getMessage(), 0, 300))
                .'</code>'
            );
        }
    }
}
