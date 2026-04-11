<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Portfolio;
use App\Models\Workshop;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $now = now()->toIso8601String();

        $entries = [];

        $staticPages = ['', 'about', 'articles', 'portfolio', 'workshops', 'contact'];
        $priorities = ['' => '1.0', 'about' => '0.8', 'articles' => '0.8', 'portfolio' => '0.8', 'workshops' => '0.8', 'contact' => '0.8'];

        foreach ($staticPages as $page) {
            $arUrl = "$base/$page";
            $enUrl = "$base/en/$page";
            $entries[] = $this->urlEntry(rtrim($arUrl, '/'), $now, 'monthly', $priorities[$page] ?? '0.6', $arUrl, $enUrl);
            $entries[] = $this->urlEntry(rtrim($enUrl, '/'), $now, 'monthly', $priorities[$page] ?? '0.6', $arUrl, $enUrl);
        }

        foreach (Article::published()->get() as $a) {
            $ar = "$base/articles/{$a->slug_ar}";
            $en = "$base/en/articles/{$a->slug_en}";
            $lastmod = optional($a->updated_at)->toIso8601String() ?? $now;
            $entries[] = $this->urlEntry($ar, $lastmod, 'weekly', '0.6', $ar, $en);
            $entries[] = $this->urlEntry($en, $lastmod, 'weekly', '0.6', $ar, $en);
        }

        foreach (Portfolio::published()->get() as $p) {
            $ar = "$base/portfolio/{$p->slug_ar}";
            $en = "$base/en/portfolio/{$p->slug_en}";
            $lastmod = optional($p->updated_at)->toIso8601String() ?? $now;
            $entries[] = $this->urlEntry($ar, $lastmod, 'monthly', '0.6', $ar, $en);
            $entries[] = $this->urlEntry($en, $lastmod, 'monthly', '0.6', $ar, $en);
        }

        foreach (Workshop::published()->get() as $w) {
            $ar = "$base/workshops/{$w->slug_ar}";
            $en = "$base/en/workshops/{$w->slug_en}";
            $lastmod = optional($w->updated_at)->toIso8601String() ?? $now;
            $entries[] = $this->urlEntry($ar, $lastmod, 'monthly', '0.6', $ar, $en);
            $entries[] = $this->urlEntry($en, $lastmod, 'monthly', '0.6', $ar, $en);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n".
            implode("\n", $entries)."\n".
            '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $body = "User-agent: *\nAllow: /\nDisallow: /proposals\nDisallow: /en/proposals\nSitemap: $base/sitemap.xml\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }

    protected function urlEntry(string $loc, string $lastmod, string $changefreq, string $priority, string $arHref, string $enHref): string
    {
        return "  <url>\n".
            "    <loc>".htmlspecialchars($loc)."</loc>\n".
            "    <lastmod>$lastmod</lastmod>\n".
            "    <changefreq>$changefreq</changefreq>\n".
            "    <priority>$priority</priority>\n".
            '    <xhtml:link rel="alternate" hreflang="ar" href="'.htmlspecialchars($arHref).'"/>'."\n".
            '    <xhtml:link rel="alternate" hreflang="en" href="'.htmlspecialchars($enHref).'"/>'."\n".
            '    <xhtml:link rel="alternate" hreflang="x-default" href="'.htmlspecialchars($arHref).'"/>'."\n".
            '  </url>';
    }
}
