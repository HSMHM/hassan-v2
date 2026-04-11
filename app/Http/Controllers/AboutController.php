<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('About/Index', [
            'meta' => SeoService::forPage(
                $locale,
                'about',
                'نبذة عني | حسان المالكي',
                'About | Hassan Almalki',
                'تعرف على حسان المالكي - مطور تطبيقات ويب ومدير منتجات تقنية بخبرة 8+ سنوات.',
                'Learn about Hassan Almalki - web developer and digital product manager with 8+ years experience.'
            ),
            'breadcrumbs' => [
                ['label' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'url' => $locale === 'ar' ? '/' : '/en'],
                ['label' => $locale === 'ar' ? 'نبذة عني' : 'About'],
            ],
        ]);
    }
}
