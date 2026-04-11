<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = str_starts_with($request->path(), 'en') ? 'en' : 'ar';
        app()->setLocale($locale);

        return $next($request);
    }
}
