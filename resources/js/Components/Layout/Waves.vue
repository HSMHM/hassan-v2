<script setup>
import { onBeforeUnmount, onMounted } from 'vue';

let initTimer = null;

onMounted(() => {
    if (typeof window === 'undefined') {
        return;
    }

    import('@/waves').then((module) => {
        const { waves } = module;
        initTimer = setTimeout(() => {
            waves();
        }, 200);
    });
});

onBeforeUnmount(() => {
    if (initTimer) {
        clearTimeout(initTimer);
    }
});
</script>

<template>
    <div class="waves" aria-hidden="true"></div>
</template>
