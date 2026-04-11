<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    items: { type: Array, required: true },
});

const page = usePage();
const baseUrl = computed(() => page.props.ziggy?.url || '');

const jsonLd = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: props.items.map((item, i) => ({
        '@type': 'ListItem',
        position: i + 1,
        name: item.label,
        ...(item.url ? { item: `${baseUrl.value.replace(/\/$/, '')}${item.url}` } : {}),
    })),
}));
</script>

<template>
    <nav class="breadcrumb" :aria-label="$page.props.locale === 'ar' ? 'مسار التنقل' : 'Breadcrumb'">
        <div class="container">
            <ol class="breadcrumb__list">
                <li v-for="(item, idx) in items" :key="idx" class="breadcrumb__item">
                    <Link v-if="item.url && idx < items.length - 1" :href="item.url" class="breadcrumb__link">
                        {{ item.label }}
                    </Link>
                    <span v-else class="breadcrumb__current">{{ item.label }}</span>
                </li>
            </ol>
        </div>
        <component :is="'script'" type="application/ld+json" v-html="jsonLd" />
    </nav>
</template>
