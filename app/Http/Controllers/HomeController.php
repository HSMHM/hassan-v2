<?php

namespace App\Http\Controllers;

use App\Models\Article;
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

        $articles = Cache::remember("home_articles_{$locale}", 3600, fn () =>
            Article::published()->latest('published_at')->take(6)->get()
        );
        $portfolios = Cache::remember("home_portfolios_{$locale}", 3600, fn () =>
            Portfolio::published()->orderBy('sort_order')->take(6)->get()
        );
        $workshops = Cache::remember("home_workshops_{$locale}", 3600, fn () =>
            Workshop::published()->orderByDesc('event_date')->take(6)->get()
        );

        return Inertia::render('Home/Index', [
            'meta' => SeoService::forHome($locale),
            'articles' => $articles,
            'portfolios' => $portfolios,
            'workshops' => $workshops,
        ]);
    }
}
