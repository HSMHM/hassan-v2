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

// Warn a week before any platform token expires
Schedule::call(function () {
    $expiring = \App\Models\PlatformToken::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '>', now())
        ->where('expires_at', '<', now()->addDays(7))
        ->get();

    if ($expiring->isEmpty()) {
        return;
    }

    $lines = $expiring->map(fn ($t) => "• {$t->platform} — {$t->expires_at->format('Y-m-d')}")->implode("\n");
    $message = "⚠️ توكنات هذي المنصات تنتهي خلال أسبوع:\n\n{$lines}\n\nجدّدها من خلال لوحة التحكم أو أعد OAuth flow.";

    try {
        app(\App\Services\WhatsAppService::class)->sendMessage($message);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Mail::raw($message, fn ($m) =>
            $m->to('hassan@almalki.sa')->subject('⚠️ Platform Tokens Expiring')
        );
    }
})->weeklyOn(1, '09:00')->name('tokens:expiry-check');

// Daily alert for tokens that already expired
Schedule::call(function () {
    $expired = \App\Models\PlatformToken::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->get();

    if ($expired->isEmpty()) {
        return;
    }

    $platforms = $expired->pluck('platform')->implode(', ');
    $message = "🔴 توكنات منتهية الصلاحية: {$platforms}\nالنشر على هذي المنصات معطّل حتى تجدّدها!";

    try {
        app(\App\Services\WhatsAppService::class)->sendMessage($message);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Mail::raw($message, fn ($m) =>
            $m->to('hassan@almalki.sa')->subject('🔴 Platform Tokens Expired')
        );
    }
})->dailyAt('08:00')->name('tokens:expired-alert');
