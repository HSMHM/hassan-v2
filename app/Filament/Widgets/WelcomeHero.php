<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\NewsPost;
use App\Models\PageVisit;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class WelcomeHero extends Widget
{
    protected string $view = 'filament.widgets.welcome-hero';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $isAr = app()->getLocale() === 'ar';
        $hour = (int) now()->format('H');
        $name = site_setting($isAr ? 'owner_name_ar' : 'owner_name_en', $isAr ? 'حسان' : 'Hassan');

        if ($hour < 12) {
            $greeting = $isAr ? 'صباح الخير' : 'Good Morning';
        } elseif ($hour < 17) {
            $greeting = $isAr ? 'مساء الخير' : 'Good Afternoon';
        } else {
            $greeting = $isAr ? 'مساء الخير' : 'Good Evening';
        }

        $dateStr = $isAr
            ? now()->locale('ar')->isoFormat('dddd، D MMMM YYYY')
            : now()->isoFormat('dddd, MMMM D, YYYY');

        $todayVisits = Cache::remember('stat_visits_today', 300, fn () => PageVisit::whereDate('created_at', Carbon::today())->count());
        $unreadMessages = Cache::remember('stat_messages_unread', 600, fn () => ContactMessage::where('is_read', false)->where('is_spam', false)->count());
        $pendingNews = Cache::remember('stat_news_pending', 600, fn () => NewsPost::where('status', 'pending')->count());

        return [
            'isAr' => $isAr,
            'greeting' => $greeting,
            'name' => $name,
            'date' => $dateStr,
            'todayVisits' => $todayVisits,
            'unreadMessages' => $unreadMessages,
            'pendingNews' => $pendingNews,
        ];
    }
}
