<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    href: { type: String, default: null },
    icon: { type: String, default: '' },
});

// If `icon` looks like a Font Awesome class (e.g. "fa-solid fa-envelope"),
// render it as an <i> element. Otherwise render the raw string (fallback for
// legacy single-character icons).
const isFaIcon = computed(() => typeof props.icon === 'string' && props.icon.includes('fa-'));
</script>

<template>
    <component
        :is="href ? 'a' : 'div'"
        :href="href || undefined"
        :target="href && href.startsWith('http') ? '_blank' : null"
        :rel="href && href.startsWith('http') ? 'noopener noreferrer' : null"
        class="contact-card"
    >
        <div class="contact-card__icon" aria-hidden="true">
            <i v-if="isFaIcon" :class="icon"></i>
            <span v-else>{{ icon || label.charAt(0) }}</span>
        </div>
        <div>
            <div class="contact-card__label">{{ label }}</div>
            <div class="contact-card__value">{{ value }}</div>
        </div>
    </component>
</template>
