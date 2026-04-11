<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

defineProps({
    title1: { type: String, required: true },
    title2: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const root = ref(null);
let trigger = null;

onMounted(() => {
    if (! root.value) {
        return;
    }

    const parts = root.value.querySelectorAll('.section-title__part');
    const sub = root.value.querySelector('.section-title__subtitle');

    gsap.set(parts, { opacity: 0, y: 40, rotateX: -30 });
    if (sub) {
        gsap.set(sub, { opacity: 0, y: 24 });
    }

    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: root.value,
            start: 'top 90%',
            once: true,
        },
    });

    tl.to(parts, {
        opacity: 1,
        y: 0,
        rotateX: 0,
        duration: 0.9,
        stagger: 0.18,
        ease: 'back.out(1.6)',
    });
    if (sub) {
        tl.to(sub, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' }, '-=0.4');
    }

    trigger = tl.scrollTrigger;
});

onBeforeUnmount(() => {
    if (trigger) {
        trigger.kill();
        trigger = null;
    }
});
</script>

<template>
    <header ref="root" class="section-title">
        <h2 class="section-title__heading">
            <span class="section-title__part">{{ title1 }}</span>
            <span v-if="title2" class="section-title__part section-title__part--accent">{{ title2 }}</span>
        </h2>
        <p v-if="subtitle" class="section-title__subtitle">{{ subtitle }}</p>
    </header>
</template>
