<template>
  <div class="hassan_almalki_section" id="about">
    <div class="section_inner">
      <div class="hassan_almalki_about">
        <div class="left">
          <div class="image">
            <img src="img/thumbs/1-1.jpg" alt="" />
            <div class="main" data-img-url="img/about/1.jpg"></div>
          </div>
        </div>
        <div class="right">
          <div class="short">
            <h3 class="name">{{ $t('about.firstName') }}<span class="coloring">{{ $t('about.lastName') }}</span></h3>
          </div>
          <div class="text">
            <p>
              {{ $t('about.description') }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AnimationText from "./AnimationText.vue";
export default {
  name: `AboutComponent`,
  data() {
    return {
      open: false,
    };
  },
  components: {
    AnimationText,
  },
};
</script>
