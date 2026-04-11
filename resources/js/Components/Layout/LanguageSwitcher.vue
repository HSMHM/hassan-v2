<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const locale = computed(() => page.props.locale);

const otherUrl = computed(() => {
    const target = locale.value === 'ar' ? 'en' : 'ar';
    const alternates = page.props.meta?.alternates || [];
    const match = alternates.find((a) => a.hreflang === target);

    if (match?.href) {
        try {
            const u = new URL(match.href);
            return u.pathname + u.search + u.hash;
        } catch {
            return match.href;
        }
    }

    // Fallback — pages that don't provide alternates
    const url = page.url || '/';
    if (locale.value === 'ar') {
        return url === '/' ? '/en' : `/en${url}`;
    }
    return url.replace(/^\/en/, '') || '/';
});

const label = computed(() => (locale.value === 'ar' ? 'EN' : 'AR'));
</script>

<template>
    <Link :href="otherUrl" class="header__lang" :hreflang="locale === 'ar' ? 'en' : 'ar'">
        {{ label }}
    </Link>
</template>
