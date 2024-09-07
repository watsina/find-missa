<template>
  <v-dialog v-model="dialog" v-bind="$attrs" :max-width="maxWidth" persistent @close="close">
    <v-card rounded>
      <v-toolbar color="#f3f4f6" density="comfortable" flat tile>
        <v-toolbar-title class="tw-text-lg">
          <slot name="title" />
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn v-if="closeable" icon="$close" size="small" @click="close"></v-btn>
      </v-toolbar>

      <v-card-text>
        <slot name="content" />
      </v-card-text>

      <v-card-actions
        class="tw-flex tw-flex-row tw-justify-end tw-px-6 tw-py-4 tw-text-right tw-bg-gray-100"
      >
        <slot name="footer" />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, watch } from 'vue';

  const props = defineProps({
    show: {
      type: Boolean,
      default: false,
    },
    maxWidth: {
      type: String,
      default: '500',
    },
    closeable: {
      type: Boolean,
      default: true,
    },
  });

  const emit = defineEmits(['close']);

  const dialog = ref(false);

  watch(
    () => props.show,
    (newValue) => (dialog.value = newValue),
  );

  const close = () => {
    // dialog.value = false;
    emit('close');
  };
</script>
