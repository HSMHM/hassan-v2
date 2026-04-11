<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests to HTML pages, skip assets/api/admin
        if (
            $request->isMethod('GET') &&
            !$request->is('cpanel*', 'admin*', 'api*', 'livewire*', 'sitemap*', 'robots*') &&
            !$request->ajax() &&
            $response->getStatusCode() === 200
        ) {
            try {
                $ua = $request->userAgent() ?? '';

                PageVisit::create([
                    'path' => '/' . ltrim($request->path(), '/'),
                    'page_title' => $this->guessPageTitle($request),
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr($ua, 0, 500),
                    'browser' => $this->parseBrowser($ua),
                    'platform' => $this->parsePlatform($ua),
                    'device' => $this->parseDevice($ua),
                    'referer' => $request->header('referer') ? mb_substr($request->header('referer'), 0, 255) : null,
                    'locale' => app()->getLocale(),
                ]);
            } catch (\Throwable) {
                // Silently fail — don't break the site for analytics
            }
        }

        return $response;
    }

    protected function guessPageTitle(Request $request): ?string
    {
        $path = trim($request->path(), '/');

        if ($path === '' || $path === '/') {
            return 'Home';
        }

        $segments = explode('/', $path);

        // Remove locale prefix
        if (in_array($segments[0], ['en', 'ar'])) {
            array_shift($segments);
        }

        return implode(' / ', array_map('ucfirst', $segments)) ?: 'Home';
    }

    protected function parseBrowser(string $ua): ?string
    {
        $browsers = [
            'Edg' => 'Edge',
            'OPR' => 'Opera',
            'Opera' => 'Opera',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'Firefox' => 'Firefox',
            'MSIE' => 'IE',
            'Trident' => 'IE',
        ];

        foreach ($browsers as $key => $name) {
            if (str_contains($ua, $key)) {
                return $name;
            }
        }

        return null;
    }

    protected function parsePlatform(string $ua): ?string
    {
        $platforms = [
            'Windows' => 'Windows',
            'Macintosh' => 'macOS',
            'Mac OS' => 'macOS',
            'Linux' => 'Linux',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iPadOS',
        ];

        foreach ($platforms as $key => $name) {
            if (str_contains($ua, $key)) {
                return $name;
            }
        }

        return null;
    }

    protected function parseDevice(string $ua): ?string
    {
        if (preg_match('/Mobile|Android.*Mobile|iPhone/i', $ua)) {
            return 'Mobile';
        }
        if (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $ua)) {
            return 'Tablet';
        }
        if (preg_match('/bot|crawl|spider|slurp|Googlebot|Bingbot/i', $ua)) {
            return 'Bot';
        }

        return 'Desktop';
    }
}
