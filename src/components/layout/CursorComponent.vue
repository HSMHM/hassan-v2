<template>
  <div class="mouse-cursor cursor-outer"></div>
  <div class="mouse-cursor cursor-inner"></div>
</template>

<script>
import { hassanCursor } from "@/utilits";

export default {
  name: `CursorComponent`,
  mounted() {
    hassanCursor();
  },
};
</script>
