<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Services\SeoService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PortfolioController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('Portfolio/Index', [
            'meta' => SeoService::forPage(
                $locale,
                'portfolio',
                'الأعمال | حسان المالكي',
                'Portfolio | Hassan Almalki',
                'مجموعة من المشاريع التقنية والمنصات الرقمية التي عملت عليها.',
                'A selection of technical projects and digital platforms I have built.'
            ),
            'portfolios' => Portfolio::published()
                ->select(['id', 'title_ar', 'title_en', 'slug_ar', 'slug_en', 'description_ar', 'description_en', 'category', 'category_en', 'cover_image', 'sort_order'])
                ->orderBy('sort_order')
                ->paginate(12),
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'الأعمال' : 'Portfolio'],
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $locale = app()->getLocale();
        $column = $locale === 'en' ? 'slug_en' : 'slug_ar';
        $portfolio = Portfolio::published()->where($column, $slug)->firstOr(fn () => throw new NotFoundHttpException);

        return Inertia::render('Portfolio/Show', [
            'meta' => SeoService::forPortfolio($portfolio, $locale),
            'portfolio' => $portfolio,
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'الأعمال' : 'Portfolio', 'url' => $locale === 'ar' ? '/portfolio' : '/en/portfolio'],
                ['label' => $portfolio->title($locale)],
            ],
        ]);
    }
}
