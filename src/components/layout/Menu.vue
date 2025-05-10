<template>
  <ul class="transition_link">
    <li :class="navFuntions.activeNavCls('home')">
      <a href="#home" @click="navFuntions.activeSection('home')">{{ $t('navigation.home') }}</a>
    </li>
    <li :class="navFuntions.activeNavCls('about')">
      <a href="#about" @click="navFuntions.activeSection('about')">{{ $t('navigation.about') }}</a>
    </li>
    <li :class="navFuntions.activeNavCls('portfolio')">
      <a href="#portfolio" @click="navFuntions.activeSection('portfolio')"
        >{{ $t('navigation.portfolio') }}</a
      >
    </li>
    <li :class="navFuntions.activeNavCls('workshops')">
      <a href="#workshops" @click="navFuntions.activeSection('workshops')">{{ $t('navigation.workshops') }}</a>
    </li>
    <li :class="navFuntions.activeNavCls('contact')">
      <a href="#contact" @click="navFuntions.activeSection('contact')"
        >{{ $t('navigation.contact') }}</a
      >
    </li>
  </ul>
</template>

<script>
import { navFuntions } from "@/navFuntions";
export default {
  name: `Menu`,
  data() {
    return {
      navFuntions,
    };
  },
};
</script>
