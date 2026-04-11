<?php

use App\Models\SiteSetting;

if (! function_exists('site_setting')) {
    function site_setting(string $key, $default = null): ?string
    {
        return SiteSetting::get($key, $default);
    }
}

if (! function_exists('reading_time')) {
    function reading_time(?string $content, string $locale = 'ar'): string
    {
        if (! $content) {
            return $locale === 'ar' ? 'أقل من دقيقة' : 'Less than a minute';
        }

        $text = trim(strip_tags($content));
        $wpm = $locale === 'ar' ? 180 : 200;
        // str_word_count fails on Arabic; count whitespace-separated tokens instead
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));
        $minutes = max(1, (int) ceil($words / $wpm));

        if ($locale === 'ar') {
            return $minutes === 1 ? 'دقيقة واحدة' : "{$minutes} دقائق";
        }

        return $minutes === 1 ? '1 min read' : "{$minutes} min read";
    }
}
