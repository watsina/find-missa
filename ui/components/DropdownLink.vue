<script setup>
  import NavLink from './NavLink.vue';

  defineProps({
    href: { type: [String, Object], required: true },
    as: { type: String, default: null },
  });
</script>

<template>
  <div>
    <button
      v-if="as == 'button'"
      class="tw-block tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-gray-700 tw-text-left hover:tw-bg-gray-100 focus:tw-outline-none focus:tw-bg-gray-100 tw-transition"
      type="submit"
    >
      <slot />
    </button>

    <NavLink
      v-else
      :href="href"
      in-active-class="tw-block tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-gray-700 hover:tw-bg-gray-100 focus:tw-outline-none focus:tw-bg-gray-100 tw-transition"
    >
      <slot />
    </NavLink>
  </div>
</template>
