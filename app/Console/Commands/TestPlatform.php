<?php

namespace App\Console\Commands;

use App\Services\InstagramService;
use App\Services\LinkedInService;
use App\Services\SnapchatService;
use App\Services\TwitterService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class TestPlatform extends Command
{
    protected $signature = 'news:test-platform {platform : twitter|instagram|linkedin|snapchat|whatsapp}';

    protected $description = 'Send a test post to a single platform to verify connectivity';

    public function handle(): int
    {
        $platform = strtolower($this->argument('platform'));
        $message = '🧪 Test post from Hassan Almalki news automation - '.now()->toIso8601String();

        try {
            $result = match ($platform) {
                'twitter' => app(TwitterService::class)->tweet($message),
                'instagram' => app(InstagramService::class)->postImage(
                    rtrim(config('app.url'), '/').'/img/og-image.jpg',
                    $message,
                ),
                'linkedin' => app(LinkedInService::class)->sharePost($message),
                'snapchat' => app(SnapchatService::class)->postStory(
                    rtrim(config('app.url'), '/').'/img/og-image.jpg',
                    $message,
                ),
                'whatsapp' => app(WhatsAppService::class)->sendMessage($message),
                default => throw new \InvalidArgumentException("Unknown platform: {$platform}"),
            };
        } catch (\Throwable $e) {
            $this->error("FAILED: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('OK');
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
