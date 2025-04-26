<script setup>
  import { ref, watch } from 'vue';

  const emit = defineEmits(['close']);

  const props = defineProps({
    show: {
      type: Boolean,
      default: false,
    },
    maxWidth: {
      type: String,
      default: '400',
    },
    closeable: {
      type: Boolean,
      default: true,
    },
  });

  const dialog = ref(false);

  watch(
    () => props.show,
    (newValue) => (dialog.value = newValue),
  );

  const close = () => {
    emit('close');
  };
</script>

<template>
  <v-dialog v-model="dialog" v-bind="$attrs" :max-width="maxWidth" persistent @close="close">
    <v-card rounded>
      <v-card-text class="tw:px-4 tw:pt-5 tw:pb-4 tw:bg-white tw:sm:p-6 tw:sm:pb-4">
        <div class="tw:sm:flex tw:sm:items-start">
          <v-avatar color="red-lighten-5">
            <svg
              class="tw:w-6 tw:h-6 tw:text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
              />
            </svg>
          </v-avatar>

          <div class="tw:mt-3 tw:text-center tw:sm:mt-0 tw:sm:ml-4 tw:sm:text-left">
            <h3 class="tw:font-semibold tw:text-lg">
              <slot name="title" />
            </h3>

            <div class="tw:mt-2">
              <slot name="content" />
            </div>
          </div>
        </div>
      </v-card-text>

      <v-card-actions
        class="tw:flex tw:flex-row tw:justify-end tw:px-6 tw:py-2 tw:text-right tw:bg-gray-100"
      >
        <slot name="footer" />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
