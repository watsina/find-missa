<!-- eslint-disable vue/no-v-html -->
<!-- eslint-disable vue/no-v-text-v-html-on-component -->
<template>
  <div
    aria-label="Pagination"
    class="tw:flex tw:items-center tw:justify-between tw:px-4 tw:py-3 tw:bg-white tw:border-t tw:border-gray-200 tw:shadow tw:sm:px-6"
  >
    <div class="tw:flex tw:justify-between tw:flex-1 tw:sm:hidden">
      <a
        class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:rounded-md tw:hover:bg-gray-50"
        :href="nextPageUrl || '#'"
        preserve-scroll
      >
        Previous
      </a>
      <a
        class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:ml-3 tw:text-sm tw:font-medium tw:text-gray-700 tw:bg-white tw:border tw:border-gray-300 tw:rounded-md tw:hover:bg-gray-50"
        :href="prevPageUrl || '#'"
        preserve-scroll
      >
        Next
      </a>
    </div>
    <div class="tw:hidden tw:sm:flex tw:sm:flex-1 tw:sm:items-center tw:sm:justify-between">
      <div>
        <p class="tw:text-sm tw:text-gray-700">
          Showing
          {{ ' ' }}
          <span class="tw:font-medium">{{ currentPage }}</span>
          {{ ' ' }}
          to
          {{ ' ' }}
          <span class="tw:font-medium">{{ to }}</span>
          {{ ' ' }}
          of
          {{ ' ' }}
          <span class="tw:font-medium">{{ total }}</span>
          {{ ' ' }}
          results
        </p>
      </div>
      <div>
        <nav
          aria-label="Pagination"
          class="tw:inline-flex tw:-space-x-px tw:rounded-md tw:shadow-sm isolate"
        >
          <!-- Current: "tw:z-10 tw:bg-indigo-50 tw:border-indigo-500 tw:text-indigo-600", Default: "tw:bg-white tw:border-gray-300 tw:text-gray-500 tw:hover:bg-gray-50" -->

          <Component
            :is="link.url ? Link : 'span'"
            v-for="(link, index) in links"
            :key="`${index}`"
            class="tw:relative tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:text-sm tw:font-medium tw:text-gray-500 tw:bg-white tw:border tw:border-gray-300 tw:hover:bg-gray-50 tw:focus:z-20"
            :class="{
              'tw:text-gray-500': !link.url,
              'tw:z-10 tw:bg-indigo-50 tw:border-indigo-500 tw:text-indigo-600': link.active,
            }"
            :href="link.url || '#'"
            preserve-scroll
            v-html="link.label"
          ></Component>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
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
