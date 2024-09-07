<template>
  <Modal :closeable="closeable" :max-width="maxWidth" :show="show" @close="close">
    <div class="tw-px-6 tw-py-4">
      <div class="tw-text-lg">
        <slot name="title" />
      </div>

      <div class="tw-mt-4">
        <slot name="content" />
      </div>
    </div>

    <div class="tw-flex tw-flex-row tw-justify-end tw-px-6 tw-py-4 tw-text-right tw-bg-gray-100">
      <slot name="footer" />
    </div>
  </Modal>
</template>

<script setup>
  import Modal from './Modal.vue';

  const emit = defineEmits(['close']);

  defineProps({
    show: {
      type: Boolean,
      default: false,
    },
    maxWidth: {
      type: String,
      default: '2xl',
    },
    closeable: {
      type: Boolean,
      default: true,
    },
  });

  const close = () => {
    emit('close');
  };
</script>
