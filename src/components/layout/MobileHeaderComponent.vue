<template>
  <div class="hassan_almalki_topbar">
    <div class="topbar_inner">
      <div class="logo">
        <a href="#" @click="navFuntions.activeSection('home')"
          ><img src="img/logo/logo.png" alt=""
        /></a>
      </div>
      <div class="mobile-controls">
        <LanguageSwitcher class="mobile-lang-switch" />
        <div class="trigger">
          <div
            class="hamburger hamburger--slider"
            @click="navFuntions.toggleMobileMenu()"
          >
            <div class="hamburger-box">
              <div class="hamburger-inner"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div
    class="hassan_almalki_mobile_menu"
    :class="navFuntions.mobileToggle ? 'opened' : ''"
  >
    <div class="inner">
      <div class="menu_list">
        <Menu />
      </div>
    </div>
  </div>
</template>

<script>
import { navFuntions } from "@/navFuntions";
import Menu from "./Menu.vue";
import LanguageSwitcher from "./LanguageSwitcher.vue";

export default {
  name: `MobileHeaderComponent`,
  data() {
    return {
      navFuntions,
    };
  },
  components: { Menu, LanguageSwitcher },
};
</script>

