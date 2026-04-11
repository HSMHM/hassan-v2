<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const locale = computed(() => page.props.locale);
const settings = computed(() => page.props.settings || {});
const t = (k) => page.props.translations?.[k] ?? {};

const year = new Date().getFullYear();
const prefix = computed(() => (locale.value === 'en' ? '/en' : ''));

const quickLinks = computed(() => {
    const nav = t('navigation');
    return [
        { href: `${prefix.value}/`, label: nav.home },
        { href: `${prefix.value}/about`, label: nav.about },
        { href: `${prefix.value}/portfolio`, label: nav.portfolio },
        { href: `${prefix.value}/articles`, label: nav.articles },
        { href: `${prefix.value}/contact`, label: nav.contact },
    ];
});

const social = computed(() => {
    const result = [];
    if (settings.value.twitter_url) result.push({ href: settings.value.twitter_url, label: 'X / Twitter', icon: 'fa-brands fa-x-twitter' });
    if (settings.value.linkedin_url) result.push({ href: settings.value.linkedin_url, label: 'LinkedIn', icon: 'fa-brands fa-linkedin-in' });
    if (settings.value.whatsapp_url) result.push({ href: settings.value.whatsapp_url, label: 'WhatsApp', icon: 'fa-brands fa-whatsapp' });
    if (settings.value.snapchat_url) result.push({ href: settings.value.snapchat_url, label: 'Snapchat', icon: 'fa-brands fa-snapchat' });
    return result;
});

const copyrightText = computed(() => {
    const owner = settings.value.owner_name || (locale.value === 'ar' ? 'حسان المالكي' : 'Hassan Almalki');
    const copyright = settings.value.copyright || (locale.value === 'ar' ? 'جميع الحقوق محفوظة' : 'All rights reserved');
    return `© ${year} ${owner}. ${copyright}.`;
});

const logoSrc = computed(() => settings.value.site_logo || '/img/logo/logo.png');
const siteName = computed(() => settings.value.site_name || (locale.value === 'ar' ? 'حسان المالكي' : 'Hassan Almalki'));
const footerDescription = computed(() => settings.value.footer_description || (locale.value === 'ar' ? 'مطور تطبيقات ويب ومدير منتجات تقنية' : 'Web developer & digital product manager'));
</script>

<template>
    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <div>
                    <Link :href="`${prefix}/`" class="footer__brand" :aria-label="siteName">
                        <img :src="logoSrc" :alt="siteName" />
                    </Link>
                    <div class="footer__freelancer">
                        <img src="/img/logo/freelance_logo_white.svg" :alt="locale === 'ar' ? 'شهادة العمل الحر' : 'Freelancer Certificate'" class="footer__freelancer-logo" />
                        <span class="footer__freelancer-id">
                            {{ locale === 'ar' ? 'رقم الشهادة:' : 'Certificate ID:' }} FL-308513622
                        </span>
                    </div>
                </div>

                <div>
                    <h3 class="footer__title">{{ locale === 'ar' ? 'روابط سريعة' : 'Quick Links' }}</h3>
                    <ul class="footer__list">
                        <li v-for="link in quickLinks" :key="link.href">
                            <Link :href="link.href" class="footer__link">{{ link.label }}</Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="footer__title">{{ locale === 'ar' ? 'تواصل' : 'Contact' }}</h3>
                    <ul class="footer__list">
                        <li v-if="settings.email">
                            <a class="footer__link" :href="`mailto:${settings.email}`">{{ settings.email }}</a>
                        </li>
                        <li v-if="settings.phone">
                            <a class="footer__link" :href="`tel:${settings.phone.replace(/\s/g, '')}`">{{ settings.phone }}</a>
                        </li>
                    </ul>
                </div>

                <div v-if="social.length">
                    <h3 class="footer__title">{{ locale === 'ar' ? 'تابعني' : 'Follow Me' }}</h3>
                    <div class="footer__social">
                        <a
                            v-for="s in social"
                            :key="s.href"
                            :href="s.href"
                            class="footer__social-link"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="s.label"
                        >
                            <i :class="s.icon" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>

            <p class="footer__copyright">{{ copyrightText }}</p>
        </div>
    </footer>
</template>
