<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeAll extends Command
{
    protected $signature = 'optimize:all
                            {--clear : Clear all caches instead of building them}';

    protected $description = 'Build (or clear) every Laravel + Filament cache in one go for production deploy';

    public function handle(): int
    {
        $mode = $this->option('clear') ? 'clear' : 'cache';

        $this->info($mode === 'cache'
            ? 'Building config / route / view / event / icon caches...'
            : 'Clearing all caches...');

        $commands = [
            "config:$mode",
            "route:$mode",
            "view:$mode",
            "event:$mode",
        ];

        foreach ($commands as $cmd) {
            $this->line("→ php artisan $cmd");
            Artisan::call($cmd);
            $this->line(trim(Artisan::output()));
        }

        // Filament icons: only the cache variant exists
        if ($mode === 'cache') {
            $this->line('→ php artisan icons:cache');
            try {
                Artisan::call('icons:cache');
                $this->line(trim(Artisan::output()));
            } catch (\Throwable $e) {
                $this->warn('icons:cache not available — skipping');
            }
        }

        // Warm the all_site_settings cache so the first real request skips the DB hit
        if ($mode === 'cache') {
            $this->line('→ warming all_site_settings cache');
            \App\Models\SiteSetting::flushCache();
            \Illuminate\Support\Facades\Cache::rememberForever(
                'all_site_settings',
                fn () => \App\Models\SiteSetting::pluck('value', 'key')->toArray()
            );
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
