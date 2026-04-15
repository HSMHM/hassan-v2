<?php

namespace App\Console\Commands;

use App\Services\SnapchatService;
use Illuminate\Console\Command;

class SnapchatRefresh extends Command
{
    protected $signature = 'snapchat:refresh';

    protected $description = 'Use the stored SNAPCHAT_REFRESH_TOKEN to fetch a fresh access token. Prints the new values to paste into .env.';

    public function handle(SnapchatService $snap): int
    {
        $data = $snap->refreshToken();

        if (! $data) {
            $this->error('Refresh failed — check SNAPCHAT_REFRESH_TOKEN and the logs.');

            return self::FAILURE;
        }

        $this->info('✅ Token refreshed. Update .env:');
        $this->newLine();
        $this->line('SNAPCHAT_ACCESS_TOKEN='.($data['access_token'] ?? ''));

        if (! empty($data['refresh_token'])) {
            $this->line('SNAPCHAT_REFRESH_TOKEN='.$data['refresh_token']);
            $this->comment('(a new refresh_token was issued — replace the old one)');
        }

        $this->newLine();
        $this->comment('Then run: php artisan config:cache');
        $this->comment('Expires in '.($data['expires_in'] ?? '?').' seconds.');

        return self::SUCCESS;
    }
}
