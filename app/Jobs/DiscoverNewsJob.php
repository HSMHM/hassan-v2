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

    public function __construct(public ?string $topic = null) {}

    public function handle(NewsDiscoveryService $discovery, TelegramService $telegram): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        if ($pending = NewsPost::where('status', 'pending')->latest('id')->first()) {
            $this->recordReason('pending_exists', ['post_id' => $pending->id]);
            $telegram->sendNewsForApproval($pending);

            return;
        }

        $items = $discovery->discoverNews($this->topic);
        if (empty($items)) {
            $this->recordReason('no_items');
            $telegram->notify("🔍 ما لقيت أخبار جديدة هالمرة. جرّب مرة ثانية بعد دقيقة.");

            return;
        }

        $sorted = collect($items)->sortByDesc(fn ($i) => match ($i['significance'] ?? 'low') {
            'high' => 3,
            'medium' => 2,
            default => 1,
        })->values();

        $top = null;
        foreach ($sorted as $candidate) {
            $url = $candidate['source_url'] ?? null;
            if (! $url) {
                continue;
            }

            if (NewsPost::where('source_url', $url)->exists()) {
                continue;
            }

            try {
                $status = \Illuminate\Support\Facades\Http::timeout(8)
                    ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                    ->get($url)
                    ->status();
            } catch (\Throwable $e) {
                Log::info('Candidate URL unreachable', ['url' => $url, 'error' => $e->getMessage()]);
                continue;
            }

            if ($status >= 200 && $status < 400) {
                $top = $candidate;
                break;
            }

            Log::info('Candidate URL non-2xx, trying next', ['url' => $url, 'status' => $status]);
        }

        if (! $top) {
            $this->recordReason('no_valid_url');
            $telegram->notify('🔗 كل الـURLs اللي رجّعها Gemini هالمرة ما اشتغلت. جرّب مرة ثانية.');

            return;
        }

        try {
            $content = $discovery->generateContent($top, $this->topic);
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
