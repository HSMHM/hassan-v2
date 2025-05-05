<template>
  <ul class="transition_link">
    <li :class="navFuntions.activeNavCls('home')">
      <a href="#home" @click="navFuntions.activeSection('home')">Home</a>
    </li>
    <li :class="navFuntions.activeNavCls('about')">
      <a href="#about" @click="navFuntions.activeSection('about')">About</a>
    </li>
    <li :class="navFuntions.activeNavCls('portfolio')">
      <a href="#portfolio" @click="navFuntions.activeSection('portfolio')"
        >Portfolio</a
      >
    </li>
    <li :class="navFuntions.activeNavCls('news')">
      <a href="#news" @click="navFuntions.activeSection('news')">News</a>
    </li>
    <li :class="navFuntions.activeNavCls('contact')">
      <a href="#contact" @click="navFuntions.activeSection('contact')"
        >Contact</a
      >
    </li>
  </ul>
  <LanguageSwitcher />
</template>

<script>
import { navFuntions } from "@/navFuntions";
import LanguageSwitcher from './LanguageSwitcher.vue';

export default {
  name: 'Menu',
  components: {
    LanguageSwitcher
  },
  setup() {
    return {
      navFuntions
    };
  }
};
</script>
<style scoped>
.menu_wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

:deep(.transition_link) {
  display: flex;
  align-items: center;
}

/* RTL support for menu */
html[dir="rtl"] :deep(.transition_link li) {
  margin-right: 0;
  margin-left: 40px;
}

html[dir="rtl"] :deep(.transition_link li:last-child) {
  margin-left: 0;
}
</style>