<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: { type: String, required: true },
    url: { type: String, default: '' },
    image: { type: String, default: '' },
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');

const fullUrl = computed(() => {
    if (props.url) return props.url;
    if (typeof window !== 'undefined') return window.location.href;
    return '';
});

const heading = computed(() => (isAr.value ? 'شارك المقال' : 'Share this article'));
const copiedLabel = computed(() => (isAr.value ? 'تم النسخ' : 'Copied!'));
const copyLabel = computed(() => (isAr.value ? 'نسخ الرابط' : 'Copy link'));

// IMPORTANT: share URL only (no pre-filled text) so WhatsApp/Telegram/LinkedIn
// unfurl the Open Graph preview card. Adding text or emojis suppresses the card.
const channels = computed(() => [
    {
        name: 'WhatsApp',
        icon: 'fa-brands fa-whatsapp',
        href: `https://wa.me/?text=${encodeURIComponent(fullUrl.value)}`,
    },
    {
        name: 'X',
        icon: 'fa-brands fa-x-twitter',
        // X accepts text+url; keep text short so the card still renders
        href: `https://twitter.com/intent/tweet?text=${encodeURIComponent(props.title)}&url=${encodeURIComponent(fullUrl.value)}`,
    },
    {
        name: 'LinkedIn',
        icon: 'fa-brands fa-linkedin-in',
        href: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(fullUrl.value)}`,
    },
    {
        name: 'Telegram',
        icon: 'fa-brands fa-telegram',
        href: `https://t.me/share/url?url=${encodeURIComponent(fullUrl.value)}&text=${encodeURIComponent(props.title)}`,
    },
    {
        name: 'Facebook',
        icon: 'fa-brands fa-facebook-f',
        href: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(fullUrl.value)}`,
    },
    {
        name: isAr.value ? 'بريد إلكتروني' : 'Email',
        icon: 'fa-solid fa-envelope',
        href: `mailto:?subject=${encodeURIComponent(props.title)}&body=${encodeURIComponent(fullUrl.value)}`,
    },
]);

function openShare(channel) {
    window.open(channel.href, '_blank', 'noopener,noreferrer,width=600,height=600');
}

const copied = ref(false);
async function copyLink() {
    try {
        await navigator.clipboard.writeText(fullUrl.value);
        copied.value = true;
        setTimeout(() => (copied.value = false), 1800);
    } catch {
        /* clipboard blocked */
    }
}
</script>

<template>
    <div class="share-buttons">
        <h3 class="share-buttons__heading">
            <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
            {{ heading }}
        </h3>

        <div class="share-buttons__list">
            <button
                v-for="ch in channels"
                :key="ch.name"
                class="share-btn"
                :title="ch.name"
                :aria-label="ch.name"
                @click="openShare(ch)"
            >
                <i :class="ch.icon" aria-hidden="true"></i>
                <span class="share-btn__label">{{ ch.name }}</span>
            </button>

            <button
                class="share-btn share-btn--copy"
                :title="copyLabel"
                :aria-label="copyLabel"
                @click="copyLink"
            >
                <i :class="copied ? 'fa-solid fa-check' : 'fa-solid fa-link'" aria-hidden="true"></i>
                <span class="share-btn__label">{{ copied ? copiedLabel : copyLabel }}</span>
            </button>
        </div>
    </div>
</template>
