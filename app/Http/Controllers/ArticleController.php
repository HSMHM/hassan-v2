<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\NewsPost;
use App\Services\SeoService;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('Articles/Index', [
            'meta' => SeoService::forPage(
                $locale,
                'articles',
                'المقالات | حسان المالكي',
                'Articles | Hassan Almalki',
                'مقالات تقنية حول تطوير الويب والذكاء الاصطناعي وإدارة المنتجات الرقمية.',
                'Technical articles on web development, AI, and digital product management.'
            ),
            'articles' => $this->paginatedArticles(),
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'المقالات' : 'Articles'],
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $locale = app()->getLocale();
        $column = $locale === 'en' ? 'slug_en' : 'slug_ar';

        $article = Article::published()->where($column, $slug)->first();
        $isNews = false;

        if (! $article) {
            $article = NewsPost::whereIn('status', ['published', 'partial'])
                ->where($column, $slug)
                ->first();

            if (! $article) {
                throw new NotFoundHttpException;
            }

            $isNews = true;
        }

        $related = Article::published()
            ->where('id', '!=', $isNews ? 0 : $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        $articlePayload = array_merge($article->toArray(), [
            'content_ar' => $article->safeContent('ar'),
            'content_en' => $article->safeContent('en'),
            'is_news' => $isNews,
        ]);

        return Inertia::render('Articles/Show', [
            'meta' => SeoService::forArticle($article, $locale),
            'article' => $articlePayload,
            'reading_time' => reading_time(
                $locale === 'en' ? $article->content_en : $article->content_ar,
                $locale
            ),
            'related' => $related,
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'المقالات' : 'Articles', 'url' => $locale === 'ar' ? '/articles' : '/en/articles'],
                ['label' => $article->title($locale)],
            ],
        ]);
    }

    private function paginatedArticles(int $perPage = 12): LengthAwarePaginator
    {
        $currentPage = request()->integer('page', 1);

        $articles = Article::published()->get()
            ->map(fn ($a) => array_merge($a->toArray(), ['is_news' => false]));

        $news = NewsPost::whereIn('status', ['published', 'partial'])
            ->whereNotNull('published_at')
            ->get()
            ->map(fn ($n) => array_merge($n->toArray(), ['is_news' => true]));

        $all = $articles->concat($news)
            ->sortByDesc('published_at')
            ->values();

        return new LengthAwarePaginator(
            $all->forPage($currentPage, $perPage)->values(),
            $all->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );
    }
}
