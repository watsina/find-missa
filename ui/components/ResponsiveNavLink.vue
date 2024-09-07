<script setup>
  import { computed } from 'vue';
  import { Link } from '@inertiajs/vue3';

  const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
  });

  const classes = computed(() => {
    return props.active
      ? 'tw-block tw-pl-3 tw-pr-4 tw-py-2 tw-border-l-4 tw-border-indigo-400 tw-text-base tw-font-medium tw-text-indigo-700 tw-bg-indigo-50 focus:tw-outline-none focus:tw-text-indigo-800 focus:tw-bg-indigo-100 focus:tw-border-indigo-700 tw-transition'
      : 'tw-block tw-pl-3 tw-pr-4 tw-py-2 tw-border-l-4 tw-border-transparent tw-text-base tw-font-medium tw-text-gray-600 hover:tw-text-gray-800 hover:tw-bg-gray-50 hover:tw-border-gray-300 focus:tw-outline-none focus:tw-text-gray-800 focus:tw-bg-gray-50 focus:tw-border-gray-300 tw-transition';
  });
</script>

<template>
  <div>
    <button
      v-if="as == 'button'"
      :aria-current="active ? 'page' : undefined"
      class="tw-w-full tw-text-left"
      :class="classes"
    >
      <slot />
    </button>

    <Link v-else :aria-current="active ? 'page' : undefined" :class="classes" :href="href">
      <slot />
    </Link>
  </div>
</template>
