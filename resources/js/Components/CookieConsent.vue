<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');
const visible = ref(false);

const labels = computed(() => (isAr.value ? {
    text: 'نستخدم ملفات تعريف الارتباط لتحسين تجربتك على الموقع.',
    accept: 'موافق',
} : {
    text: 'We use cookies to improve your experience on this site.',
    accept: 'Accept',
}));

onMounted(() => {
    try {
        if (localStorage.getItem('cookieConsent') !== 'true') {
            visible.value = true;
        }
    } catch {
        visible.value = true;
    }
});

function accept() {
    try {
        localStorage.setItem('cookieConsent', 'true');
    } catch {
        /* storage blocked */
    }
    visible.value = false;
}
</script>

<template>
    <div v-if="visible" class="cookie-consent" role="region" aria-live="polite">
        <p class="cookie-consent__text">{{ labels.text }}</p>
        <button type="button" class="cookie-consent__button" @click="accept">
            {{ labels.accept }}
        </button>
    </div>
</template>
