<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Extract the primary image from an article URL (og:image / twitter:image)
 * and save a local copy so we can embed it in generated OG images.
 */
class SourceImageExtractor
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Returns public-relative path like /uploads/sources/123.jpg, or null
     * if the URL yields no usable image.
     */
    public function extract(string $url, int|string $id): ?string
    {
        try {
            $html = Http::timeout(10)
                ->withUserAgent('Mozilla/5.0 (compatible; HassanNewsBot/1.0; +https://almalki.sa)')
                ->get($url)
                ->body();
        } catch (\Throwable $e) {
            Log::info('SourceImage fetch HTML failed', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }

        $imageUrl = $this->findImageUrl($html, $url);
        if (! $imageUrl) {
            return null;
        }

        return $this->download($imageUrl, $id);
    }

    private function findImageUrl(string $html, string $pageUrl): ?string
    {
        // DOMDocument is more reliable than regex for real-world HTML.
        $priority = [
            ['property', 'og:image:secure_url'],
            ['property', 'og:image'],
            ['name', 'twitter:image'],
            ['name', 'twitter:image:src'],
        ];

        $previous = libxml_use_internal_errors(true);
        $doc = new \DOMDocument;
        $loaded = $doc->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return null;
        }

        $metas = $doc->getElementsByTagName('meta');
        foreach ($priority as [$attr, $value]) {
            foreach ($metas as $meta) {
                /** @var \DOMElement $meta */
                if (strtolower($meta->getAttribute($attr)) === $value) {
                    $content = trim($meta->getAttribute('content'));
                    if ($content !== '') {
                        return $this->resolveUrl($content, $pageUrl);
                    }
                }
            }
        }

        return null;
    }

    private function resolveUrl(string $url, string $base): string
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (str_starts_with($url, '//')) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';

            return $scheme.':'.$url;
        }
        if (str_starts_with($url, '/')) {
            $parts = parse_url($base);

            return $parts['scheme'].'://'.$parts['host'].$url;
        }

        return rtrim($base, '/').'/'.ltrim($url, '/');
    }

    private function download(string $imageUrl, int|string $id): ?string
    {
        try {
            $bytes = Http::timeout(15)
                ->withUserAgent('Mozilla/5.0 (compatible; HassanNewsBot/1.0)')
                ->get($imageUrl)
                ->body();

            if (strlen($bytes) < 1024) {
                return null; // too small to be a useful image
            }

            // Decode and resize for the OG layout. Store a 600px-wide thumbnail.
            $image = $this->manager->read($bytes);
            $image->scale(width: 600);

            $dir = public_path('uploads/sources');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $path = "{$dir}/{$id}.jpg";
            $image->save($path, quality: 85);

            return "/uploads/sources/{$id}.jpg";
        } catch (\Throwable $e) {
            Log::info('SourceImage download failed', ['url' => $imageUrl, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
