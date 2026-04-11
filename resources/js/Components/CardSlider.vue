<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    items: { type: Array, required: true },
});

const page = usePage();
const locale = computed(() => page.props.locale);

// The slider always renders LTR — slides flow left-to-right, prev on left,
// next on right — even when the page locale is Arabic. This matches the
// pattern most Arabic news/portfolio sites use for carousels: text inside
// the cards stays RTL (because it's content-driven) but the slider mechanics
// are consistent regardless of page direction.
const sliderDirection = 'ltr';

const root = ref(null);
const swiperInstance = ref(null);
const modules = [Navigation, Pagination, Autoplay];
let initialTrigger = null;
let hasRevealed = false;

const enableLoop = computed(() => props.items.length > 2);

// slidesPerView adapts to the item count so there is ALWAYS at least one
// slide hidden (guarantees overflow). Otherwise, when total items === perView,
// Swiper concludes there is nothing to scroll to and the slider freezes.
const breakpoints = computed(() => {
    const total = props.items.length;
    const desktopPerView = total > 3 ? 3 : Math.max(1, total - 1);
    const tabletPerView = total > 2 ? 2 : Math.max(1, total - 1);

    return {
        0: { slidesPerView: 1, spaceBetween: 16 },
        640: { slidesPerView: tabletPerView, spaceBetween: 20 },
        1024: { slidesPerView: desktopPerView, spaceBetween: 28 },
    };
});

// IMPORTANT: animate the inner .card, not .swiper-slide.
// Swiper needs total control of .swiper-slide transforms (especially in loop
// mode where it clones slides and translates them internally). Touching slide
// transforms with GSAP causes flicker, stuck slides, and stacking conflicts.
const getCardElements = () => {
    if (! root.value) {
        return [];
    }
    return Array.from(root.value.querySelectorAll('.swiper-slide .card'));
};

// shared rise-from-below tween config
const HIDDEN = { opacity: 0, y: 60 };
const VISIBLE = {
    opacity: 1,
    y: 0,
    duration: 0.9,
    stagger: 0.12,
    ease: 'power2.out',
    overwrite: 'auto',
};

const setInitialState = () => {
    const cards = getCardElements();
    if (! cards.length) {
        return;
    }
    gsap.set(cards, {
        ...HIDDEN,
        willChange: 'transform, opacity',
    });
};

const hideCardsInstant = () => {
    const cards = getCardElements();
    if (! cards.length) {
        return;
    }
    gsap.set(cards, HIDDEN);
};

const riseCards = () => {
    const cards = getCardElements();
    if (! cards.length) {
        return;
    }
    gsap.to(cards, VISIBLE);
};

const playInitialReveal = () => {
    if (hasRevealed) {
        return;
    }
    hasRevealed = true;
    riseCards();
};

const onBeforeSlide = () => {
    if (! hasRevealed) {
        return;
    }
    // INSTANTLY hide the current cards (no fade out). This runs while the
    // browser is still painting the previous frame, so the user never sees
    // the snap — by the time Swiper repositions slides for the new index,
    // the cards are already at y:60, opacity:0.
    hideCardsInstant();
};

const onAfterSlide = () => {
    if (! hasRevealed) {
        return;
    }
    // defer one animation frame so the browser has painted the hidden state
    // before we start animating back up. Without this, GSAP can coalesce the
    // set+to into a single tick and the hide is invisible.
    requestAnimationFrame(() => requestAnimationFrame(() => riseCards()));
};

const onSwiper = async (swiper) => {
    swiperInstance.value = swiper;

    // wait for Swiper to finish creating loop clones in the DOM
    await nextTick();
    setInitialState();

    // Swiper fires `beforeTransitionStart` BEFORE it translates the wrapper,
    // and `slideChange` AFTER the index has changed. Use both to hide-then-rise.
    swiper.on('beforeTransitionStart', onBeforeSlide);
    swiper.on('slideChange', onAfterSlide);

    if (! root.value) {
        return;
    }

    if (initialTrigger) {
        initialTrigger.kill();
    }

    initialTrigger = ScrollTrigger.create({
        trigger: root.value,
        start: 'top 85%',
        once: true,
        onEnter: playInitialReveal,
    });

    // if the slider is already in view on init (above the fold), trigger immediately
    ScrollTrigger.refresh();
};

onBeforeUnmount(() => {
    if (initialTrigger) {
        initialTrigger.kill();
        initialTrigger = null;
    }
    const sw = swiperInstance.value;
    if (sw) {
        sw.off('beforeTransitionStart', onBeforeSlide);
        sw.off('slideChange', onAfterSlide);
    }
});

// when the page locale changes (RTL ↔ LTR), refresh ScrollTrigger so any
// layout shift caused by the html dir change is recomputed
watch(locale, async () => {
    await nextTick();
    if (swiperInstance.value && typeof swiperInstance.value.update === 'function') {
        swiperInstance.value.update();
    }
    ScrollTrigger.refresh();
});
</script>

<template>
    <div ref="root" class="card-slider">
        <Swiper
            :dir="sliderDirection"
            :modules="modules"
            :loop="enableLoop"
            :slides-per-view="1"
            :space-between="16"
            :speed="0"
            :allow-touch-move="false"
            :breakpoints="breakpoints"
            :navigation="{ nextEl: '.card-slider__next', prevEl: '.card-slider__prev' }"
            :pagination="{ clickable: true, el: '.card-slider__pagination' }"
            :autoplay="{ delay: 5500, disableOnInteraction: false, pauseOnMouseEnter: true }"
            :watch-slides-progress="true"
            :watch-overflow="false"
            @swiper="onSwiper"
        >
            <SwiperSlide v-for="(item, idx) in items" :key="item.id ?? idx">
                <slot name="slide" :item="item" :index="idx" />
            </SwiperSlide>
        </Swiper>

        <div class="card-slider__controls" dir="ltr">
            <button type="button" class="card-slider__nav card-slider__prev" :aria-label="locale === 'ar' ? 'السابق' : 'Previous'">
                <span aria-hidden="true">‹</span>
            </button>
            <div class="card-slider__pagination"></div>
            <button type="button" class="card-slider__nav card-slider__next" :aria-label="locale === 'ar' ? 'التالي' : 'Next'">
                <span aria-hidden="true">›</span>
            </button>
        </div>
    </div>
</template>
