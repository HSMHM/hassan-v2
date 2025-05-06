import { createI18n } from 'vue-i18n';
import en from './locales/en/translation.json';
import ar from './locales/ar/translation.json';

const messages = {
  en,
  ar
};

const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('direction') === 'rtl' ? 'ar' : 'en',
  fallbackLocale: 'en',
  messages
});

export default i18n;