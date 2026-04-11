<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import LanguageSwitcher from './LanguageSwitcher.vue';

const page = usePage();
const locale = computed(() => page.props.locale);
const settings = computed(() => page.props.settings || {});
const t = (k) => page.props.translations?.[k] ?? {};

const scrolled = ref(false);
const onScroll = () => { scrolled.value = window.scrollY > 20; };
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

// Mobile menu state lives here now (was duplicated in a separate MobileHeader)
const menuOpen = ref(false);
const toggleMenu = () => { menuOpen.value = !menuOpen.value; };
const closeMenu = () => { menuOpen.value = false; };

// Close on Inertia navigation
router.on('navigate', closeMenu);

// Lock body scroll when menu is open
watch(menuOpen, (v) => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = v ? 'hidden' : '';
    }
});

onBeforeUnmount(() => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});

const prefix = computed(() => (locale.value === 'en' ? '/en' : ''));
const links = computed(() => {
    const nav = t('navigation');
    return [
        { href: `${prefix.value}/`, label: nav.home },
        { href: `${prefix.value}/about`, label: nav.about },
        { href: `${prefix.value}/portfolio`, label: nav.portfolio },
        { href: `${prefix.value}/workshops`, label: nav.workshops },
        { href: `${prefix.value}/articles`, label: nav.articles },
        { href: `${prefix.value}/contact`, label: nav.contact },
    ];
});

const isActive = (href) => {
    const current = page.url.replace(/\/$/, '') || '/';
    const target = href.replace(/\/$/, '') || '/';
    return current === target;
};

const logoText = computed(() => settings.value.site_name || (locale.value === 'ar' ? 'حسان المالكي' : 'Hassan Almalki'));
const logoSrc = computed(() => settings.value.site_logo || '/img/logo/logo.png');
const homeHref = computed(() => `${prefix.value}/`);
</script>

<template>
    <header class="header" :class="{ 'header--scrolled': scrolled }">
        <div class="container header__inner">
            <Link :href="homeHref" class="header__logo" @click="closeMenu">
                <img :src="logoSrc" :alt="logoText" />
            </Link>

            <nav class="header__nav" :aria-label="locale === 'ar' ? 'القائمة الرئيسية' : 'Main navigation'">
                <ul class="header__nav-list">
                    <li v-for="link in links" :key="link.href">
                        <Link
                            :href="link.href"
                            prefetch="hover"
                            class="header__nav-link"
                            :class="{ 'header__nav-link--active': isActive(link.href) }"
                        >
                            {{ link.label }}
                        </Link>
                    </li>
                </ul>
                <LanguageSwitcher />
            </nav>

            <button
                class="header__hamburger"
                type="button"
                :aria-label="locale === 'ar' ? (menuOpen ? 'إغلاق القائمة' : 'فتح القائمة') : (menuOpen ? 'Close menu' : 'Open menu')"
                :aria-expanded="menuOpen"
                :class="{ 'header__hamburger--open': menuOpen }"
                @click="toggleMenu"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <div class="mobile-menu" :class="{ 'mobile-menu--open': menuOpen }" :aria-hidden="!menuOpen">
        <ul class="mobile-menu__list">
            <li v-for="link in links" :key="link.href">
                <Link
                    :href="link.href"
                    class="mobile-menu__link"
                    :class="{ 'mobile-menu__link--active': isActive(link.href) }"
                    @click="closeMenu"
                >
                    {{ link.label }}
                </Link>
            </li>
            <li>
                <LanguageSwitcher />
            </li>
        </ul>
    </div>
</template>
