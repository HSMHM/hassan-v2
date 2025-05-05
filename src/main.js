import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import i18n from './i18n';

// Import Swiper styles
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";
import "swiper/css/scrollbar";

const app = createApp(App);

app.use(router);
app.use(i18n);

app.mount('#app');
