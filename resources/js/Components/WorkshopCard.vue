<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    workshop: { type: Object, required: true },
});

const page = usePage();
const locale = computed(() => page.props.locale);
const prefix = computed(() => (locale.value === 'en' ? '/en' : ''));
const slug = computed(() => (locale.value === 'en' ? props.workshop.slug_en : props.workshop.slug_ar));
const title = computed(() => (locale.value === 'en' ? props.workshop.title_en : props.workshop.title_ar));
const description = computed(() => (locale.value === 'en' ? props.workshop.description_en : props.workshop.description_ar));
const location = computed(() => (locale.value === 'en' ? props.workshop.location_en : props.workshop.location_ar));
const cover = computed(() => (locale.value === 'en' ? (props.workshop.cover_image_en || props.workshop.cover_image) : props.workshop.cover_image));
const platform = computed(() => (locale.value === 'en' ? (props.workshop.platform_en || props.workshop.platform) : props.workshop.platform));
const url = computed(() => `${prefix.value}/workshops/${slug.value}`);
const date = computed(() => {
    if (!props.workshop.event_date) return '';
    return new Date(props.workshop.event_date).toLocaleDateString(
        locale.value === 'ar' ? 'ar-SA' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );
});
</script>

<template>
    <article class="card">
        <Link :href="url" class="card__image" :aria-label="title">
            <img v-if="cover" :src="cover" :alt="title" loading="lazy" />
        </Link>
        <div class="card__body">
            <h3 class="card__title"><Link :href="url">{{ title }}</Link></h3>
            <p class="card__excerpt">{{ description }}</p>
            <div class="card__meta">
                <time v-if="date" :datetime="workshop.event_date">{{ date }}</time>
                <span v-if="location">{{ location }}</span>
                <span v-else-if="platform">{{ platform }}</span>
            </div>
        </div>
    </article>
</template>
