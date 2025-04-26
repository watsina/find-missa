<script setup>
  import AppLink from './AppLink';

  defineProps({
    href: { type: [String, Object], required: true },
    active: Boolean,
    activeClass: {
      type: String,
      default:
        'tw:inline-flex tw:items-center tw:px-1 tw:pt-1 tw:border-b-2 tw:border-indigo-400 tw:text-sm tw:font-medium tw:leading-5 tw:text-gray-900 tw:focus:outline-hidden tw:focus:border-indigo-700 tw:transition',
    },
    inActiveClass: {
      type: String,
      default:
        'tw:inline-flex tw:items-center tw:px-1 tw:pt-1 tw:border-b-2 tw:border-transparent tw:text-sm tw:font-medium tw:leading-5 tw:text-gray-500 tw:hover:text-gray-700 tw:hover:border-gray-300 tw:focus:outline-hidden tw:focus:text-gray-700 tw:focus:border-gray-300 tw:transition',
    },
  });
</script>

<template>
  <app-link :active-class="activeClass" :inactive-class="inActiveClass" :to="href">
    <slot />
  </app-link>
</template>
