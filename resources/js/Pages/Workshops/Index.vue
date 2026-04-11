<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import WorkshopCard from '@/Components/WorkshopCard.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    meta: Object,
    workshops: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <h1>{{ locale === 'ar' ? 'ورش العمل' : 'Workshops' }}</h1>
                <div v-if="workshops.data?.length" class="grid grid--3 mt-4">
                    <WorkshopCard v-for="w in workshops.data" :key="w.id" :workshop="w" />
                </div>
                <p v-else class="mt-4">{{ locale === 'ar' ? 'لا توجد ورش حالياً.' : 'No workshops yet.' }}</p>
                <Pagination v-if="workshops.links" :links="workshops.links" />
            </div>
        </section>
    </MainLayout>
</template>
