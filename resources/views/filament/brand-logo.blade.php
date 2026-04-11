@php
    $isAr = app()->getLocale() === 'ar';
    $siteName = site_setting($isAr ? 'site_name_ar' : 'site_name_en', 'Hassan Almalki');
    $logo = site_setting('admin_logo') ?: site_setting('site_logo');
@endphp

<div class="flex items-center gap-3">
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ $siteName }}" style="height: 32px; width: auto;">
    @endif
</div>
