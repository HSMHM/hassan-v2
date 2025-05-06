import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import i18n from "./i18n";

// Import CSS files for processing with PostCSS
import "../public/css/plugins.css";
import "../public/css/style.css";

// Import Swiper styles
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import "swiper/css/scrollbar";

createApp(App).use(router).use(i18n).mount("#app");
