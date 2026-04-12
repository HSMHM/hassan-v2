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

class DiscoverNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public function handle(NewsDiscoveryService $discovery, TelegramService $telegram): void
    {
        if (NewsPost::where('status', 'pending')->exists()) {
            Log::info('Skipping: pending post exists');

            return;
        }

        $items = $discovery->discoverNews();
        if (empty($items)) {
            return;
        }

        $top = collect($items)->sortByDesc(fn ($i) => match ($i['significance'] ?? 'low') {
            'high' => 3,
            'medium' => 2,
            default => 1,
        })->first();

        if (NewsPost::where('source_url', $top['source_url'])->exists()) {
            return;
        }

        try {
            $content = $discovery->generateContent($top);
            $post = NewsPost::create([...$content, 'status' => 'draft']);

            $telegram->sendNewsForApproval($post);
        } catch (\Throwable $e) {
            Log::error('News processing failed', ['error' => $e->getMessage()]);
            $telegram->notify("❌ فشل معالجة الخبر:\n<code>{$e->getMessage()}</code>");
        }
    }
}
