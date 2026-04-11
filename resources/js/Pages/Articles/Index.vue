<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    meta: Object,
    articles: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');
const query = ref('');

const placeholder = computed(() =>
    isAr.value ? 'ابحث في المقالات…' : 'Search articles…'
);
const buttonLabel = computed(() => (isAr.value ? 'بحث' : 'Search'));

const submitSearch = () => {
    const q = query.value.trim();
    if (q.length < 2) return;
    const base = isAr.value ? '/search' : '/en/search';
    router.get(base, { q });
};
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <h1>{{ locale === 'ar' ? 'المقالات' : 'Articles' }}</h1>

                <form class="search__form mt-4" @submit.prevent="submitSearch">
                    <input
                        v-model="query"
                        type="search"
                        class="search__input"
                        :placeholder="placeholder"
                        :aria-label="placeholder"
                    />
                    <button type="submit" class="btn">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        {{ buttonLabel }}
                    </button>
                </form>

                <div v-if="articles.data?.length" class="grid grid--3 mt-4">
                    <ArticleCard v-for="a in articles.data" :key="a.id" :article="a" />
                </div>
                <p v-else class="mt-4">{{ locale === 'ar' ? 'لا توجد مقالات حالياً.' : 'No articles yet.' }}</p>
                <Pagination v-if="articles.links" :links="articles.links" />
            </div>
        </section>
    </MainLayout>
</template>
