<?php

namespace App\Jobs;

use App\Models\NewsPost;
use App\Services\OgImageService;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePostImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(
        private int $postId,
        private string $mode = 'ensure',
        private bool $resendPreview = false,
    ) {}

    public function handle(OgImageService $og, TelegramService $telegram): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $post = NewsPost::find($this->postId);
        if (! $post) {
            return;
        }

        if ($this->mode === 'regenerate') {
            $og->regenerateAll($post);
        } else {
            $og->ensureAll($post);
        }

        if ($this->resendPreview) {
            $telegram->sendNewsForApproval($post->refresh());
        }
    }
}
