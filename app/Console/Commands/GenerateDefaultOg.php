<?php

namespace App\Console\Commands;

use App\Services\OgImageService;
use Illuminate\Console\Command;

class GenerateDefaultOg extends Command
{
    protected $signature = 'og:generate-default';

    protected $description = 'Generate the site-wide default Open Graph image at public/img/og-image.jpg';

    public function handle(OgImageService $ogs): int
    {
        $path = $ogs->generateDefault();
        $this->info("Generated: {$path}");

        return self::SUCCESS;
    }
}
