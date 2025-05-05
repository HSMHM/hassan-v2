<template>
  <div class="hassan_tm_section" id="about">
    <div class="section_inner">
      <div class="hassan_tm_about">
        <div class="left">
          <div class="image">
            <img src="img/thumbs/1-1.jpg" alt="" />
            <div class="main" data-img-url="img/about/1.jpg"></div>
          </div>
        </div>
        <div class="right">
          <div class="short">
            <h3 class="name">{{ $t('about.name') }}</h3>
            <h3 class="job">
              <AnimationText />
            </h3>
          </div>
          <div class="text">
            <p v-html="$t('about.intro')"></p>
          </div>
          <div class="hassan_tm_button" id="aboutPopup">
            <a href="#">{{ $t('about.learnMore') }}</a>
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

<style scoped>
.hassan_tm_modalbox {
  top: -90px;
}
</style>
