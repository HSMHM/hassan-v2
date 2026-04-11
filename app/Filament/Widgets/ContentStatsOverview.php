<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\NewsPost;
use App\Models\PageVisit;
use App\Models\Portfolio;
use App\Models\Workshop;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $isAr = app()->getLocale() === 'ar';

        $articles = Cache::remember('stat_articles_published', 3600, fn () => Article::where('is_published', true)->count());
        $portfolios = Cache::remember('stat_portfolios_published', 3600, fn () => Portfolio::where('is_published', true)->count());
        $workshops = Cache::remember('stat_workshops_published', 3600, fn () => Workshop::where('is_published', true)->count());
        $news = Cache::remember('stat_news_published', 3600, fn () => NewsPost::where('status', 'published')->count());
        $pending = Cache::remember('stat_news_pending', 600, fn () => NewsPost::where('status', 'pending')->count());
        $unread = Cache::remember('stat_messages_unread', 600, fn () => ContactMessage::where('is_read', false)->where('is_spam', false)->count());
        $totalMessages = Cache::remember('stat_messages_total', 3600, fn () => ContactMessage::where('is_spam', false)->count());
        $totalVisits = Cache::remember('stat_visits_total', 300, fn () => PageVisit::count());

        return [
            Stat::make($isAr ? 'المقالات المنشورة' : 'Published Articles', $articles)
                ->description($isAr ? 'مقال مرئي للعامة' : 'Visible to public')
                ->descriptionIcon('heroicon-m-document-text')
                ->icon('heroicon-o-document-text')
                ->color('success'),

            Stat::make($isAr ? 'المشاريع' : 'Portfolios', $portfolios)
                ->description($isAr ? 'مشروع منشور' : 'Published projects')
                ->descriptionIcon('heroicon-m-briefcase')
                ->icon('heroicon-o-briefcase')
                ->color('info'),

            Stat::make($isAr ? 'ورش العمل' : 'Workshops', $workshops)
                ->description($isAr ? 'ورشة منشورة' : 'Published workshops')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-o-academic-cap')
                ->color('warning'),

            Stat::make($isAr ? 'أخبار منشورة' : 'Published News', $news)
                ->description(($isAr ? 'بانتظار: ' : 'Pending: ').number_format($pending))
                ->descriptionIcon('heroicon-m-newspaper')
                ->icon('heroicon-o-newspaper')
                ->color($pending > 0 ? 'warning' : 'info'),

            Stat::make($isAr ? 'رسائل غير مقروءة' : 'Unread Messages', $unread)
                ->description(($isAr ? 'الإجمالي: ' : 'Total: ').number_format($totalMessages))
                ->descriptionIcon('heroicon-m-envelope')
                ->icon('heroicon-o-envelope')
                ->color($unread > 0 ? 'danger' : 'gray'),

            Stat::make($isAr ? 'إجمالي الرسائل' : 'Total Messages', number_format($totalMessages))
                ->description($isAr ? 'بدون الرسائل المزعجة' : 'Excluding spam')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary'),

            Stat::make($isAr ? 'إجمالي الزيارات' : 'Total Visits', number_format($totalVisits))
                ->description($isAr ? 'منذ إطلاق الموقع' : 'Since launch')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->icon('heroicon-o-globe-alt')
                ->color('success'),

            Stat::make($isAr ? 'بانتظار الموافقة' : 'Pending Review', $pending)
                ->description($isAr ? 'أخبار تحتاج مراجعة' : 'News awaiting you')
                ->descriptionIcon('heroicon-m-clock')
                ->icon('heroicon-o-clock')
                ->color($pending > 0 ? 'warning' : 'gray'),
        ];
    }
}
