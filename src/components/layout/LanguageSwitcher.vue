<template>
  <div class="language-switcher">
    <a @click.prevent="changeLanguage('ar')" :class="{ active: currentLocale === 'ar' }">العربية</a>
    <span class="separator">|</span>
    <a @click.prevent="changeLanguage('en')" :class="{ active: currentLocale === 'en' }">English</a>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';

export default {
  name: 'LanguageSwitcher',
  setup() {
    const i18n = useI18n();
    const router = useRouter();
    const route = useRoute();
    
    const currentLocale = computed(() => i18n.locale.value);
    
    const changeLanguage = (locale) => {
      // If already on the selected language, do nothing
      if (locale === currentLocale.value) return;
      
      // Get the current route path without any language prefix
      let path = route.path;
      if (path.startsWith('/en')) {
        path = path.substring(3) || '/';
      }
      
      // Navigate to the new localized route
      if (locale === 'ar') {
        // Arabic is the default, so remove /en prefix
        router.push(path);
      } else {
        // Add /en prefix for English
        router.push(`/en${path === '/' ? '' : path}`);
      }
    };
    
    return {
      currentLocale,
      changeLanguage
    };
  }
};
</script>

<style scoped>
.language-switcher {
  display: flex;
  align-items: center;
  margin-left: 20px;
}

.language-switcher a {
  cursor: pointer;
  margin: 0 5px;
  color: #a2a2a2;
  text-decoration: none;
}

.language-switcher a.active {
  color: var(--main-color);
}

.separator {
  color: #a2a2a2;
}

html[dir="rtl"] .language-switcher {
  margin-left: 0;
  margin-right: 20px;
}
</style>