<template>
  <span class="cd-headline rotate-1">
    <span class="blc">{{ $t('home.intro') }}</span>
    <span class="cd-words-wrapper">
      <b
        :class="text == i ? 'is-visible' : 'is-hidden'"
        v-for="(skill, i) in skills"
        :key="i"
      >
        {{ skill }}</b>
    </span>
  </span>
</template>

<script>
export default {
  name: `AnimationText`,
  data() {
    return {
      text: 0,
    };
  },
  computed: {
    skills() {
      return [
        this.$t('home.titles.designer'),
        this.$t('home.titles.systems_analyst'),
        this.$t('home.titles.business_analyst'),
        this.$t('home.titles.product_manager')
      ];
    }
  },
  mounted() {
    const interval = setInterval(() => {
      this.text = this.text < this.skills.length - 1 ? this.text + 1 : 0;
    }, 3000);
    return () => clearInterval(interval);
  },
};
</script>
