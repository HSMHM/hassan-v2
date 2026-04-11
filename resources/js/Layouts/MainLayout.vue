<script setup>
import { computed, onMounted, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Header from '@/Components/Layout/Header.vue';
import Footer from '@/Components/Layout/Footer.vue';
import Breadcrumb from '@/Components/Layout/Breadcrumb.vue';
import Waves from '@/Components/Layout/Waves.vue';

const props = defineProps({
    meta: { type: Object, default: () => ({}) },
    breadcrumbs: { type: Array, default: null },
});

const page = usePage();
const locale = computed(() => page.props.locale);
const direction = computed(() => (locale.value === 'ar' ? 'rtl' : 'ltr'));

const applyDocumentLocale = () => {
    if (typeof document === 'undefined') {
        return;
    }
    document.documentElement.lang = locale.value;
    document.documentElement.dir = direction.value;
};

onMounted(applyDocumentLocale);
watch([locale, direction], applyDocumentLocale, { immediate: true });

const jsonLdString = computed(() => {
    const blocks = props.meta?.jsonLd ?? [];
    if (!blocks.length) return null;
    return blocks.map((b) => JSON.stringify(b)).join('\n');
});
</script>

<template>
    <Head>
        <title>{{ meta.title || 'Hassan Almalki' }}</title>
        <meta v-if="meta.description" name="description" :content="meta.description" />
        <meta v-if="meta.robots" name="robots" :content="meta.robots" />
        <link v-if="meta.canonical" rel="canonical" :href="meta.canonical" />

        <template v-if="meta.og">
            <meta property="og:title" :content="meta.og.title" />
            <meta property="og:description" :content="meta.og.description" />
            <meta property="og:image" :content="meta.og.image" />
            <meta property="og:url" :content="meta.og.url" />
            <meta property="og:type" :content="meta.og.type" />
            <meta property="og:locale" :content="meta.og.locale" />
            <meta property="og:site_name" :content="meta.og.site_name" />
        </template>

        <template v-if="meta.twitter">
            <meta name="twitter:card" :content="meta.twitter.card" />
            <meta name="twitter:site" :content="meta.twitter.site" />
            <meta name="twitter:creator" :content="meta.twitter.creator" />
            <meta name="twitter:title" :content="meta.twitter.title" />
            <meta name="twitter:description" :content="meta.twitter.description" />
            <meta name="twitter:image" :content="meta.twitter.image" />
        </template>

        <template v-if="meta.alternates">
            <link
                v-for="alt in meta.alternates"
                :key="alt.hreflang"
                rel="alternate"
                :hreflang="alt.hreflang"
                :href="alt.href"
            />
        </template>

        <component v-if="jsonLdString" :is="'script'" type="application/ld+json" v-html="jsonLdString" />
    </Head>

    <a href="#main" class="skip-link">{{ locale === 'ar' ? 'تخطّي إلى المحتوى' : 'Skip to content' }}</a>
    <Waves />
    <Header />
    <Breadcrumb v-if="breadcrumbs" :items="breadcrumbs" />
    <main id="main" class="main">
        <slot />
    </main>
    <Footer />
</template>
