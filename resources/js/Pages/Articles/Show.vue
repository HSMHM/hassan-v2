<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import ShareButtons from '@/Components/ShareButtons.vue';

const props = defineProps({
    meta: Object,
    article: Object,
    reading_time: String,
    related: Array,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');

const title = computed(() => (isAr.value ? props.article.title_ar : props.article.title_en));
const excerpt = computed(() => (isAr.value ? props.article.excerpt_ar : props.article.excerpt_en));
const content = computed(() => (isAr.value ? props.article.content_ar : props.article.content_en));
const cover = computed(() => {
    if (isAr.value) {
        return props.article.og_image || props.article.cover_image;
    }
    return props.article.og_image_en || props.article.cover_image_en || props.article.og_image || props.article.cover_image;
});

const extras = computed(() => props.article.extras || {});
const readingTime = computed(() =>
    extras.value[isAr.value ? 'reading_time_ar' : 'reading_time_en'] || props.reading_time
);
const takeaways = computed(() => extras.value[isAr.value ? 'takeaways_ar' : 'takeaways_en'] ?? []);
const tags = computed(() => extras.value[isAr.value ? 'tags_ar' : 'tags_en'] ?? []);
const references = computed(() => extras.value.references ?? []);

const publishedDate = computed(() => {
    if (! props.article.published_at) return '';
    return new Date(props.article.published_at).toLocaleDateString(
        isAr.value ? 'ar-SA' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
});

const labels = computed(() => (isAr.value ? {
    publishedOn: 'تاريخ النشر',
    readingTime: 'وقت القراءة',
    takeaways: 'أهم النقاط',
    article: 'المقال',
    tags: 'الوسوم',
    references: 'المراجع',
    related: 'مقالات ذات صلة',
    externalLink: 'رابط خارجي',
} : {
    publishedOn: 'Published',
    readingTime: 'Reading Time',
    takeaways: 'Key Takeaways',
    article: 'Article',
    tags: 'Tags',
    references: 'References',
    related: 'Related Articles',
    externalLink: 'External link',
}));

const metaBadges = computed(() => {
    const out = [];
    if (publishedDate.value) out.push({ icon: 'fa-solid fa-calendar-days', label: labels.value.publishedOn, value: publishedDate.value });
    if (readingTime.value) out.push({ icon: 'fa-solid fa-clock', label: labels.value.readingTime, value: readingTime.value });
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
                            <p v-if="excerpt" class="project__lead">{{ excerpt }}</p>

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
                        <article v-if="takeaways.length" class="project-card project-card--wide project-card--accent">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                                {{ labels.takeaways }}
                            </h2>
                            <ul class="project-card__outcomes">
                                <li v-for="(t, idx) in takeaways" :key="idx">
                                    <span class="project-card__outcome-dot"></span>{{ t }}
                                </li>
                            </ul>
                        </article>

                        <article class="project-card project-card--wide">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                                {{ labels.article }}
                            </h2>
                            <div class="article__content" v-html="content" />
                        </article>

                        <article v-if="tags.length" class="project-card">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                                {{ labels.tags }}
                            </h2>
                            <div class="project-card__tags">
                                <span v-for="(tag, idx) in tags" :key="idx" class="project-card__tag">{{ tag }}</span>
                            </div>
                        </article>

                        <article v-if="references.length" class="project-card">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-link" aria-hidden="true"></i>
                                {{ labels.references }}
                            </h2>
                            <ul class="project-card__refs">
                                <li v-for="(ref, idx) in references" :key="idx">
                                    <a
                                        :href="ref.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        :title="ref.title"
                                    >
                                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                        {{ ref.title }}
                                    </a>
                                </li>
                            </ul>
                        </article>
                    </div>

                    <ShareButtons :title="title" :url="meta?.canonical" :image="cover" />
                </div>

                <section v-if="related?.length" class="mt-4">
                    <SectionTitle :title1="labels.related" />
                    <div class="grid grid--3">
                        <ArticleCard v-for="a in related" :key="a.id" :article="a" />
                    </div>
                </section>
            </div>
        </section>
    </MainLayout>
</template>
