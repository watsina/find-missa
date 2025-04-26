<script setup>
  import { computed, useSlots } from 'vue';
  import SectionTitle from './SectionTitle.vue';
  defineProps({
    formGrid: { type: Boolean, default: true },
  });
  defineEmits(['submitted']);

  const hasActions = computed(() => !!useSlots().actions);
</script>

<template>
  <div class="">
    <SectionTitle>
      <template #title>
        <slot name="title" />
      </template>
      <template #description>
        <slot name="description" />
      </template>
    </SectionTitle>

    <div class="tw:mt-5">
      <form @submit.prevent="$emit('submitted')">
        <div
          class="tw:px-4 tw:py-5 tw:bg-white tw:shadow tw:sm:p-6"
          :class="hasActions ? 'tw:sm:rounded-tl-md tw:sm:rounded-tr-md' : 'tw:sm:rounded-md'"
        >
          <div :class="{ 'tw:grid tw:grid-cols-12 tw:gap-6': formGrid }">
            <slot name="form" />
          </div>
        </div>

        <div
          v-if="hasActions"
          class="tw:flex tw:items-center tw:justify-end tw:px-4 tw:py-3 tw:text-right tw:shadow tw:bg-gray-50 tw:sm:px-6 tw:sm:rounded-bl-md tw:sm:rounded-br-md"
        >
          <slot name="actions" />
        </div>
      </form>
    </div>
  </div>
</template>
