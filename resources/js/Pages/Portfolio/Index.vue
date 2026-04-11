<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PortfolioCard from '@/Components/PortfolioCard.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    meta: Object,
    portfolios: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <h1>{{ locale === 'ar' ? 'الأعمال' : 'Portfolio' }}</h1>
                <div v-if="portfolios.data?.length" class="grid grid--3 mt-4">
                    <PortfolioCard v-for="p in portfolios.data" :key="p.id" :portfolio="p" />
                </div>
                <p v-else class="mt-4">{{ locale === 'ar' ? 'لا توجد أعمال حالياً.' : 'No projects yet.' }}</p>
                <Pagination v-if="portfolios.links" :links="portfolios.links" />
            </div>
        </section>
    </MainLayout>
</template>
