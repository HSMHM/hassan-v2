<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrafficBreakdown extends Widget
{
    protected string $view = 'filament.widgets.traffic-breakdown';

    protected static bool $isLazy = true;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $isAr = app()->getLocale() === 'ar';
        $since = Carbon::now()->subDays(30);

        $browsers = Cache::remember('traffic_browsers', 600, fn () =>
            PageVisit::query()
                ->select('browser', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $since)
                ->where('device', '!=', 'Bot')
                ->whereNotNull('browser')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->limit(6)
                ->pluck('count', 'browser')
                ->toArray()
        );

        $devices = Cache::remember('traffic_devices', 600, fn () =>
            PageVisit::query()
                ->select('device', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $since)
                ->whereNotNull('device')
                ->groupBy('device')
                ->orderByDesc('count')
                ->pluck('count', 'device')
                ->toArray()
        );

        $platforms = Cache::remember('traffic_platforms', 600, fn () =>
            PageVisit::query()
                ->select('platform', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $since)
                ->where('device', '!=', 'Bot')
                ->whereNotNull('platform')
                ->groupBy('platform')
                ->orderByDesc('count')
                ->limit(6)
                ->pluck('count', 'platform')
                ->toArray()
        );

        $topReferers = Cache::remember('traffic_referers', 600, fn () =>
            PageVisit::query()
                ->select('referer', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $since)
                ->where('device', '!=', 'Bot')
                ->whereNotNull('referer')
                ->where('referer', '!=', '')
                ->groupBy('referer')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'referer')
                ->toArray()
        );

        $totalBrowsers = array_sum($browsers);
        $totalDevices = array_sum($devices);
        $totalPlatforms = array_sum($platforms);

        return [
            'isAr' => $isAr,
            'browsers' => $browsers,
            'devices' => $devices,
            'platforms' => $platforms,
            'topReferers' => $topReferers,
            'totalBrowsers' => $totalBrowsers,
            'totalDevices' => $totalDevices,
            'totalPlatforms' => $totalPlatforms,
        ];
    }
}
