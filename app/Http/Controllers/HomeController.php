<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\NewsPost;
use App\Models\Portfolio;
use App\Models\Workshop;
use App\Services\SeoService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        // Cache the arrays, not the Eloquent collections, so Inertia always
        // serializes to a JS array even if the cache driver changes format.
        $articles = Cache::remember("home_articles_{$locale}_v2", 3600, function () {
            $articles = Article::published()->latest('published_at')->get()
                ->map(fn ($a) => array_merge($a->toArray(), ['is_news' => false]));

            $news = NewsPost::whereIn('status', ['published', 'partial'])
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->get()
                ->map(fn ($n) => array_merge($n->toArray(), ['is_news' => true]));

            return $articles->concat($news)
                ->sortByDesc('published_at')
                ->take(6)
                ->values()
                ->toArray();
        });
        $portfolios = Cache::remember("home_portfolios_{$locale}_v2", 3600, fn () =>
            Portfolio::published()->orderBy('sort_order')->take(6)->get()->toArray()
        );
        $workshops = Cache::remember("home_workshops_{$locale}_v2", 3600, fn () =>
            Workshop::published()->orderByDesc('event_date')->take(6)->get()->toArray()
        );

        return Inertia::render('Home/Index', [
            'meta' => SeoService::forHome($locale),
            'articles' => $articles,
            'portfolios' => $portfolios,
            'workshops' => $workshops,
        ]);
    }
}
