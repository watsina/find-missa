<script setup>
  import { computed } from 'vue';

  const props = defineProps({
    href: String,
    active: Boolean,
  });

  const classes = computed(() => {
    return props.active
      ? 'tw-inline-flex tw-items-center tw-px-1 tw-pt-1 tw-border-b-2 tw-border-indigo-400 tw-text-sm tw-font-medium tw-leading-5 tw-text-gray-900 focus:tw-outline-none focus:tw-border-indigo-700 tw-transition'
      : 'tw-inline-flex tw-items-center tw-px-1 tw-pt-1 tw-border-b-2 tw-border-transparent tw-text-sm tw-font-medium tw-leading-5 tw-text-gray-500 hover:tw-text-gray-700 hover:tw-border-gray-300 focus:tw-outline-none focus:tw-text-gray-700 focus:tw-border-gray-300 tw-transition';
  });
</script>

<template>
  <a :href="href" :class="classes">
    <slot />
  </a>
</template>
