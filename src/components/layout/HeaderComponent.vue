<template>
  <MobileHeaderComponent />
  <!-- Header -->
  <div class="hassan_almalki_header">
    <div class="header_inner">
      <div class="logo">
        <a href="#" @click="navFuntions.activeSection('home')"
          ><img src="img/logo/logo.png" alt=""
        /></a>
      </div>
      <div class="menu">
        <Menu />
      </div>
      <LanguageSwitcher class="desktop-lang-switch" />
    </div>
  </div>

</template>

<script>
import { navFuntions } from "@/navFuntions";
import Menu from "./Menu.vue";
import MobileHeaderComponent from "./MobileHeaderComponent.vue";
import LanguageSwitcher from "./LanguageSwitcher.vue";

export default {
  name: `HeaderComponent`,
  components: { MobileHeaderComponent, Menu, LanguageSwitcher },
  data() {
    return {
      navFuntions,
    };
  },
};
</script>