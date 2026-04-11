<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import gsap from 'gsap';
import MainLayout from '@/Layouts/MainLayout.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import CardSlider from '@/Components/CardSlider.vue';
import ArticleCard from '@/Components/ArticleCard.vue';
import PortfolioCard from '@/Components/PortfolioCard.vue';
import WorkshopCard from '@/Components/WorkshopCard.vue';
import AnimationText from '@/Components/AnimationText.vue';

defineProps({
    meta: Object,
    articles: Array,
    portfolios: Array,
    workshops: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
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

const prefix = computed(() => (locale.value === 'en' ? '/en' : ''));

const heroRoot = ref(null);

onMounted(() => {
    if (! heroRoot.value) {
        return;
    }

    const nameParts = heroRoot.value.querySelectorAll('.hero__name-part');
    const job = heroRoot.value.querySelector('.hero__job');
    const buttons = heroRoot.value.querySelectorAll('.hero__buttons .btn');

    gsap.set(nameParts, { opacity: 0, y: 40, rotateX: -35 });
    gsap.set(job, { opacity: 0, y: 20 });
    gsap.set(buttons, { opacity: 0, y: 16 });

    const tl = gsap.timeline({ delay: 0.15 });

    tl.to(nameParts, {
        opacity: 1,
        y: 0,
        rotateX: 0,
        duration: 0.9,
        stagger: 0.18,
        ease: 'back.out(1.4)',
    });
    tl.to(job, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out' }, '-=0.4');
    tl.to(buttons, { opacity: 1, y: 0, duration: 0.5, stagger: 0.12, ease: 'power3.out' }, '-=0.3');
});
</script>

<template>
    <MainLayout :meta="meta">
        <section ref="heroRoot" class="hero">
            <div class="container">
                <div class="hero__inner">
                    <h1 class="hero__name">
                        <span class="hero__name-part">{{ t('home.firstName') }}</span>
                        <span class="hero__name-part coloring">{{ t('home.lastName') }}</span>
                    </h1>
                    <h3 class="hero__job">
                        <AnimationText />
                    </h3>
                    <div class="hero__buttons">
                        <Link :href="`${prefix}/contact`" class="btn">
                            {{ t('home.getInTouch') }}
                        </Link>
                        <Link :href="`${prefix}/about`" class="btn btn--outline">
                            {{ t('home.aboutMe') }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="portfolios?.length" class="section section--alt">
            <div class="container">
                <SectionTitle :title1="t('portfolio.title_1')" :title2="t('portfolio.title_2')" />
                <CardSlider :items="portfolios">
                    <template #slide="{ item }">
                        <PortfolioCard :portfolio="item" />
                    </template>
                </CardSlider>
                <p class="text-center mt-4">
                    <Link :href="`${prefix}/portfolio`" class="btn btn--outline">
                        {{ locale === 'ar' ? 'عرض جميع الأعمال' : 'View all projects' }}
                    </Link>
                </p>
            </div>
        </section>

        <section v-if="articles?.length" class="section section--reverse">
            <div class="container">
                <SectionTitle :title1="t('articles.title_1')" :title2="t('articles.title_2')" />
                <CardSlider :items="articles">
                    <template #slide="{ item }">
                        <ArticleCard :article="item" />
                    </template>
                </CardSlider>
                <p class="text-center mt-4">
                    <Link :href="`${prefix}/articles`" class="btn btn--outline">
                        {{ locale === 'ar' ? 'عرض جميع المقالات' : 'View all articles' }}
                    </Link>
                </p>
            </div>
        </section>

        <section v-if="workshops?.length" class="section">
            <div class="container">
                <SectionTitle :title1="t('workshops.title_1')" :title2="t('workshops.title_2')" />
                <CardSlider :items="workshops">
                    <template #slide="{ item }">
                        <WorkshopCard :workshop="item" />
                    </template>
                </CardSlider>
                <p class="text-center mt-4">
                    <Link :href="`${prefix}/workshops`" class="btn btn--outline">
                        {{ locale === 'ar' ? 'عرض جميع الورش' : 'View all workshops' }}
                    </Link>
                </p>
            </div>
        </section>
    </MainLayout>
</template>
