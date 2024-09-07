<!-- eslint-disable vue/no-v-html -->
<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <div
    class="tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-bg-white tw-border-t tw-border-gray-200 tw-shadow sm:tw-px-6"
    aria-label="Pagination"
  >
    <div class="tw-flex tw-justify-between tw-flex-1 sm:tw-hidden">
      <Link
        :href="nextPageUrl || '#'"
        preserve-scroll
        class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50"
      >
        Previous
      </Link>
      <Link
        preserve-scroll
        :href="prevPageUrl || '#'"
        class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-ml-3 tw-text-sm tw-font-medium tw-text-gray-700 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50"
      >
        Next
      </Link>
    </div>
    <div class="tw-hidden sm:tw-flex sm:tw-flex-1 sm:tw-items-center sm:tw-justify-between">
      <div>
        <p class="tw-text-sm tw-text-gray-700">
          Showing
          {{ ' ' }}
          <span class="tw-font-medium">{{ currentPage }}</span>
          {{ ' ' }}
          to
          {{ ' ' }}
          <span class="tw-font-medium">{{ to }}</span>
          {{ ' ' }}
          of
          {{ ' ' }}
          <span class="tw-font-medium">{{ total }}</span>
          {{ ' ' }}
          results
        </p>
      </div>
      <div>
        <nav
          class="tw-inline-flex -tw-space-x-px tw-rounded-md tw-shadow-sm isolate"
          aria-label="Pagination"
        >
          <!-- Current: "tw-z-10 tw-bg-indigo-50 tw-border-indigo-500 tw-text-indigo-600", Default: "tw-bg-white tw-border-gray-300 tw-text-gray-500 hover:tw-bg-gray-50" -->

          <Component
            :is="link.url ? Link : 'span'"
            v-for="(link, index) in links"
            :key="`${index}`"
            :href="link.url || '#'"
            preserve-scroll
            class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 hover:tw-bg-gray-50 focus:tw-z-20"
            :class="{
              'tw-text-gray-500': !link.url,
              'tw-z-10 tw-bg-indigo-50 tw-border-indigo-500 tw-text-indigo-600': link.active,
            }"
            v-html="link.label"
          ></Component>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { Link } from '@inertiajs/vue3';

  defineProps({
    links: { type: Array, required: true },
    currentPage: { type: Number, required: true },
    nextPageUrl: { type: String, default: '#' },
    prevPageUrl: { type: String, default: '#' },
    perPage: { type: Number, required: true },
    from: { type: Number, required: true },
    to: { type: Number, required: true },
    total: { type: Number, required: true },
  });
</script>

<style lang="scss" scoped></style>
