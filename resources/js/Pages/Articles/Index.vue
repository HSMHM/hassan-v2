<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
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
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <h1>{{ locale === 'ar' ? 'المقالات' : 'Articles' }}</h1>
                <div v-if="articles.data?.length" class="grid grid--3 mt-4">
                    <ArticleCard v-for="a in articles.data" :key="a.id" :article="a" />
                </div>
                <p v-else class="mt-4">{{ locale === 'ar' ? 'لا توجد مقالات حالياً.' : 'No articles yet.' }}</p>
                <Pagination v-if="articles.links" :links="articles.links" />
            </div>
        </section>
    </MainLayout>
</template>
