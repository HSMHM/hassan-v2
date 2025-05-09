<template>
  <div v-if="video">
    <div
      class="mfp-bg mfp-ready"
      @click="video = false"
      style="overflow: hidden; display: block"
    ></div>
    <div class="mfp-wrap mfp-close-btn-in mfp-auto-cursor mfp-ready">
      <div class="mfp-container mfp-s-ready mfp-iframe-holder">
        <div class="mfp-content">
          <div class="mfp-iframe-scaler">
            <iframe
              :src="videoValue"
              title="YouTube video player"
              frameBorder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
            ></iframe>
          </div>
        </div>
        <div class="mfp-preloader">Loading...</div>
      </div>
    </div>
  </div>
  <div v-if="img">
    <div class="mfp-bg mfp-ready" @click="img = null"></div>
    <div class="mfp-wrap mfp-close-btn-in mfp-auto-cursor mfp-ready">
      <div
        class="mfp-container mfp-s-ready mfp-iframe-holder mfp-img-container"
      >
        <div class="mfp-content">
          <div class="mfp-iframe-scaler">
            <img class="mfp-img" :src="img" />
          </div>
        </div>
        <div class="mfp-preloader">Loading...</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: `MfpPopup`,
  data() {
    return {
      videoValue: null,
      video: null,
      img: null,
    };
  },
  mounted() {
    const a = document.querySelectorAll("a");
    a.forEach((a) => {
      let href = a.href;
      if (
        href.includes("www.youtube.com") ||
        href.includes("vimeo.com") ||
        href.includes("soundcloud.com")
      ) {
        a.addEventListener("click", (e) => {
          e.preventDefault();
          this.videoValue = a.href;
          this.video = true;
        });
      } else if (href.includes("img")) {
        console.log();
        a.addEventListener("click", (e) => {
          if (a.getAttribute("download") === null) {
            e.preventDefault();
            this.img = href;
          }
        });
      }
    });
  },
};
</script>
