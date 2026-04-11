<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $isAr = $locale === 'ar';

        // Load ALL settings in a single cached query. Previously every request
        // made ~22 separate site_setting() calls, each hitting its own cache entry
        // and, on a cold cache, a separate DB query. Now: 1 query, cached for 1h.
        $settings = Cache::remember('all_site_settings', 3600, function () {
            return SiteSetting::pluck('value', 'key')->toArray();
        });

        $pick = static fn (string $key) => $settings[$key] ?? null;

        return [
            ...parent::share($request),
            'locale' => $locale,
            'direction' => $isAr ? 'rtl' : 'ltr',
            // Translations cached per-locale indefinitely, flushed via artisan cache:clear
            'translations' => fn () => Cache::rememberForever(
                "translations.$locale",
                fn () => $this->loadTranslations($locale)
            ),
            'settings' => [
                'site_name' => $pick($isAr ? 'site_name_ar' : 'site_name_en'),
                'site_name_ar' => $pick('site_name_ar'),
                'site_name_en' => $pick('site_name_en'),
                'site_logo' => $pick('site_logo'),
                'favicon' => $pick('favicon'),
                'og_image' => $pick('og_image'),
                'owner_name' => $pick($isAr ? 'owner_name_ar' : 'owner_name_en'),
                'profession' => $pick($isAr ? 'profession_ar' : 'profession_en'),
                'job_title' => $pick($isAr ? 'job_title_ar' : 'job_title_en'),
                'phone' => $pick('phone'),
                'email' => $pick('email'),
                'whatsapp_number' => $pick('whatsapp_number'),
                'whatsapp_url' => $pick('whatsapp_url'),
                'twitter_url' => $pick('twitter_url'),
                'twitter_handle' => $pick('twitter_handle'),
                'linkedin_url' => $pick('linkedin_url'),
                'snapchat_url' => $pick('snapchat_url'),
                'snapchat_handle' => $pick('snapchat_handle'),
                'address' => $pick($isAr ? 'address_ar' : 'address_en'),
                'copyright' => $pick($isAr ? 'copyright_ar' : 'copyright_en'),
                'footer_description' => $pick($isAr ? 'footer_description_ar' : 'footer_description_en'),
                'about_description' => $pick($isAr ? 'about_description_ar' : 'about_description_en'),
                'birthday' => $pick('birthday'),
                'years_experience' => $pick('years_experience'),
                'cv_url' => $pick('cv_url'),
                'pm_skills' => $pick($isAr ? 'pm_skills_ar' : 'pm_skills_en'),
                'skills' => $pick($isAr ? 'skills_ar' : 'skills_en'),
                'languages' => $pick($isAr ? 'languages_ar' : 'languages_en'),
            ],
            'turnstile_site_key' => config('services.turnstile.site_key'),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    protected function loadTranslations(string $locale): array
    {
        $path = lang_path("$locale/messages.php");

        return file_exists($path) ? require $path : [];
    }
}
