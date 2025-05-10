<template>
  <div class="hassan_almalki_section" id="workshops">
    <div class="section_inner">
      <div class="hassan_almalki_workshops swiper-section">
        <div class="hassan_almalki_main_title">
          <h3>{{ $t('workshops.title_1') }} <span class="coloring">{{ $t('workshops.title_2') }}</span></h3>
        </div>
        <div class="workshops_list">
          <swiper :loop="false" :slidesPerView="1" :spaceBetween="0" :loopAdditionalSlides="1"
            :autoplay="{ delay: 6000 }" :navigation="{ nextEl: '.my_next', prevEl: '.my_prev' }"
            :pagination="pagination" :breakpoints="{
              700: { slidesPerView: 2, spaceBetween: 20 },
              1200: { slidesPerView: 3, spaceBetween: 30 }
            }" @slideChange="onSlideChange" :modules="modules" class="swiper-container">
            <swiper-slide class="swiper-slide" v-for="(workshop, i) in workshopsData" :key="i">
              <div class="list_inner">
                <div class="image">
                  <img :src="workshop.thumbnailImg" :alt="workshop.title" />
                  <div class="main" :data-img-url="workshop.img"></div>
                  <a class="hassan_almalki_full_link workshop_popup" href="#" :aria-label="workshop.title"></a>
                </div>
                <div class="details">
                  <h3><a href="#">{{ workshop.title }}</a></h3>
                  <span><a href="#">{{ workshop.platform }}</a></span>
                </div>

                <!-- Workshop Popup Information -->
                <div class="hassan_almalki_hidden_content">
                  <div class="workshop_popup_details">
                    <!-- Workshop Header with Banner Image -->
                    <div class="top_image">
                      <img src="img/thumbs/4-2.jpg" :alt="workshop.title" />
                      <div class="main" :data-img-url="workshop.img"></div>
                      <!-- Overlay with Platform Badge -->
                      <div class="workshop_platform_badge">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>{{ workshop.platform }}</span>
                      </div>
                    </div>

                    <!-- Title Section with Visual Separator -->
                    <div class="workshop_main_title">
                      <h3>{{ workshop.title }}</h3>
                      <div class="title_separator"><span></span></div>
                    </div>

                    <!-- Content Sections with Icons -->
                    <div class="text">
                      <!-- About Section -->
                      <div class="workshop_section">
                        <div class="section_header">
                          <i class="fas fa-info-circle"></i>
                          <h4>{{ $t('workshops.about') }}</h4>
                        </div>
                        <div class="section_content">
                          <p v-for="(text, t) in workshop.description" :key="t" class="description_paragraph">
                            {{ text }}
                          </p>
                        </div>
                      </div>

                      <!-- Index Section with Custom Bullets -->
                      <div class="workshop_section">
                        <div class="section_header">
                          <i class="fas fa-list-ul"></i>
                          <h4>{{ $t('workshops.index') }}</h4>
                        </div>
                        <div class="section_content">
                          <ul class="index_list">
                            <li v-for="(index, idx) in workshop.index" :key="idx" class="index_item">
                              <i class="fas fa-check-circle"></i>
                              <span>{{ index }}</span>
                            </li>
                          </ul>
                        </div>
                      </div>

                      <!-- Video Section -->
                      <div class="workshop_section">
                        <div class="section_header">
                          <i class="fas fa-play-circle"></i>
                          <h4>{{ $t('workshops.watchVideo') }}</h4>
                        </div>
                        <div class="section_content">
                          <div class="youtube-embed">
                            <iframe :src="workshop.youtube" frameborder="0"
                              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                              allowfullscreen :title="`${workshop.title} - ${$t('workshops.videoTitle')}`"
                              loading="lazy"></iframe>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /Workshop Popup Information -->
              </div>
            </swiper-slide>

            <div class="hassan_almalki_swiper_progress fill">
              <div class="my_pagination_in">
                <span class="currentWorkshops">0</span>
                <span class="pagination_progress">
                  <span class="all allWorkshops"><span></span></span>
                </span>
                <span class="totalWorkshops">04</span>
              </div>
              <div class="my_navigation">
                <ul>
                  <li>
                    <a class="my_prev" href="#" aria-label="Previous workshop">
                      <i class="fas fa-chevron-left"></i>
                    </a>
                  </li>
                  <li>
                    <a class="my_next" href="#" aria-label="Next workshop">
                      <i class="fas fa-chevron-right"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </swiper>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Navigation, Pagination } from 'swiper';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { swiperSliderCustomSlider } from '../utilits';
