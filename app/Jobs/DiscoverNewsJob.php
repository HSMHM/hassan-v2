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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DiscoverNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const REASON_CACHE_KEY = 'news:discovery:last';

    public int $tries = 2;

    public int $timeout = 180;

    public function handle(NewsDiscoveryService $discovery, TelegramService $telegram): void
    {
        if ($pending = NewsPost::where('status', 'pending')->latest('id')->first()) {
            $this->recordReason('pending_exists', ['post_id' => $pending->id]);

            return;
        }

        $items = $discovery->discoverNews();
        if (empty($items)) {
            $this->recordReason('no_items');

            return;
        }

        $top = collect($items)->sortByDesc(fn ($i) => match ($i['significance'] ?? 'low') {
            'high' => 3,
            'medium' => 2,
            default => 1,
        })->first();

        if (NewsPost::where('source_url', $top['source_url'])->exists()) {
            $this->recordReason('duplicate_url', ['url' => $top['source_url']]);

            return;
        }

        try {
            $content = $discovery->generateContent($top);
            // Create directly as `pending` — skip the `draft` intermediate so the
            // controller's check for the newly-created post always matches, even
            // if the Telegram notification below fails.
            $post = NewsPost::create([...$content, 'status' => 'pending', 'sent_to_whatsapp_at' => now()]);

            $this->recordReason('created', ['post_id' => $post->id]);

            $telegram->sendNewsForApproval($post);
        } catch (\Throwable $e) {
            Log::error('News processing failed', ['error' => $e->getMessage()]);
            $this->recordReason('generate_failed', ['error' => $e->getMessage()]);
            $telegram->notify("❌ فشل معالجة الخبر:\n<code>{$e->getMessage()}</code>");
        }
    }

    private function recordReason(string $reason, array $extra = []): void
    {
        Cache::put(self::REASON_CACHE_KEY, [
            'reason' => $reason,
            'at' => now()->toIso8601String(),
            ...$extra,
        ], now()->addMinutes(10));

        Log::info('DiscoverNewsJob exit', ['reason' => $reason, ...$extra]);
    }
}
