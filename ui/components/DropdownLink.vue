<script setup>
  defineProps({
    href: String,
    as: String,
  });
</script>

<template>
  <div>
    <button
      v-if="as == 'button'"
      type="submit"
      class="tw-block tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-gray-700 tw-text-left hover:tw-bg-gray-100 focus:tw-outline-none focus:tw-bg-gray-100 tw-transition"
    >
      <slot />
    </button>

    <RouterLink
      v-else
      :href="href"
      class="tw-block tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-gray-700 hover:tw-bg-gray-100 focus:tw-outline-none focus:tw-bg-gray-100 tw-transition"
    >
      <slot />
    </RouterLink>
  </div>
</template>
