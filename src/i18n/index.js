import { createI18n } from 'vue-i18n';
import ar from './locales/ar';
import en from './locales/en';

const i18n = createI18n({
  legacy: false,
  locale: 'ar', // Set Arabic as default
  fallbackLocale: 'ar',
  messages: {
    ar,
    en
  },
  rtl: {
    ar: true,
    en: false
  }
});

export default i18n;