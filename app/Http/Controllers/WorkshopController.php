<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Services\SeoService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WorkshopController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('Workshops/Index', [
            'meta' => SeoService::forPage(
                $locale,
                'workshops',
                'ورش العمل | حسان المالكي',
                'Workshops | Hassan Almalki',
                'ورش عمل ومحاضرات تقنية في تطوير الويب وإدارة المنتجات الرقمية.',
                'Technical workshops and lectures on web development and digital product management.'
            ),
            'workshops' => Workshop::published()
                ->select(['id', 'title_ar', 'title_en', 'slug_ar', 'slug_en', 'description_ar', 'description_en', 'platform', 'platform_en', 'cover_image', 'cover_image_en', 'event_date', 'location_ar', 'location_en'])
                ->orderByDesc('event_date')
                ->paginate(12),
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'ورش العمل' : 'Workshops'],
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $locale = app()->getLocale();
        $column = $locale === 'en' ? 'slug_en' : 'slug_ar';
        $workshop = Workshop::published()->where($column, $slug)->firstOr(fn () => throw new NotFoundHttpException);

        return Inertia::render('Workshops/Show', [
            'meta' => SeoService::forWorkshop($workshop, $locale),
            'workshop' => $workshop,
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'ورش العمل' : 'Workshops', 'url' => $locale === 'ar' ? '/workshops' : '/en/workshops'],
                ['label' => $workshop->title($locale)],
            ],
        ]);
    }
}
