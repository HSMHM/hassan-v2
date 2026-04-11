<?php

use App\Jobs\DiscoverNewsJob;
use App\Services\InstagramService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (env('NEWS_DISCOVERY_ENABLED', false)) {
    Schedule::job(new DiscoverNewsJob)
        ->everySixHours()
        ->withoutOverlapping()
        ->name('news:discover');
}

Schedule::call(fn () => app(InstagramService::class)->refreshToken())
    ->monthly()
    ->name('instagram:refresh-token');

// Monthly backup + cleanup (spatie/laravel-backup)
Schedule::command('backup:clean')->monthlyOn(1, '01:00')->name('backup:clean');
Schedule::command('backup:run')->monthlyOn(1, '02:00')->name('backup:run');
