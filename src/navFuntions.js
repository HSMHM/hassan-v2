import { reactive } from "vue";
import { hassanSectionActive } from "./utilits";

export const navFuntions = reactive({
  active: "home",
  mobileToggle: false,
  activeSection(value) {
    this.active = value;
    this.mobileToggle = false;
    hassanSectionActive(value);
  },
  activeNavCls(value) {
    return this.active == value ? "active" : "";
  },
  toggleMobileMenu() {
    this.mobileToggle = !this.mobileToggle;
  },
});
