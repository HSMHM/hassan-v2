<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    portfolio: { type: Object, required: true },
});

const page = usePage();
const locale = computed(() => page.props.locale);
const prefix = computed(() => (locale.value === 'en' ? '/en' : ''));
const slug = computed(() => (locale.value === 'en' ? props.portfolio.slug_en : props.portfolio.slug_ar));
const title = computed(() => (locale.value === 'en' ? props.portfolio.title_en : props.portfolio.title_ar));
const description = computed(() => (locale.value === 'en' ? props.portfolio.description_en : props.portfolio.description_ar));
const category = computed(() => (locale.value === 'en' ? (props.portfolio.category_en || props.portfolio.category) : props.portfolio.category));
const url = computed(() => `${prefix.value}/portfolio/${slug.value}`);
</script>

<template>
    <article class="card">
        <Link :href="url" class="card__image" :aria-label="title">
            <img v-if="portfolio.cover_image" :src="portfolio.cover_image" :alt="title" loading="lazy" />
        </Link>
        <div class="card__body">
            <span v-if="category" class="card__category">{{ category }}</span>
            <h3 class="card__title"><Link :href="url">{{ title }}</Link></h3>
            <p class="card__excerpt">{{ description }}</p>
        </div>
    </article>
</template>
