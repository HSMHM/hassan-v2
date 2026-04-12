<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    article: { type: Object, required: true },
});

const page = usePage();
const locale = computed(() => page.props.locale);
const prefix = computed(() => (locale.value === 'en' ? '/en' : ''));
const slug = computed(() => (locale.value === 'en' ? props.article.slug_en : props.article.slug_ar));
const title = computed(() => (locale.value === 'en' ? props.article.title_en : props.article.title_ar));
const excerpt = computed(() => (locale.value === 'en' ? props.article.excerpt_en : props.article.excerpt_ar));
const cover = computed(() => (locale.value === 'en' ? (props.article.cover_image_en || props.article.cover_image) : props.article.cover_image));
const url = computed(() => `${prefix.value}/articles/${slug.value}`);
const date = computed(() => {
    if (!props.article.published_at) return '';
    return new Date(props.article.published_at).toLocaleDateString(
        locale.value === 'ar' ? 'ar-SA' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
});
</script>

<template>
    <article class="card">
        <span v-if="article.is_news" class="card__badge">
            {{ locale === 'ar' ? 'خبر' : 'News' }}
        </span>
        <Link :href="url" class="card__image" :aria-label="title">
            <img
                v-if="cover"
                :src="cover"
                :alt="title"
                loading="lazy"
            />
        </Link>
        <div class="card__body">
            <h3 class="card__title">
                <Link :href="url">{{ title }}</Link>
            </h3>
            <p class="card__excerpt">{{ excerpt }}</p>
            <div class="card__meta">
                <time v-if="date" :datetime="article.published_at">{{ date }}</time>
            </div>
        </div>
    </article>
</template>
