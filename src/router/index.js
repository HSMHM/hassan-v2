import { createRouter, createWebHistory } from "vue-router";
import { routes } from "./i18n-routes";
import i18n from "../i18n";

// Create localized routes
const localeRoutes = [];

// Add routes with /en prefix for English
const enRoutes = routes.map(route => {
  return {
    ...route,
    path: '/en' + route.path,
    name: `en-${route.name}`,
    meta: { 
      ...route.meta,
      locale: 'en' 
    }
  };
});

// Add routes without prefix for Arabic (default)
const arRoutes = routes.map(route => {
  return {
    ...route,
    path: route.path,
    name: `ar-${route.name}`,
    meta: { 
      ...route.meta,
      locale: 'ar' 
    }
  };
});

// Combine all routes
localeRoutes.push(...arRoutes, ...enRoutes);

// Root redirect
localeRoutes.push({
  path: '/:pathMatch(.*)*',
  redirect: to => {
    const preferredLanguage = localStorage.getItem('language') || 'ar';
    const targetPath = to.path;
    
    // If trying to access English route without /en prefix
    if (preferredLanguage === 'en' && !targetPath.startsWith('/en')) {
      return { path: `/en${targetPath === '/' ? '' : targetPath}` };
    }
    
    return { path: '/' };
  }
});

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes: localeRoutes,
});

// Navigation guard to sync route locale with i18n locale
router.beforeEach((to, from, next) => {
  // Set the language based on the route
  const locale = to.meta.locale || 'ar';
  i18n.global.locale.value = locale;
  localStorage.setItem('language', locale);
  document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
  document.documentElement.lang = locale;
  
  // Update body classes for RTL support
  if (locale === 'ar') {
    document.body.classList.add('rtl-active');
  } else {
    document.body.classList.remove('rtl-active');
  }
  
  // Update document language and direction
  document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
  document.documentElement.lang = locale;
  document.title = locale === 'ar' ? 'حسن للتصميم' : 'Hassan Design';
  
  // Update meta tags
  let descriptionMeta = document.querySelector('meta[name="description"]');
  if (!descriptionMeta) {
    descriptionMeta = document.createElement('meta');
    descriptionMeta.name = 'description';
    document.head.appendChild(descriptionMeta);
  }
  descriptionMeta.content = locale === 'ar' 
    ? 'موقع حسن للتصميم والتطوير' 
    : 'Hassan Design and Development Portfolio';
  
  next();
});

export default router;
