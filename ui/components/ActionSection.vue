<template>
  <div class="tw:md:grid tw:md:grid-cols-3 tw:md:gap-6">
    <SectionTitle>
      <template #title>
        <slot name="title" />
      </template>
      <template #description>
        <slot name="description" />
      </template>
    </SectionTitle>

    <div class="tw:mt-5 tw:md:mt-0 tw:md:col-span-2">
      <div class="tw:px-4 tw:py-5 tw:bg-white tw:shadow tw:sm:p-6 tw:sm:rounded-lg">
        <slot name="content" />
      </div>
    </div>
  </div>
</template>

<script setup>
  import SectionTitle from './SectionTitle.vue';
</script>
