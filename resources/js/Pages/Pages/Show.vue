<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    meta: Object,
    page: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');

const title = computed(() => (isAr.value ? props.page.title_ar : props.page.title_en));
const content = computed(() => (isAr.value ? props.page.content_ar : props.page.content_en));

const updatedAt = computed(() => {
    if (!props.page.updated_at) return '';
    return new Date(props.page.updated_at).toLocaleDateString(
        isAr.value ? 'ar-SA' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
});

const labels = computed(() => (isAr.value ? {
    lastUpdated: 'آخر تحديث',
} : {
    lastUpdated: 'Last Updated',
}));
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <div class="project">
                    <header class="project__header">
                        <div class="project__info">
                            <h1 class="project__title">{{ title }}</h1>

                            <div v-if="updatedAt" class="workshop__meta">
                                <div class="workshop__meta-item">
                                    <span class="workshop__meta-icon" aria-hidden="true">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </span>
                                    <div>
                                        <div class="workshop__meta-label">{{ labels.lastUpdated }}</div>
                                        <div class="workshop__meta-value">{{ updatedAt }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="project__grid">
                        <article class="project-card project-card--wide">
                            <div class="article__content" v-html="content" />
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
