<template>
  <div id="preloader">
    <div class="loader_line"></div>
  </div>
</template>

<script>
import { hassanPreLoader } from "@/utilits";

export default {
  name: `PreloaderComponent`,
  mounted() {
    hassanPreLoader();
  },
};
</script>
