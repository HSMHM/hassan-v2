<?php

namespace App\Console\Commands;

use App\Jobs\PublishNewsJob;
use App\Models\NewsPost;
use Illuminate\Console\Command;

class PublishNews extends Command
{
    protected $signature = 'news:publish {id} {--platforms=twitter,instagram,linkedin,snapchat} {--sync}';

    protected $description = 'Publish a specific news post to selected platforms';

    public function handle(): int
    {
        $post = NewsPost::find($this->argument('id'));
        if (! $post) {
            $this->error("NewsPost #{$this->argument('id')} not found.");

            return self::FAILURE;
        }

        $platforms = array_filter(array_map('trim', explode(',', $this->option('platforms'))));
        $this->info("Publishing #{$post->id} to: ".implode(', ', $platforms));

        if ($this->option('sync')) {
            PublishNewsJob::dispatchSync($post->id, $platforms);
        } else {
            PublishNewsJob::dispatch($post->id, $platforms);
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
