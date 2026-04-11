<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Portfolio;
use App\Models\Workshop;
use App\Services\SeoService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('Home/Index', [
            'meta' => SeoService::forHome($locale),
            'articles' => Article::published()->latest('published_at')->take(6)->get(),
            'portfolios' => Portfolio::published()->orderBy('sort_order')->take(6)->get(),
            'workshops' => Workshop::published()->orderByDesc('event_date')->take(6)->get(),
        ]);
    }
}
