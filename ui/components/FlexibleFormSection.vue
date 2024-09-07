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

    <div class="tw-mt-5">
      <form @submit.prevent="$emit('submitted')">
        <div
          class="tw-px-4 tw-py-5 tw-bg-white tw-shadow sm:tw-p-6"
          :class="hasActions ? 'sm:tw-rounded-tl-md sm:tw-rounded-tr-md' : 'sm:tw-rounded-md'"
        >
          <div :class="{ 'tw-grid tw-grid-cols-12 tw-gap-6': formGrid }">
            <slot name="form" />
          </div>
        </div>

        <div
          v-if="hasActions"
          class="tw-flex tw-items-center tw-justify-end tw-px-4 tw-py-3 tw-text-right tw-shadow tw-bg-gray-50 sm:tw-px-6 sm:tw-rounded-bl-md sm:tw-rounded-br-md"
        >
          <slot name="actions" />
        </div>
      </form>
    </div>
  </div>
</template>
