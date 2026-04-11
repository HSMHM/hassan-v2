<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    meta: Object,
    portfolio: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');

const title = computed(() => (isAr.value ? props.portfolio.title_ar : props.portfolio.title_en));
const description = computed(() => (isAr.value ? props.portfolio.description_ar : props.portfolio.description_en));
const category = computed(() => (isAr.value ? props.portfolio.category : (props.portfolio.category_en || props.portfolio.category)));
const content = computed(() => (isAr.value ? props.portfolio.content_ar : props.portfolio.content_en));

const features = computed(() => {
    const f = props.portfolio.features?.[locale.value] ?? [];
    return Array.isArray(f) ? f : Object.values(f);
});

const tech = computed(() => props.portfolio.features?.tech ?? []);

const outcomes = computed(() => {
    const key = isAr.value ? 'outcomes_ar' : 'outcomes_en';
    return props.portfolio.features?.[key] ?? [];
});

const challenge = computed(() => {
    const key = isAr.value ? 'challenge_ar' : 'challenge_en';
    return props.portfolio.features?.[key] ?? null;
});

const approach = computed(() => {
    const key = isAr.value ? 'approach_ar' : 'approach_en';
    return props.portfolio.features?.[key] ?? null;
});

const labels = computed(() => (isAr.value ? {
    visit: 'زيارة المشروع',
    overview: 'نظرة عامة',
    challenge: 'التحدي',
    approach: 'المنهجية والحل',
    features: 'المميزات الرئيسية',
    tech: 'التقنيات المستخدمة',
    outcomes: 'النتائج والأثر',
} : {
    visit: 'Visit Project',
    overview: 'Overview',
    challenge: 'The Challenge',
    approach: 'Approach & Solution',
    features: 'Key Features',
    tech: 'Technologies',
    outcomes: 'Outcomes & Impact',
}));
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <div class="project">
                    <header class="project__header">
                        <div class="project__info">
                            <span v-if="category" class="card__category">{{ category }}</span>
                            <h1 class="project__title">{{ title }}</h1>
                            <p v-if="description" class="project__lead">{{ description }}</p>
                            <a
                                v-if="portfolio.project_url"
                                :href="portfolio.project_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn"
                            >
                                {{ labels.visit }} →
                            </a>
                        </div>
                        <figure v-if="portfolio.cover_image" class="project__cover">
                            <img :src="portfolio.cover_image" :alt="title" />
                        </figure>
                    </header>

                    <div class="project__grid">
                        <article v-if="description" class="project-card project-card--wide">
                            <h2 class="project-card__heading">{{ labels.overview }}</h2>
                            <p>{{ description }}</p>
                        </article>

                        <article v-if="challenge" class="project-card">
                            <h2 class="project-card__heading">{{ labels.challenge }}</h2>
                            <p>{{ challenge }}</p>
                        </article>

                        <article v-if="approach" class="project-card">
                            <h2 class="project-card__heading">{{ labels.approach }}</h2>
                            <p>{{ approach }}</p>
                        </article>

                        <article v-if="features.length" class="project-card">
                            <h2 class="project-card__heading">{{ labels.features }}</h2>
                            <ul class="project-card__list">
                                <li v-for="(feature, idx) in features" :key="idx">{{ feature }}</li>
                            </ul>
                        </article>

                        <article v-if="tech.length" class="project-card">
                            <h2 class="project-card__heading">{{ labels.tech }}</h2>
                            <div class="project-card__tags">
                                <span v-for="(t, idx) in tech" :key="idx" class="project-card__tag">{{ t }}</span>
                            </div>
                        </article>

                        <article v-if="outcomes.length" class="project-card project-card--accent">
                            <h2 class="project-card__heading">{{ labels.outcomes }}</h2>
                            <ul class="project-card__outcomes">
                                <li v-for="(o, idx) in outcomes" :key="idx">
                                    <span class="project-card__outcome-dot"></span>{{ o }}
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
