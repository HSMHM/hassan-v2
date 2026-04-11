<?php

namespace App\Console\Commands;

use App\Jobs\DiscoverNewsJob;
use Illuminate\Console\Command;

class DiscoverNews extends Command
{
    protected $signature = 'news:discover {--sync : Run synchronously instead of dispatching to queue}';

    protected $description = 'Manually trigger Claude AI news discovery';

    public function handle(): int
    {
        if ($this->option('sync')) {
            $this->info('Running DiscoverNewsJob synchronously...');
            DiscoverNewsJob::dispatchSync();
        } else {
            $this->info('Dispatching DiscoverNewsJob to queue...');
            DiscoverNewsJob::dispatch();
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
