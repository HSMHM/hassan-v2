<template>
  <div class="hassan_almalki_section" id="portfolio">
    <div class="section_inner">
      <div class="hassan_almalki_portfolio swiper-section">
        <div class="hassan_almalki_main_title">
          <h2>{{ $t('portfolio.title_1') }} <span class="coloring">{{ $t('portfolio.title_2') }}</span></h2>
        </div>
        <div class="portfolio_list gallery_zoom">
          <swiper :loop="false" :slidesPerView="1" :spaceBetween="0" :loopAdditionalSlides="1" :autoplay="{
            delay: 6000,
          }" :navigation="{
              nextEl: '.my_next',
              prevEl: '.my_prev',
            }" :pagination="pagination" :breakpoints="{
              700: {
                slidesPerView: 2,
                spaceBetween: 20,
              },
              1200: {
                slidesPerView: 3,
                spaceBetween: 30,
              },
            }" @slideChange="onSlideChange" :modules="modules" class="swiper-container">
            <swiper-slide class="swiper-slide" v-for="(project, i) in portfolioData" :key="i">
              <div class="list_inner">
                <div class="image">
                  <img src="img/thumbs/1-1.jpg" alt="" />
                  <div class="main" :data-img-url="project.img"></div>
                </div>
                <div class="details">
                  <h3>{{ project.title }}</h3>
                  <span>{{ project.category }}</span>
                </div>
                <a class="hassan_almalki_full_link portfolio_popup" href="#" :aria-label="project.title"></a>

                <!-- Portfolio Popup Information -->
                <div class="hassan_almalki_hidden_content">
                  <div class="portfolio_popup_details">
                    <div class="top_image">
                      <img src="img/thumbs/4-2.jpg" :alt="project.title" />
                      <div class="main" :data-img-url="project.img"></div>
                    </div>
                    <div class="portfolio_main_title">
                      <h3>{{ project.title }}</h3>
                      <span>{{ project.category }}</span>
                    </div>
                    <div class="text">
                      <p v-for="(paragraph, p) in project.description" :key="p">
                        {{ paragraph }}
                      </p>
                    </div>
                    <div class="features">
                      <h4>{{ $t('portfolio.features') }}</h4>
                      <ul>
                        <li v-for="(feature, f) in project.features" :key="f">
                          {{ feature }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <!-- /Portfolio Popup Information -->
              </div>
            </swiper-slide>
            <div class="hassan_almalki_swiper_progress fill">
              <div class="my_pagination_in">
                <span class="currentPortfolio">0</span>
                <span class="pagination_progress">
                  <span class="all allPortfolio"><span></span></span>
                </span>
                <span class="totalPortfolio">04</span>
              </div>
              <div class="my_navigation">
                <ul>
                  <li>
                    <a class="my_prev" href="#" aria-label="Previous slide">
                      <i :class="isRTL ? 'fas fa-chevron-right' : 'fas fa-chevron-left'"></i>
                    </a>
                  </li>
                  <li>
                    <a class="my_next" href="#" aria-label="Next slide">
                      <i :class="isRTL ? 'fas fa-chevron-left' : 'fas fa-chevron-right'"></i>
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
import { Navigation, Pagination } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import { swiperSliderCustomSlider } from "../utilits";

export default {
  name: `PortfolioComponent`,
  components: {
    Swiper,
    SwiperSlide,
  },
  computed: {
    isRTL() {
      return document.documentElement.dir === 'rtl' || document.body.classList.contains('rtl');
    }
  },
  methods: {
    onSlideChange(swiper) {
      this.activeSlider = swiper.activeIndex + 1;
    },
  },
  setup() {
    return {
      modules: [Navigation, Pagination],
      pagination: {
        el: ".hassan_almalki_swiper_progress",
        type: "custom", // progressbar
        renderCustom: function (swiper, current, total) {
          swiperSliderCustomSlider(
            current,
            total,
            "currentPortfolio",
            "totalPortfolio",
            "allPortfolio"
          );
        },
      },
    };
  },
  data() {
    return {
      portfolioData: [
        {
          title: this.$t('portfolio.projects.virtual_auction.title'),
          img: "img/portfolio/1.png",
          category: this.$t('portfolio.projects.virtual_auction.category'),
          description: [
            this.$t('portfolio.projects.virtual_auction.description.p1'),
            this.$t('portfolio.projects.virtual_auction.description.p2')
          ],
          features: [
            this.$t('portfolio.projects.virtual_auction.features.f1'),
            this.$t('portfolio.projects.virtual_auction.features.f2'),
            this.$t('portfolio.projects.virtual_auction.features.f3'),
            this.$t('portfolio.projects.virtual_auction.features.f4'),
            this.$t('portfolio.projects.virtual_auction.features.f5'),
            this.$t('portfolio.projects.virtual_auction.features.f6'),
            this.$t('portfolio.projects.virtual_auction.features.f7')
          ]
        },
        {
          title: this.$t('portfolio.projects.ecommerce_platform.title'),
          img: "img/portfolio/2.png",
          category: this.$t('portfolio.projects.ecommerce_platform.category'),
          description: [
            this.$t('portfolio.projects.ecommerce_platform.description.p1'),
            this.$t('portfolio.projects.ecommerce_platform.description.p2')
          ],
          features: [
            this.$t('portfolio.projects.ecommerce_platform.features.f1'),
            this.$t('portfolio.projects.ecommerce_platform.features.f2'),
            this.$t('portfolio.projects.ecommerce_platform.features.f3'),
            this.$t('portfolio.projects.ecommerce_platform.features.f4'),
            this.$t('portfolio.projects.ecommerce_platform.features.f5'),
            this.$t('portfolio.projects.ecommerce_platform.features.f6'),
            this.$t('portfolio.projects.ecommerce_platform.features.f7')
          ]
        },
        {
          title: this.$t('portfolio.projects.beneficiary_services.title'),
          img: "img/portfolio/3.png",
          category: this.$t('portfolio.projects.beneficiary_services.category'),
          description: [
            this.$t('portfolio.projects.beneficiary_services.description.p1'),
            this.$t('portfolio.projects.beneficiary_services.description.p2')
          ],
          features: [
            this.$t('portfolio.projects.beneficiary_services.features.f1'),
            this.$t('portfolio.projects.beneficiary_services.features.f2'),
            this.$t('portfolio.projects.beneficiary_services.features.f3'),
            this.$t('portfolio.projects.beneficiary_services.features.f4'),
            this.$t('portfolio.projects.beneficiary_services.features.f5'),
            this.$t('portfolio.projects.beneficiary_services.features.f6'),
            this.$t('portfolio.projects.beneficiary_services.features.f7')
          ]
        },
        {
          title: this.$t('portfolio.projects.learning_platform.title'),
          img: "img/portfolio/4.png",
          category: this.$t('portfolio.projects.learning_platform.category'),
          description: [
            this.$t('portfolio.projects.learning_platform.description.p1'),
            this.$t('portfolio.projects.learning_platform.description.p2')
          ],
          features: [
            this.$t('portfolio.projects.learning_platform.features.f1'),
            this.$t('portfolio.projects.learning_platform.features.f2'),
            this.$t('portfolio.projects.learning_platform.features.f3'),
            this.$t('portfolio.projects.learning_platform.features.f4'),
            this.$t('portfolio.projects.learning_platform.features.f5'),
            this.$t('portfolio.projects.learning_platform.features.f6'),
            this.$t('portfolio.projects.learning_platform.features.f7')
          ]
        },
        {
          title: this.$t('portfolio.projects.hackathon_platform.title'),
          img: "img/portfolio/5.png",
          category: this.$t('portfolio.projects.hackathon_platform.category'),
          description: [
            this.$t('portfolio.projects.hackathon_platform.description.p1'),
            this.$t('portfolio.projects.hackathon_platform.description.p2')
          ],
          features: [
            this.$t('portfolio.projects.hackathon_platform.features.f1'),
            this.$t('portfolio.projects.hackathon_platform.features.f2'),
            this.$t('portfolio.projects.hackathon_platform.features.f3'),
            this.$t('portfolio.projects.hackathon_platform.features.f4'),
            this.$t('portfolio.projects.hackathon_platform.features.f5'),
            this.$t('portfolio.projects.hackathon_platform.features.f6'),
            this.$t('portfolio.projects.hackathon_platform.features.f7')
          ]
        },
        {
          title: this.$t('portfolio.projects.support_system.title'),
          img: "img/portfolio/6.png",
          category: this.$t('portfolio.projects.support_system.category'),
          description: [
            this.$t('portfolio.projects.support_system.description.p1'),
            this.$t('portfolio.projects.support_system.description.p2')
          ],
          features: [
            this.$t('portfolio.projects.support_system.features.f1'),
            this.$t('portfolio.projects.support_system.features.f2'),
            this.$t('portfolio.projects.support_system.features.f3'),
            this.$t('portfolio.projects.support_system.features.f4'),
            this.$t('portfolio.projects.support_system.features.f5'),
            this.$t('portfolio.projects.support_system.features.f6'),
            this.$t('portfolio.projects.support_system.features.f7')
          ]
        },
        {
          title: this.$t('portfolio.projects.archive_system.title'),
          img: "img/portfolio/7.png",
          category: this.$t('portfolio.projects.archive_system.category'),
          description: [
            this.$t('portfolio.projects.archive_system.description.p1'),
            this.$t('portfolio.projects.archive_system.description.p2')
          ],
          features: [
            this.$t('portfolio.projects.archive_system.features.f1'),
            this.$t('portfolio.projects.archive_system.features.f2'),
            this.$t('portfolio.projects.archive_system.features.f3'),
            this.$t('portfolio.projects.archive_system.features.f4'),
            this.$t('portfolio.projects.archive_system.features.f5'),
            this.$t('portfolio.projects.archive_system.features.f6'),
            this.$t('portfolio.projects.archive_system.features.f7')
          ]
        }

      ]
    };
  },
};
</script>
