<?php

namespace App\Jobs;

use App\Models\NewsPost;
use App\Services\NewsDiscoveryService;
use App\Services\OgImageService;
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

    public int $timeout = 300;

    public function handle(NewsDiscoveryService $discovery, TelegramService $telegram): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        if ($pending = NewsPost::where('status', 'pending')->latest('id')->first()) {
            $this->recordReason('pending_exists', ['post_id' => $pending->id]);
            $telegram->sendNewsForApproval($pending);

            return;
        }

        $items = $discovery->discoverNews();
        if (empty($items)) {
            $this->recordReason('no_items');
            $telegram->notify("🔍 ما لقيت أخبار جديدة هالمرة. جرّب مرة ثانية بعد دقيقة.");

            return;
        }

        $top = collect($items)->sortByDesc(fn ($i) => match ($i['significance'] ?? 'low') {
            'high' => 3,
            'medium' => 2,
            default => 1,
        })->first();

        if (NewsPost::where('source_url', $top['source_url'])->exists()) {
            $this->recordReason('duplicate_url', ['url' => $top['source_url']]);
            $telegram->notify('🔁 الخبر اللي لقاه منشور عندك من قبل — جرّب مرة ثانية.');

            return;
        }

        try {
            $content = $discovery->generateContent($top);
            $post = NewsPost::create([...$content, 'status' => 'pending', 'sent_to_whatsapp_at' => now()]);

            app(OgImageService::class)->ensureAll($post);

            $this->recordReason('created', ['post_id' => $post->id]);

            $telegram->sendNewsForApproval($post->refresh());
        } catch (\Throwable $e) {
            Log::error('News processing failed', ['error' => $e->getMessage()]);
            $this->recordReason('generate_failed', ['error' => $e->getMessage()]);
            $telegram->notify("❌ فشل معالجة الخبر:\n<code>".htmlspecialchars(mb_substr($e->getMessage(), 0, 500))."</code>");
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
