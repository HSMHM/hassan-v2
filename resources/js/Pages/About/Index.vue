<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    meta: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');
const settings = computed(() => page.props.settings || {});
const prefix = computed(() => (isAr.value ? '' : '/en'));

const ownerName = computed(() => settings.value.owner_name || 'Hassan Almalki');
const profession = computed(() => settings.value.profession || '');
const description = computed(() => settings.value.about_description || '');
const yearsExp = computed(() => settings.value.years_experience);

// Parse comma-separated skills into an array
const skills = computed(() => {
    const raw = settings.value.skills || '';
    return raw.split(',').map((s) => s.trim()).filter(Boolean);
});

const pmSkills = computed(() => {
    const raw = settings.value.pm_skills || '';
    return raw.split(',').map((s) => s.trim()).filter(Boolean);
});

// Parse languages: newline-separated "Name:Level" pairs
const languages = computed(() => {
    const raw = settings.value.languages || '';
    return raw.split('\n').map((line) => {
        const [name, level] = line.split(':').map((s) => (s || '').trim());
        return name ? { name, level: level || '' } : null;
    }).filter(Boolean);
});

const descriptionParagraphs = computed(() => description.value.split('\n').filter((p) => p.trim()));

const labels = computed(() => (isAr.value ? {
    contactMe: 'تواصل معي',
    downloadCv: 'تحميل السيرة الذاتية',
    yearsExp: 'سنوات خبرة',
    summary: 'نبذة عني',
    pmSkills: 'مهارات إدارة المنتجات',
    skills: 'المهارات التقنية',
    languages: 'المهارات اللغوية',
    ctaTitle: 'لنعمل معاً',
    ctaText: 'هل لديك مشروع أو فكرة تحتاج للتطوير؟ تواصل معي وسأساعدك في تحويلها إلى واقع.',
} : {
    contactMe: 'Contact Me',
    downloadCv: 'Download CV',
    yearsExp: 'Years',
    summary: 'Professional Summary',
    pmSkills: 'Product Management Skills',
    skills: 'Development Skills',
    languages: 'Language Skills',
    ctaTitle: "Let's Work Together",
    ctaText: 'Have a project or idea you want to build? Get in touch and I will help you bring it to life.',
}));
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="section">
            <div class="container">
                <div class="about">
                    <div class="about__hero">
                        <div class="about__photo-wrap">
                            <div class="about__photo-ring"></div>
                            <figure class="about__photo">
                                <img
                                    src="/img/about/hassan-almalki-web-developer.jpg"
                                    :alt="`${ownerName} - ${profession}`"
                                    loading="lazy"
                                />
                            </figure>
                            <div v-if="yearsExp" class="about__years-chip">
                                <span class="about__years-chip-number">{{ yearsExp }}</span>
                                <span class="about__years-chip-label">{{ labels.yearsExp }}</span>
                            </div>
                        </div>

                        <h1 class="about__name">{{ ownerName }}</h1>

                        <div class="about__actions">
                            <Link :href="`${prefix}/contact`" class="btn">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                                {{ labels.contactMe }}
                            </Link>
                            <a
                                v-if="settings.cv_url"
                                :href="settings.cv_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn--outline"
                            >
                                <i class="fa-solid fa-download" aria-hidden="true"></i>
                                {{ labels.downloadCv }}
                            </a>
                        </div>
                    </div>

                    <div class="project__grid">
                        <article v-if="descriptionParagraphs.length" class="project-card project-card--wide">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                {{ labels.summary }}
                            </h2>
                            <p v-for="(para, idx) in descriptionParagraphs" :key="idx">{{ para }}</p>
                        </article>

                        <article v-if="pmSkills.length" class="project-card project-card--wide project-card--accent">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-compass-drafting" aria-hidden="true"></i>
                                {{ labels.pmSkills }}
                            </h2>
                            <div class="project-card__tags">
                                <span v-for="(s, idx) in pmSkills" :key="idx" class="project-card__tag">{{ s }}</span>
                            </div>
                        </article>

                        <article v-if="skills.length" class="project-card project-card--wide">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-code" aria-hidden="true"></i>
                                {{ labels.skills }}
                            </h2>
                            <div class="project-card__tags">
                                <span v-for="(s, idx) in skills" :key="idx" class="project-card__tag">{{ s }}</span>
                            </div>
                        </article>

                        <article v-if="languages.length" class="project-card">
                            <h2 class="project-card__heading">
                                <i class="fa-solid fa-language" aria-hidden="true"></i>
                                {{ labels.languages }}
                            </h2>
                            <ul class="about__languages">
                                <li v-for="(lang, idx) in languages" :key="idx">
                                    <span class="about__lang-name">{{ lang.name }}</span>
                                    <span v-if="lang.level" class="about__lang-level">{{ lang.level }}</span>
                                </li>
                            </ul>
                        </article>
                    </div>

                    <section class="about__cta">
                        <h2 class="about__cta-title">{{ labels.ctaTitle }}</h2>
                        <p class="about__cta-text">{{ labels.ctaText }}</p>
                        <Link :href="`${prefix}/contact`" class="btn">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            {{ labels.contactMe }}
                        </Link>
                    </section>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
