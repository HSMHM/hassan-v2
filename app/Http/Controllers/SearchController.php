<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $locale = app()->getLocale();
        $query = trim((string) $request->input('q', ''));
        $results = null;

        if (mb_strlen($query) >= 2) {
            $titleCol = $locale === 'en' ? 'title_en' : 'title_ar';
            $excerptCol = $locale === 'en' ? 'excerpt_en' : 'excerpt_ar';
            $contentCol = $locale === 'en' ? 'content_en' : 'content_ar';

            $like = '%'.$query.'%';
            $results = Article::published()
                ->where(fn ($q) => $q
                    ->where($titleCol, 'like', $like)
                    ->orWhere($excerptCol, 'like', $like)
                    ->orWhere($contentCol, 'like', $like)
                )
                ->latest('published_at')
                ->paginate(12)
                ->appends(['q' => $query]);
        }

        return Inertia::render('Search/Index', [
            'meta' => SeoService::forPage(
                $locale,
                'search',
                'البحث | حسان المالكي',
                'Search | Hassan Almalki',
                'ابحث في المقالات التقنية.',
                'Search technical articles.'
            ),
            'q' => $query,
            'results' => $results,
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'البحث' : 'Search'],
            ],
        ]);
    }
}
