<template>
  <div class="language-switcher">
    <button @click="toggleDirection" :title="isRtl ? $t('languageSwitcher.switchToLtr') : $t('languageSwitcher.switchToRtl')">
      {{ isRtl ? $t('languageSwitcher.english') : $t('languageSwitcher.arabic') }}
    </button>
  </div>
</template>

<script>
import { useI18n } from 'vue-i18n';

export default {
  name: 'LanguageSwitcher',
  data() {
    return {
      isRtl: document.documentElement.getAttribute('dir') === 'rtl'
    }
  },
  setup() {
    const i18n = useI18n();
    return { i18n };
  },
  methods: {
    toggleDirection() {
      this.isRtl = !this.isRtl;
      document.documentElement.setAttribute('dir', this.isRtl ? 'rtl' : 'ltr');
      localStorage.setItem('direction', this.isRtl ? 'rtl' : 'ltr');
      
      // Change the locale
      this.$i18n.locale = this.isRtl ? 'ar' : 'en';
      localStorage.setItem('locale', this.isRtl ? 'ar' : 'en');
      
      // Force a full page reload to apply the new direction
      window.location.reload();
    }
  },
  mounted() {
    // Check if there's a saved direction preference
    const savedDirection = localStorage.getItem('direction');
    const savedLocale = localStorage.getItem('locale');
    
    if (savedDirection) {
      this.isRtl = savedDirection === 'rtl';
      document.documentElement.setAttribute('dir', savedDirection);
    }
    
    // Set the locale based on saved preference or direction
    if (savedLocale) {
      this.$i18n.locale = savedLocale;
    } else if (this.isRtl) {
      this.$i18n.locale = 'ar';
    }
  }
}
</script>