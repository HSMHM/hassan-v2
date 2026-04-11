<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import gsap from 'gsap';

const page = usePage();
const t = (path) => {
    const parts = path.split('.');
    let cur = page.props.translations || {};
    for (const p of parts) {
        if (cur && typeof cur === 'object' && p in cur) {
            cur = cur[p];
        } else {
            return path;
        }
    }
    return cur;
};

const skills = computed(() => [
    t('home.titles.designer'),
    t('home.titles.systems_analyst'),
    t('home.titles.business_analyst'),
    t('home.titles.product_manager'),
]);

const intro = computed(() => t('home.intro'));

const wordRefs = ref([]);

const setWordRef = (el, idx) => {
    if (el) {
        wordRefs.value[idx] = el;
    }
};

let timeline = null;

const buildTimeline = () => {
    if (timeline) {
        timeline.kill();
        timeline = null;
    }

    if (! wordRefs.value.length) {
        return;
    }

    // Reset all words to hidden, then show the first one immediately
    gsap.set(wordRefs.value, { opacity: 0, y: 24, rotateX: -50 });
    gsap.set(wordRefs.value[0], { opacity: 1, y: 0, rotateX: 0 });

    timeline = gsap.timeline({ repeat: -1, repeatRefresh: false });

    wordRefs.value.forEach((current, i) => {
        const next = wordRefs.value[(i + 1) % wordRefs.value.length];

        // hold the current word for 1.5s
        timeline.to({}, { duration: 1.5 });

        // exit current
        timeline.to(current, {
            opacity: 0,
            y: -24,
            rotateX: 50,
            duration: 0.4,
            ease: 'power3.in',
        });

        // explicit reset of next so each enter starts from the same place
        timeline.set(next, { opacity: 0, y: 24, rotateX: -50 });

        // enter next (slight overlap with exit)
        timeline.to(next, {
            opacity: 1,
            y: 0,
            rotateX: 0,
            duration: 0.5,
            ease: 'back.out(1.4)',
        }, '-=0.1');
    });
};

onMounted(async () => {
    await nextTick();
    buildTimeline();
});

watch(skills, async () => {
    await nextTick();
    buildTimeline();
});

onBeforeUnmount(() => {
    if (timeline) {
        timeline.kill();
        timeline = null;
    }
});
</script>

<template>
    <span class="anim-headline">
        <span class="anim-headline__intro">{{ intro }}</span>
        <span class="anim-headline__words">
            <b
                v-for="(skill, i) in skills"
                :key="i"
                :ref="(el) => setWordRef(el, i)"
                class="anim-headline__word"
            >
                {{ skill }}
            </b>
        </span>
    </span>
</template>