import { useI18n } from 'vue-i18n';

export default {
  name: `WorkshopsComponent`,
  components: {
    Swiper,
    SwiperSlide,
  },
  methods: {
    onSlideChange(swiper) {
      this.activeSlider = swiper.activeIndex + 1;
    },
    getImagePath(baseNumber) {
      const isLTR = this.$i18n.locale === 'en';
      return `img/workshops/${baseNumber}${isLTR ? 'en' : ''}.jpg`;
    }
  },
  setup() {
    const { locale } = useI18n();
    return {
      modules: [Navigation, Pagination],
      pagination: {
        el: '.hassan_almalki_swiper_progress',
        type: 'custom',
        renderCustom: function (swiper, current, total) {
          swiperSliderCustomSlider(
            current,
            total,
            'currentWorkshops',
            'totalWorkshops',
            'allWorkshops'
          );
        },
      },
    };
  },
  computed: {
    isLTR() {
      return this.$i18n.locale === 'en';
    },
    // Use a computed property to generate workshop data with the correct image paths
    workshopsData() {
      const suffix = this.isLTR ? 'en' : '';
      
      return [
        {
          title: this.$t('workshops.items.vue.title'),
          img: `img/workshops/1${suffix}.jpg`,
          thumbnailImg: 'img/thumbs/4-3.jpg',
          platform: this.$t('workshops.items.vue.platform'),
          description: [
            this.$t('workshops.items.vue.description.p1'),
            this.$t('workshops.items.vue.description.p2')
          ],
          index: [
            this.$t('workshops.items.vue.index.i1'),
            this.$t('workshops.items.vue.index.i2'),
            this.$t('workshops.items.vue.index.i3'),
            this.$t('workshops.items.vue.index.i4'),
            this.$t('workshops.items.vue.index.i5'),
            this.$t('workshops.items.vue.index.i6'),
            this.$t('workshops.items.vue.index.i7')
          ],
          youtube: 'https://www.youtube.com/embed/8xjveMX9jiM?si=b5Lv8HPcPWVacBw-'
        },
        {
          title: this.$t('workshops.items.resume.title'),
          img: `img/workshops/2${suffix}.jpg`,
          thumbnailImg: 'img/thumbs/4-3.jpg',
          platform: this.$t('workshops.items.resume.platform'),
          description: [
            this.$t('workshops.items.resume.description.p1'),
            this.$t('workshops.items.resume.description.p2')
          ],
          index: [
            this.$t('workshops.items.resume.index.i1'),
            this.$t('workshops.items.resume.index.i2'),
            this.$t('workshops.items.resume.index.i3'),
            this.$t('workshops.items.resume.index.i4'),
            this.$t('workshops.items.resume.index.i5')
          ],
          youtube: 'https://www.youtube.com/embed/0iBsUJ8ZhMM?si=xv6FET5-UXF4Jtn6'
        },
        {
          title: this.$t('workshops.items.animations.title'),
          img: `img/workshops/3${suffix}.jpg`,
          thumbnailImg: 'img/thumbs/4-3.jpg',
          platform: this.$t('workshops.items.animations.platform'),
          description: [
            this.$t('workshops.items.animations.description.p1'),
            this.$t('workshops.items.animations.description.p2')
          ],
          index: [
            this.$t('workshops.items.animations.index.i1'),
            this.$t('workshops.items.animations.index.i2'),
            this.$t('workshops.items.animations.index.i3'),
            this.$t('workshops.items.animations.index.i4'),
            this.$t('workshops.items.animations.index.i5')
          ],
          youtube: 'https://www.youtube.com/embed/qoSSMs2RRIg?si=dmpti0SfWB0jbX-D'
        },
        {
          title: this.$t('workshops.items.prototypes.title'),
          img: `img/workshops/4${suffix}.jpg`,
          thumbnailImg: 'img/thumbs/4-3.jpg',
          platform: this.$t('workshops.items.prototypes.platform'),
          description: [
            this.$t('workshops.items.prototypes.description.p1'),
            this.$t('workshops.items.prototypes.description.p2')
          ],
          index: [
            this.$t('workshops.items.prototypes.index.i1'),
            this.$t('workshops.items.prototypes.index.i2'),
            this.$t('workshops.items.prototypes.index.i3'),
            this.$t('workshops.items.prototypes.index.i4'),
            this.$t('workshops.items.prototypes.index.i5')
          ],
          youtube: 'https://www.youtube.com/embed/5qWP0FVLKVI?si=s7USTS7HBi23R6bO'
        },
        {
          title: this.$t('workshops.items.digital_presence.title'),
          img: `img/workshops/5${suffix}.jpg`,
          thumbnailImg: 'img/thumbs/4-3.jpg',
          platform: this.$t('workshops.items.digital_presence.platform'),
          description: [
            this.$t('workshops.items.digital_presence.description.p1'),
            this.$t('workshops.items.digital_presence.description.p2')
          ],
          index: [
            this.$t('workshops.items.digital_presence.index.i1'),
            this.$t('workshops.items.digital_presence.index.i2'),
            this.$t('workshops.items.digital_presence.index.i3'),
            this.$t('workshops.items.digital_presence.index.i4'),
            this.$t('workshops.items.digital_presence.index.i5')
          ],
          youtube: 'https://www.youtube.com/embed/gIyIzA_4yEI?si=rFBcpp-kN5Tq1Z_n'
        },
        {
          title: this.$t('workshops.items.digital_self_sufficiency.title'),
          img: `img/workshops/6${suffix}.jpg`,
          thumbnailImg: 'img/thumbs/4-3.jpg',
          platform: this.$t('workshops.items.digital_self_sufficiency.platform'),
          description: [
            this.$t('workshops.items.digital_self_sufficiency.description.p1'),
            this.$t('workshops.items.digital_self_sufficiency.description.p2')
          ],
          index: [
            this.$t('workshops.items.digital_self_sufficiency.index.i1'),
            this.$t('workshops.items.digital_self_sufficiency.index.i2'),
            this.$t('workshops.items.digital_self_sufficiency.index.i3'),
            this.$t('workshops.items.digital_self_sufficiency.index.i4'),
            this.$t('workshops.items.digital_self_sufficiency.index.i5')
          ],
          youtube: 'https://www.youtube.com/embed/DxKJkYPTcVo?si=FlkfPzsGgYuZIZPG'
        }
      ];
    }
  },
  data() {
    return {
      activeSlider: 1
    };
  },
};
</script>

