<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Portfolio;
use App\Models\Workshop;
use Illuminate\Support\Facades\URL;

class SeoService
{
    protected static function absoluteUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim(config('app.url'), '/').'/'.ltrim($path, '/');
    }

    protected static function base(string $locale, string $path, array $extras = [], ?string $pathEn = null): array
    {
        $baseUrl = config('app.url');
        $arPath = ltrim($path, '/');
        $enPath = 'en/'.ltrim($pathEn ?? $path, '/');
        $canonical = $locale === 'en' ? "$baseUrl/$enPath" : "$baseUrl/$arPath";
        $canonical = rtrim($canonical, '/');

        $ogImagePath = site_setting('og_image', '/img/og-image.jpg');
        $ogImage = str_starts_with((string) $ogImagePath, 'http') ? $ogImagePath : $baseUrl.$ogImagePath;
        $siteName = site_setting($locale === 'ar' ? 'site_name_ar' : 'site_name_en', 'Hassan Almalki');
        $twitterHandle = site_setting('twitter_handle', '@eng_hssaan');

        return array_merge([
            'title' => '',
            'description' => '',
            'canonical' => $canonical,
            'robots' => 'index, follow',
            'og' => [
                'title' => '',
                'description' => '',
                'image' => $ogImage,
                'url' => $canonical,
                'type' => 'website',
                'locale' => $locale === 'ar' ? 'ar_SA' : 'en_US',
                'site_name' => $siteName,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'site' => $twitterHandle,
                'creator' => $twitterHandle,
                'title' => '',
                'description' => '',
                'image' => $ogImage,
            ],
            'alternates' => [
                ['hreflang' => 'ar', 'href' => rtrim("$baseUrl/$arPath", '/')],
                ['hreflang' => 'en', 'href' => rtrim("$baseUrl/$enPath", '/')],
                ['hreflang' => 'x-default', 'href' => rtrim("$baseUrl/$arPath", '/')],
            ],
            'jsonLd' => [],
        ], $extras);
    }

    public static function forHome(string $locale): array
    {
        $isAr = $locale === 'ar';
        $title = site_setting($isAr ? 'meta_title_ar' : 'meta_title_en');
        $description = site_setting($isAr ? 'meta_description_ar' : 'meta_description_en');
        $ownerName = site_setting($isAr ? 'owner_name_ar' : 'owner_name_en', 'Hassan Almalki');
        $jobTitle = site_setting($isAr ? 'job_title_ar' : 'job_title_en', 'Web Developer');

        $meta = self::base($locale, '');
        $meta['title'] = $title;
        $meta['description'] = $description;
        $meta['og']['title'] = $title;
        $meta['og']['description'] = $description;
        $meta['twitter']['title'] = $title;
        $meta['twitter']['description'] = $description;
        $meta['jsonLd'] = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => $ownerName,
                'url' => config('app.url'),
                'jobTitle' => $jobTitle,
                'sameAs' => array_filter([
                    site_setting('twitter_url'),
                    site_setting('linkedin_url'),
                ]),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'url' => config('app.url'),
                'name' => $meta['og']['site_name'],
                'inLanguage' => $locale,
            ],
        ];

        return $meta;
    }

    public static function forPage(string $locale, string $path, string $titleAr, string $titleEn, string $descAr, string $descEn): array
    {
        $isAr = $locale === 'ar';
        $rawTitle = $isAr ? $titleAr : $titleEn;
        $siteName = site_setting($isAr ? 'site_name_ar' : 'site_name_en', 'Hassan Almalki');

        // Strip any legacy hardcoded suffix then re-append from settings
        $shortTitle = trim(preg_replace('/\s*\|\s*(حسان المالكي|Hassan Almalki).*$/u', '', $rawTitle));
        $title = $shortTitle.' | '.$siteName;

        $desc = $isAr ? $descAr : $descEn;
        $meta = self::base($locale, $path);
        $meta['title'] = $title;
        $meta['description'] = $desc;
        $meta['og']['title'] = $title;
        $meta['og']['description'] = $desc;
        $meta['twitter']['title'] = $title;
        $meta['twitter']['description'] = $desc;

        return $meta;
    }

    public static function forArticle(Article $article, string $locale): array
    {
        $meta = self::base($locale, "articles/{$article->slug_ar}", [], "articles/{$article->slug_en}");
        $title = $locale === 'ar' ? ($article->meta_title_ar ?: $article->title_ar) : ($article->meta_title_en ?: $article->title_en);
        $desc = $locale === 'ar' ? ($article->meta_description_ar ?: $article->excerpt_ar) : ($article->meta_description_en ?: $article->excerpt_en);
        $meta['title'] = $title;
        $meta['description'] = $desc;
        $meta['og']['title'] = $title;
        $meta['og']['description'] = $desc;
        $meta['og']['type'] = 'article';
        $cover = $locale === 'ar' ? $article->cover_image : ($article->cover_image_en ?: $article->cover_image);
        if ($cover && ($absolute = self::absoluteUrl($cover))) {
            $meta['og']['image'] = $absolute;
            $meta['twitter']['image'] = $absolute;
        }
        $meta['twitter']['title'] = $title;
        $meta['twitter']['description'] = $desc;
        $meta['jsonLd'] = [[
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $desc,
            'datePublished' => optional($article->published_at)->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $locale === 'ar' ? 'حسان المالكي' : 'Hassan Almalki'],
        ]];

        return $meta;
    }

    public static function forPortfolio(Portfolio $portfolio, string $locale): array
    {
        $meta = self::base($locale, "portfolio/{$portfolio->slug_ar}", [], "portfolio/{$portfolio->slug_en}");
        $title = $locale === 'ar' ? ($portfolio->meta_title_ar ?: $portfolio->title_ar) : ($portfolio->meta_title_en ?: $portfolio->title_en);
        $desc = $locale === 'ar' ? ($portfolio->meta_description_ar ?: $portfolio->description_ar) : ($portfolio->meta_description_en ?: $portfolio->description_en);
        $meta['title'] = $title;
        $meta['description'] = $desc;
        $meta['og']['title'] = $title;
        $meta['og']['description'] = $desc;
        $cover = $locale === 'ar' ? $portfolio->cover_image : ($portfolio->cover_image_en ?: $portfolio->cover_image);
        if ($cover && ($absolute = self::absoluteUrl($cover))) {
            $meta['og']['image'] = $absolute;
            $meta['twitter']['image'] = $absolute;
        }
        $meta['twitter']['title'] = $title;
        $meta['twitter']['description'] = $desc;
        $meta['jsonLd'] = [[
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $title,
            'description' => $desc,
        ]];

        return $meta;
    }

    public static function forWorkshop(Workshop $workshop, string $locale): array
    {
        $meta = self::base($locale, "workshops/{$workshop->slug_ar}", [], "workshops/{$workshop->slug_en}");
        $title = $locale === 'ar' ? ($workshop->meta_title_ar ?: $workshop->title_ar) : ($workshop->meta_title_en ?: $workshop->title_en);
        $desc = $locale === 'ar' ? ($workshop->meta_description_ar ?: $workshop->description_ar) : ($workshop->meta_description_en ?: $workshop->description_en);
        $meta['title'] = $title;
        $meta['description'] = $desc;
        $meta['og']['title'] = $title;
        $meta['og']['description'] = $desc;
        $cover = $locale === 'ar' ? $workshop->cover_image : ($workshop->cover_image_en ?: $workshop->cover_image);
        if ($cover && ($absolute = self::absoluteUrl($cover))) {
            $meta['og']['image'] = $absolute;
            $meta['twitter']['image'] = $absolute;
        }
        $meta['twitter']['title'] = $title;
        $meta['twitter']['description'] = $desc;
        $meta['jsonLd'] = [[
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $title,
            'description' => $desc,
            'startDate' => optional($workshop->event_date)->toIso8601String(),
        ]];

        return $meta;
    }

    public static function forProposalLogin(string $locale): array
    {
        $meta = self::base($locale, 'proposals');
        $meta['robots'] = 'noindex, nofollow';
        $meta['title'] = $locale === 'ar' ? 'العروض' : 'Proposals';
        $meta['description'] = '';

        return $meta;
    }
}
