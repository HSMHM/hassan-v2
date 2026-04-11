<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    meta: Object,
    workshop: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');

const title = computed(() => (isAr.value ? props.workshop.title_ar : props.workshop.title_en));
const description = computed(() => (isAr.value ? props.workshop.description_ar : props.workshop.description_en));
const platform = computed(() => (isAr.value ? props.workshop.platform : (props.workshop.platform_en || props.workshop.platform)));
const location = computed(() => (isAr.value ? props.workshop.location_ar : props.workshop.location_en));
const cover = computed(() => (isAr.value ? props.workshop.cover_image : (props.workshop.cover_image_en || props.workshop.cover_image)));

const extras = computed(() => props.workshop.extras || {});
const objectives = computed(() => extras.value[isAr.value ? 'objectives_ar' : 'objectives_en'] ?? []);
const audience = computed(() => extras.value[isAr.value ? 'audience_ar' : 'audience_en'] ?? []);
const topics = computed(() => extras.value[isAr.value ? 'topics_ar' : 'topics_en'] ?? []);
const outcomes = computed(() => extras.value[isAr.value ? 'outcomes_ar' : 'outcomes_en'] ?? []);
const duration = computed(() => extras.value[isAr.value ? 'duration_ar' : 'duration_en']);

// Normalize any YouTube URL to an embeddable form (https://www.youtube.com/embed/VIDEO_ID)
const embedUrl = computed(() => {
    const url = props.workshop.video_url;
    if (! url) return null;
    if (url.includes('/embed/')) return url;

    // Extract video ID from watch?v= or youtu.be/ links
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
    if (match) {
        return `https://www.youtube.com/embed/${match[1]}`;
    }
    return url;
});

const eventDate = computed(() => {
    if (! props.workshop.event_date) return '';
    return new Date(props.workshop.event_date).toLocaleDateString(
        isAr.value ? 'ar-SA' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
});

const labels = computed(() => (isAr.value ? {
    overview: 'نظرة عامة',
    objectives: 'أهداف الورشة',
    audience: 'الفئة المستهدفة',
    topics: 'محاور الورشة',
    outcomes: 'المخرجات المتوقعة',
    video: 'تسجيل الورشة',
    videoTitle: 'شاهد الورشة كاملة',
    date: 'تاريخ الورشة',
    duration: 'المدة',
    platform: 'المنصة',
    location: 'الموقع',
} : {
    overview: 'Overview',
    objectives: 'Learning Objectives',
    audience: 'Target Audience',
    topics: 'Topics Covered',
    outcomes: 'Expected Outcomes',
    video: 'Workshop Recording',
    videoTitle: 'Watch the full workshop',
    date: 'Date',
    duration: 'Duration',
    platform: 'Platform',
    location: 'Location',
}));

const metaBadges = computed(() => {
    const out = [];
    if (eventDate.value) out.push({ icon: 'fa-solid fa-calendar-days', label: labels.value.date, value: eventDate.value });
    if (duration.value) out.push({ icon: 'fa-solid fa-clock', label: labels.value.duration, value: duration.value });
    if (platform.value) out.push({ icon: 'fa-solid fa-laptop', label: labels.value.platform, value: platform.value });
    if (location.value) out.push({ icon: 'fa-solid fa-location-dot', label: labels.value.location, value: location.value });
    return out;
});
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <div class="project">
                    <header class="project__header">
                        <div class="project__info">
                            <h1 class="project__title">{{ title }}</h1>
                            <p v-if="description" class="project__lead">{{ description }}</p>

                            <div v-if="metaBadges.length" class="workshop__meta">
                                <div v-for="(m, idx) in metaBadges" :key="idx" class="workshop__meta-item">
                                    <span class="workshop__meta-icon" aria-hidden="true">
                                        <i :class="m.icon"></i>
                                    </span>
                                    <div>
                                        <div class="workshop__meta-label">{{ m.label }}</div>
                                        <div class="workshop__meta-value">{{ m.value }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <figure v-if="cover" class="project__cover">
                            <img :src="cover" :alt="title" />
                        </figure>
                    </header>

                    <div class="project__grid">
                        <article v-if="embedUrl" class="project-card project-card--wide workshop__video-card">
                            <h2 class="project-card__heading">{{ labels.video }}</h2>
                            <div class="workshop__video-frame">
                                <iframe
                                    :src="embedUrl"
                                    :title="labels.videoTitle + ' — ' + title"
                                    frameborder="0"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </article>

                        <article v-if="description" class="project-card project-card--wide">
                            <h2 class="project-card__heading">{{ labels.overview }}</h2>
                            <p>{{ description }}</p>
                        </article>

                        <article v-if="objectives.length" class="project-card">
                            <h2 class="project-card__heading">{{ labels.objectives }}</h2>
                            <ul class="project-card__list">
                                <li v-for="(o, idx) in objectives" :key="idx">{{ o }}</li>
                            </ul>
                        </article>

                        <article v-if="audience.length" class="project-card">
                            <h2 class="project-card__heading">{{ labels.audience }}</h2>
                            <ul class="project-card__list">
                                <li v-for="(a, idx) in audience" :key="idx">{{ a }}</li>
                            </ul>
                        </article>

                        <article v-if="topics.length" class="project-card">
                            <h2 class="project-card__heading">{{ labels.topics }}</h2>
                            <ul class="project-card__list">
                                <li v-for="(t, idx) in topics" :key="idx">{{ t }}</li>
                            </ul>
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
