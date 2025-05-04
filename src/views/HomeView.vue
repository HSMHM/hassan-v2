<template>
  <MfpPopup />
  <HassnPopup />
  <AboutPopup />
  <PreloaderComponent />
  <div
    class="hassan_tm_all_wrap"
    data-magic-cursor="show"
    data-enter="fadeInUp"
    data-exit=""
  >
    <!-- WAVE DOTS -->
    <div class="waves"></div>
    <!-- WAVE DOTS -->

    <!-- SETTINGS -->
    <!-- <SettingsComponent /> -->
    <!-- HEADER -->
    <HeaderComponent />
    <!-- /HEADER -->

    <!-- CONTENT -->
    <div class="hassan_tm_content">
      <!-- HOME -->
      <HomeComponent />
      <!-- /HOME -->

      <!-- ABOUT -->
      <AboutComponent />
      <!-- /ABOUT -->

      <!-- PORTFOLIO -->
      <PortfolioComponent />
      <!-- /PORTFOLIO -->

      <!-- NEWS -->
      <NewsComponent />
      <!-- /NEWS -->

      <!-- CONTACT -->
      <ContactComponent />
      <!-- /CONTACT -->
    </div>
    <!-- /CONTENT -->

    <!-- MAGIC CURSOR -->
    <CursorComponent />
    <!-- /MAGIC CURSOR -->
  </div>
</template>

<script>
import PreloaderComponent from "@/components/layout/PreloaderComponent.vue";

import AboutPopup from "@/components/popup/AboutPopup.vue";
import HassnPopup from "@/components/popup/HassnPopup.vue";
import MfpPopup from "@/components/popup/MfpPopup.vue";
import AboutComponent from "../components/AboutComponent.vue";
import ContactComponent from "../components/ContactComponent.vue";
import HomeComponent from "../components/HomeComponent.vue";
import CursorComponent from "../components/layout/CursorComponent.vue";
import HeaderComponent from "../components/layout/HeaderComponent.vue";
import SettingsComponent from "../components/layout/SettingsComponent.vue";
import NewsComponent from "../components/NewsComponent.vue";
import PortfolioComponent from "../components/PortfolioComponent.vue";
import { dataImage, imgToSVG } from "../utilits";
import { waves } from "../waves";

export default {
  name: `HomeView`,
  mounted() {
    waves();
    imgToSVG();
    dataImage();
  },
  components: {
    HomeComponent,
    HomeComponent,
    AboutComponent,
    PortfolioComponent,
    NewsComponent,
    ContactComponent,
    HeaderComponent,
    SettingsComponent,
    CursorComponent,
    PortfolioComponent,
    PreloaderComponent,
    MfpPopup,
    HassnPopup,
    AboutPopup,
  },
};
</script>

<!-- about chart -->
