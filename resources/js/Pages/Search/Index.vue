<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';

const props = defineProps({
    meta: Object,
    q: String,
    results: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');
const query = ref(props.q || '');

const labels = computed(() => (isAr.value ? {
    title: 'البحث في المقالات',
    placeholder: 'اكتب كلمة للبحث…',
    button: 'بحث',
    empty: 'لا توجد نتائج.',
    prompt: 'اكتب كلمتين أو أكثر للبدء.',
    count: (n) => `${n} نتيجة`,
} : {
    title: 'Search Articles',
    placeholder: 'Type a keyword…',
    button: 'Search',
    empty: 'No results found.',
    prompt: 'Type 2+ characters to begin.',
    count: (n) => `${n} result${n === 1 ? '' : 's'}`,
}));

const submit = () => {
    const base = isAr.value ? '/search' : '/en/search';
    router.get(base, { q: query.value }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <h1 class="search__title">{{ labels.title }}</h1>

                <form class="search__form" @submit.prevent="submit">
                    <input
                        v-model="query"
                        type="search"
                        class="search__input"
                        :placeholder="labels.placeholder"
                        autofocus
                    />
                    <button type="submit" class="btn">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        {{ labels.button }}
                    </button>
                </form>

                <p v-if="! q" class="search__hint">{{ labels.prompt }}</p>

                <template v-else-if="results && results.data.length">
                    <p class="search__count">{{ labels.count(results.total) }}</p>
                    <div class="grid grid--3">
                        <ArticleCard v-for="a in results.data" :key="a.id" :article="a" />
                    </div>
                </template>

                <p v-else class="search__hint">{{ labels.empty }}</p>
            </div>
        </section>
    </MainLayout>
</template>
