<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer privacy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Limit powerful features we don't use
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=()'
        );

        // HSTS — 1 year, include subdomains, preload-ready.
        // Only sent over HTTPS so local http://127.0.0.1 is unaffected.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // CSP — permissive enough for Inertia/Vue + FontAwesome kit + Google Fonts.
        // 'unsafe-inline' on style is needed for Vue scoped styles and Filament.
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://kit.fontawesome.com https://ka-f.fontawesome.com; ".
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://ka-f.fontawesome.com; ".
            "font-src 'self' data: https://fonts.gstatic.com https://ka-f.fontawesome.com; ".
            "img-src 'self' data: blob: https:; ".
            "connect-src 'self' https://ka-f.fontawesome.com; ".
            "frame-ancestors 'self'; ".
            "base-uri 'self'; ".
            "form-action 'self'"
        );

        return $response;
    }
}
