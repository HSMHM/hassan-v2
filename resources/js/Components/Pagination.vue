<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, required: true },
});
</script>

<template>
    <nav v-if="links.length > 3" class="pagination" aria-label="Pagination">
        <template v-for="(link, idx) in links" :key="idx">
            <span
                v-if="!link.url"
                class="pagination__link pagination__link--disabled"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="pagination__link"
                :class="{ 'pagination__link--active': link.active }"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
