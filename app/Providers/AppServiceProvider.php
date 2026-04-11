<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(5)->by($request->ip())->response(function () {
                return back()->withErrors([
                    'message' => app()->getLocale() === 'ar'
                        ? 'لقد تجاوزت الحد المسموح. حاول لاحقاً.'
                        : 'Too many attempts. Please try again later.',
                ]);
            });
        });

        // Log slow queries (> 100ms) in local development so we can spot N+1 / unindexed reads
        if ($this->app->environment('local')) {
            DB::listen(function ($query) {
                if ($query->time > 100) {
                    Log::warning('Slow query ('.round($query->time, 2).'ms): '.$query->sql, [
                        'bindings' => $query->bindings,
                    ]);
                }
            });
        }
    }
}
