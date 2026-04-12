<?php

namespace App\Console\Commands;

use App\Models\PlatformToken;
use App\Services\InstagramService;
use App\Services\SnapchatService;
use Illuminate\Console\Command;

class RefreshTokens extends Command
{
    protected $signature = 'tokens:refresh {platform? : instagram|snapchat (omit to refresh all)}';

    protected $description = 'Refresh expiring OAuth tokens for Instagram, Snapchat (and any other supported platforms)';

    public function handle(): int
    {
        $platform = $this->argument('platform');
        $targets = $platform ? [$platform] : ['instagram', 'snapchat'];

        foreach ($targets as $p) {
            $this->info("Refreshing {$p}...");
            $newToken = match ($p) {
                'instagram' => app(InstagramService::class)->refreshToken(),
                'snapchat' => app(SnapchatService::class)->refreshToken(),
                default => null,
            };

            if (! $newToken) {
                $this->warn("  → no token returned for {$p}");

                continue;
            }

            PlatformToken::saveToken($p, $newToken, null, now()->addDays(60));

            $this->info("  → stored new token for {$p}");
        }

        return self::SUCCESS;
    }
}
