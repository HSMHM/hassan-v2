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
    <li :class="navFuntions.activeNavCls('news')">
      <a href="#news" @click="navFuntions.activeSection('news')">{{ $t('navigation.news') }}</a>
    </li>
    <li :class="navFuntions.activeNavCls('contact')">
      <a href="#contact" @click="navFuntions.activeSection('contact')"
        >{{ $t('navigation.contact') }}</a
      >
    </li>
  </ul>
  <i class="fa-solid fa-house"></i> {{ $t('navigation.home') }}

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
