<template>
  <span class="cd-headline rotate-1">
    <span class="blc">{{ $t('home.creative') }} </span>
    <span class="cd-words-wrapper">
      <b
        :class="text == i ? 'is-visible' : 'is-hidden'"
        v-for="(skill, i) in translatedSkills"
        :key="i"
      >
        {{ skill }}</b>
    </span>
  </span>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useI18n } from 'vue-i18n';

export default {
  name: `AnimationText`,
  setup() {
    const { t, locale } = useI18n();
    const text = ref(0);
    let interval;
    
    const translatedSkills = computed(() => {
      return t('home.skills');
    });
    
    const startInterval = () => {
      interval = setInterval(() => {
        text.value = text.value < translatedSkills.value.length - 1 ? text.value + 1 : 0;
      }, 3000);
    };
    
    onMounted(() => {
      startInterval();
    });
    
    // Reset the animation when language changes
    watch(locale, () => {
      text.value = 0;
      clearInterval(interval);
      startInterval();
    });
    
    onBeforeUnmount(() => {
      clearInterval(interval);
    });
    
    return {
      text,
      translatedSkills,
    };
  },
};
</script>
