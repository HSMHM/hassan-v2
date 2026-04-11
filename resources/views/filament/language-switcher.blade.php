<div class="flex items-center gap-2 px-3 py-1" dir="ltr">
    <a
        href="{{ route('filament.admin.locale', ['locale' => 'ar']) }}"
        class="text-sm px-2 py-1 rounded {{ app()->getLocale() === 'ar' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
    >
        عربي
    </a>
    <a
        href="{{ route('filament.admin.locale', ['locale' => 'en']) }}"
        class="text-sm px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
    >
        EN
    </a>
</div>
