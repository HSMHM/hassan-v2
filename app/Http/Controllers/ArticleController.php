<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\SeoService;
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
            'articles' => Article::published()->latest('published_at')->paginate(12),
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
        $article = Article::published()->where($column, $slug)->firstOr(fn () => throw new NotFoundHttpException);

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        // Purify HTML content before it reaches the browser (XSS defense for
        // Claude-generated news and any other HTML-bearing fields).
        $articlePayload = array_merge($article->toArray(), [
            'content_ar' => $article->safeContent('ar'),
            'content_en' => $article->safeContent('en'),
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
}
