<script setup>
  defineProps({
    href: String,
    as: String,
  });
</script>

<template>
  <div>
    <v-btn
      v-if="as == 'button'"
      text
      type="submit"
      class="tw-block tw-w-full tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-gray-700 tw-text-left hover:tw-bg-gray-100 focus:tw-outline-none focus:tw-bg-gray-100 tw-transition"
    >
      <slot />
    </v-btn>

    <RouteLink
      v-else
      :to="href"
      class="tw-block tw-px-4 tw-py-2 tw-text-sm tw-leading-5 tw-text-gray-700 hover:tw-bg-gray-100 focus:tw-outline-none focus:tw-bg-gray-100 tw-transition"
    >
      <slot />
    </RouteLink>
  </div>
</template>