<style scoped>
/* Add any additional styles needed for YouTube embed */
.youtube-embed {
  position: relative;
  padding-bottom: 56.25%;
  /* 16:9 aspect ratio */
  height: 0;
  overflow: hidden;
  max-width: 100%;
  margin-top: 20px;
  margin-bottom: 20px;
}

.youtube-embed iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 8px;
}

/* Workshop popup styling */
.workshop_popup_details {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
}

/* Platform badge styling */
.workshop_platform_badge {
  position: absolute;
  top: 20px;
  right: 20px;
  background-color: rgba(0, 0, 0, 0.7);
  color: var(--main-color);
  padding: 8px 15px;
  border-radius: 30px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Title section styling */
.workshop_main_title {
  padding: 30px 30px 5px 30px;
}

.title_separator {
  width: 100%;
  height: 10px;
  position: relative;
  margin: 15px 0;
}

.title_separator span {
  width: 50px;
  height: 2px;
  background-color: var(--main-color);
  display: block;
}

/* Content section styling */
.text {
  padding: 0 30px 30px 30px;
}

.workshop_section {
  margin-bottom: 30px;
  animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.section_header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 8px;
}

.section_header i {
  color: var(--main-color);
  font-size: 18px;
}

.section_header h4 {
  margin: 0;
  font-size: 18px;
}

/* Description paragraphs */
.description_paragraph {
  margin-bottom: 15px;
  line-height: 1.7;
}

/* Index list styling */
.index_list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.index_item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 10px;
  padding: 8px 10px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.index_item:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

.index_item i {
  color: var(--main-color);
  margin-right: 10px;
  margin-top: 4px;
  flex-shrink: 0;
}

/* YouTube embed styling */
.youtube-embed {
  position: relative;
  padding-bottom: 56.25%;
  /* 16:9 aspect ratio */
  height: 0;
  overflow: hidden;
  max-width: 100%;
  border-radius: 10px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.youtube-embed iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 8px;
}
</style>