<script setup>
  import { computed } from 'vue';
  import { RouterLink } from 'vue-router';

  defineOptions({
    inheritAttrs: false,
  });

  const props = defineProps({
    ...RouterLink.props,
    inactiveClass: { type: String, default: '' },
    activeClass: { type: String, default: '' },
  });

  const isExternalLink = computed(() => {
    return typeof props.to === 'string' && props.to.startsWith('http');
  });
</script>

<template>
  <a v-if="isExternalLink" v-bind="$attrs" :class="inactiveClass" :href="to" target="_blank">
    <slot />
  </a>

  <router-link v-else v-slot="{ isActive, href, navigate }" v-bind="$props" custom>
    <a
      v-bind="$attrs"
      :class="isActive ? activeClass : inactiveClass"
      :href="href"
      @click="navigate"
    >
      <slot />
    </a>
  </router-link>
</template>
