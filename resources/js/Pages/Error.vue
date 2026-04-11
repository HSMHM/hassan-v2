<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    status: { type: Number, required: true },
});

const page = usePage();
const locale = computed(() => page.props.locale);
const isAr = computed(() => locale.value === 'ar');
const prefix = computed(() => (isAr.value ? '' : '/en'));

const labels = computed(() => {
    const map = {
        403: {
            ar: { title: '403', heading: 'لا تملك الصلاحية', text: 'هذه الصفحة مقيدة الوصول.' },
            en: { title: '403', heading: 'Forbidden', text: 'You do not have permission to view this page.' },
        },
        404: {
            ar: { title: '404', heading: 'الصفحة غير موجودة', text: 'الصفحة التي تبحث عنها غير موجودة أو تم نقلها.' },
            en: { title: '404', heading: 'Page Not Found', text: 'The page you are looking for does not exist or has been moved.' },
        },
        500: {
            ar: { title: '500', heading: 'خطأ في الخادم', text: 'حدث خطأ غير متوقع. سنصلحه في أقرب وقت.' },
            en: { title: '500', heading: 'Server Error', text: 'Something went wrong on our end. We are looking into it.' },
        },
        503: {
            ar: { title: '503', heading: 'الموقع تحت الصيانة', text: 'نعتذر عن الإزعاج، سنعود قريباً.' },
            en: { title: '503', heading: 'Be Right Back', text: 'The site is under maintenance. We will be back shortly.' },
        },
    };
    const l = isAr.value ? 'ar' : 'en';
    return map[props.status]?.[l] ?? map[404][l];
});

const homeLabel = computed(() => (isAr.value ? 'العودة للرئيسية' : 'Back to Home'));

const meta = computed(() => ({
    title: `${labels.value.title} — ${labels.value.heading}`,
    description: labels.value.text,
}));
</script>

<template>
    <MainLayout :meta="meta">
        <section class="section">
            <div class="container">
                <div class="error-page">
                    <div class="error-page__code">{{ labels.title }}</div>
                    <h1 class="error-page__heading">{{ labels.heading }}</h1>
                    <p class="error-page__text">{{ labels.text }}</p>
                    <Link :href="`${prefix}/`" class="btn">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        {{ homeLabel }}
                    </Link>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
