<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {--delete : Remove webhook instead of setting it}';

    protected $description = 'Set or remove the Telegram bot webhook URL';

    public function handle(TelegramService $telegram): int
    {
        if ($this->option('delete')) {
            $result = $telegram->deleteWebhook();
            $this->info('Webhook removed: '.json_encode($result));

            return self::SUCCESS;
        }

        $url = rtrim(config('app.url'), '/').'/api/webhooks/telegram';
        $secret = config('services.telegram.webhook_secret');

        if (! $secret) {
            $this->error('TELEGRAM_WEBHOOK_SECRET is not set in .env');

            return self::FAILURE;
        }

        $result = $telegram->setWebhook($url, $secret);

        if ($result['ok'] ?? false) {
            $this->info("Webhook set successfully: {$url}");
        } else {
            $this->error('Failed: '.json_encode($result));
        }

        return self::SUCCESS;
    }
}
