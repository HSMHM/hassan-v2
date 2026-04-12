<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $locale = app()->getLocale();

        return Inertia::render('Pages/Show', [
            'meta' => SeoService::forPage(
                $locale,
                $slug,
                $page->meta_title_ar ?: $page->title_ar,
                $page->meta_title_en ?: $page->title_en,
                $page->meta_description_ar ?: '',
                $page->meta_description_en ?: '',
            ),
            'page' => $page->toArray(),
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $page->title($locale)],
            ],
        ]);
    }
}
