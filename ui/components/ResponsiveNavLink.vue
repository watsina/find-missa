<script setup>
  import { computed } from 'vue';
  import AppLink from './AppLink';

  const props = defineProps({
    href: { type: [String, Object], default: null },
    active: Boolean,
    as: { type: String, default: null },
    activeClass: {
      type: String,
      default:
        'tw:block tw:pl-3 tw:pr-4 tw:py-2 tw:border-l-4 tw:border-indigo-400 tw:text-base tw:font-medium tw:text-indigo-700 tw:bg-indigo-50 tw:focus:outline-hidden tw:focus:text-indigo-800 tw:focus:bg-indigo-100 tw:focus:border-indigo-700 tw:transition',
    },
    inActiveClass: {
      type: String,
      default:
        'tw:block tw:pl-3 tw:pr-4 tw:py-2 tw:border-l-4 tw:border-transparent tw:text-base tw:font-medium tw:text-gray-600 tw:hover:text-gray-800 tw:hover:bg-gray-50 tw:hover:border-gray-300 tw:focus:outline-hidden tw:focus:text-gray-800 tw:focus:bg-gray-50 tw:focus:border-gray-300 tw:transition',
    },
  });

  defineOptions({
    inheritAttrs: false,
  });

  const classes = computed(() => {
    return props.active ? props.activeClass : props.inActiveClass;
  });
</script>

<template>
  <div>
    <button
      v-if="as == 'button'"
      :aria-current="active ? 'page' : undefined"
      class="tw:w-full tw:text-left"
      :class="classes"
      text
    >
      <slot />
    </button>

    <app-link v-else :active-class="activeClass" :inactive-class="inActiveClass" :to="href">
      <slot />
    </app-link>
  </div>
</template>
