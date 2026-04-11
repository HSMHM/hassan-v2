<?php

namespace App\Filament\Widgets;

use App\Models\PageVisit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class VisitorStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $isAr = app()->getLocale() === 'ar';

        $todayVisits = Cache::remember('stat_visits_today', 300, fn () => PageVisit::whereDate('created_at', Carbon::today())->count());
        $weekVisits = Cache::remember('stat_visits_week', 300, fn () => PageVisit::where('created_at', '>=', Carbon::now()->subDays(7))->count());
        $monthVisits = Cache::remember('stat_visits_month', 300, fn () => PageVisit::where('created_at', '>=', Carbon::now()->subDays(30))->count());
        $totalVisits = Cache::remember('stat_visits_total', 300, fn () => PageVisit::count());
        $uniqueToday = Cache::remember('stat_unique_today', 300, fn () => PageVisit::whereDate('created_at', Carbon::today())->distinct('ip_address')->count('ip_address'));
        $uniqueMonth = Cache::remember('stat_unique_month', 300, fn () => PageVisit::where('created_at', '>=', Carbon::now()->subDays(30))->distinct('ip_address')->count('ip_address'));

        $yesterdayVisits = Cache::remember('stat_visits_yesterday', 300, fn () => PageVisit::whereDate('created_at', Carbon::yesterday())->count());
        $todayTrend = $yesterdayVisits > 0
            ? round((($todayVisits - $yesterdayVisits) / $yesterdayVisits) * 100)
            : ($todayVisits > 0 ? 100 : 0);

        return [
            Stat::make($isAr ? 'زوار اليوم' : 'Today Visitors', number_format($todayVisits))
                ->description(($todayTrend >= 0 ? '+' : '').$todayTrend.'% '.($isAr ? 'مقارنة بالأمس' : 'vs yesterday'))
                ->descriptionIcon($todayTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->icon('heroicon-o-eye')
                ->color($todayTrend >= 0 ? 'success' : 'danger'),

            Stat::make($isAr ? 'زوار فريدون اليوم' : 'Unique Today', number_format($uniqueToday))
                ->description($isAr ? 'عناوين IP مختلفة' : 'Distinct IPs')
                ->descriptionIcon('heroicon-m-user-group')
                ->icon('heroicon-o-user-group')
                ->color('info'),

            Stat::make($isAr ? 'زيارات الأسبوع' : 'This Week', number_format($weekVisits))
                ->description($isAr ? 'آخر 7 أيام' : 'Last 7 days')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->icon('heroicon-o-calendar-days')
                ->color('warning'),

            Stat::make($isAr ? 'زيارات الشهر' : 'This Month', number_format($monthVisits))
                ->description(($isAr ? 'زوار فريدون: ' : 'Unique: ').number_format($uniqueMonth))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->icon('heroicon-o-chart-bar')
                ->color('primary'),
        ];
    }
}
