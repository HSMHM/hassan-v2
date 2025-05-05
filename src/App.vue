<template>
  <div 
    class="hassan_tm_all_wrap" 
    :data-magic-cursor="magicCursor"
    :class="{ 'rtl-mode': $i18n.locale === 'ar' }"
  >
    <router-view />
  </div>
</template>
<script>
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

export default {
  name: 'App',
  setup() {
    const { locale } = useI18n();
    const magicCursor = ref('show');

    onMounted(() => {
      // Only initialize the magic cursor
      // Language is now handled by the router
      const cursorSetting = localStorage.getItem('hassan-cursor');
      if (cursorSetting) {
        magicCursor.value = cursorSetting;
      }
    });

    return {
      magicCursor
    };
  }
};
</script>
<style>
/* RTL global styles */
.rtl-mode {
  direction: rtl;
  text-align: right;
}

/* Adjust other global RTL styles as needed */
html[dir="rtl"] .hassan_tm_main_title h3 {
  text-align: right;
}

html[dir="rtl"] .hassan_tm_contact .wrapper .left ul li .list_inner {
  text-align: right;
}

/* Icon flipping for RTL */
html[dir="rtl"] .icon-right-open-1:before,
html[dir="rtl"] .icon-left-open-1:before {
  transform: scaleX(-1);
  display: inline-block;
}

/* Adjust swiper navigation for RTL */
html[dir="rtl"] .hassan_tm_swiper_progress .my_navigation {
  left: unset;
  right: 6px;
}

html[dir="rtl"] .hassan_tm_swiper_progress .my_navigation ul li {
  margin: 0 0 0 10px;
}
</style>
