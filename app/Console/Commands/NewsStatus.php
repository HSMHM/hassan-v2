<?php

namespace App\Console\Commands;

use App\Models\NewsPost;
use Illuminate\Console\Command;

class NewsStatus extends Command
{
    protected $signature = 'news:status {--limit=10}';

    protected $description = 'List recent news posts and their platform statuses';

    public function handle(): int
    {
        $posts = NewsPost::latest()->limit((int) $this->option('limit'))->get();

        if ($posts->isEmpty()) {
            $this->info('No news posts found.');

            return self::SUCCESS;
        }

        $rows = $posts->map(fn (NewsPost $p) => [
            $p->id,
            mb_substr($p->title_ar ?? '', 0, 40),
            $p->status,
            $p->sent_to_whatsapp_at?->diffForHumans() ?? '-',
            $p->published_at?->diffForHumans() ?? '-',
        ])->all();

        $this->table(['ID', 'Title (AR)', 'Status', 'Sent', 'Published'], $rows);

        return self::SUCCESS;
    }
}
