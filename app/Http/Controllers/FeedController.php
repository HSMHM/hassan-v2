<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function articles(Request $request): Response
    {
        $locale = str_starts_with(ltrim($request->path(), '/'), 'en/') ? 'en' : 'ar';

        $items = Article::published()
            ->latest('published_at')
            ->take(30)
            ->get()
            ->map(fn (Article $a) => [
                'title' => $locale === 'en' ? $a->title_en : $a->title_ar,
                'link' => rtrim(config('app.url'), '/').($locale === 'en' ? '/en' : '').'/articles/'.($locale === 'en' ? $a->slug_en : $a->slug_ar),
                'description' => $locale === 'en' ? ($a->excerpt_en ?? '') : ($a->excerpt_ar ?? ''),
                'pubDate' => $a->published_at?->toRfc2822String(),
            ]);

        return response()
            ->view('feed.rss', [
                'items' => $items,
                'locale' => $locale,
                'selfUrl' => $request->url(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}
