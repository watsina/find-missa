<template>
  <teleport to="body">
    <transition leave-active-class="tw:duration-200">
      <div
        v-show="show"
        class="tw:fixed tw:inset-0 tw:z-50 tw:px-4 tw:py-6 tw:overflow-y-auto tw:sm:px-0"
        scroll-region
      >
        <transition
          enter-active-class="tw:duration-300 tw:ease-out"
          enter-from-class="tw:opacity-0"
          enter-to-class="tw:opacity-100"
          leave-active-class="tw:duration-200 tw:ease-in"
          leave-from-class="tw:opacity-100"
          leave-to-class="tw:opacity-0"
        >
          <div
            v-show="show"
            class="tw:fixed tw:inset-0 tw:transition-all tw:transform"
            @click="close"
          >
            <div class="tw:absolute tw:inset-0 tw:bg-gray-500 tw:opacity-75" />
          </div>
        </transition>

        <transition
          enter-active-class="tw:duration-300 tw:ease-out"
          enter-from-class="tw:translate-y-4 tw:opacity-0 tw:sm:translate-y-0 tw:sm:scale-95"
          enter-to-class="tw:translate-y-0 tw:opacity-100 tw:sm:scale-100"
          leave-active-class="tw:duration-200 tw:ease-in"
          leave-from-class="tw:translate-y-0 tw:opacity-100 tw:sm:scale-100"
          leave-to-class="tw:translate-y-4 tw:opacity-0 tw:sm:translate-y-0 tw:sm:scale-95"
        >
          <div
            v-show="show"
            class="tw:mb-6 tw:overflow-hidden tw:transition-all tw:transform tw:bg-white tw:rounded-lg tw:shadow-xl tw:sm:w-full tw:sm:mx-auto"
            :class="maxWidthClass"
          >
            <slot v-if="show" />
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
  import { computed, onMounted, onUnmounted, watch } from 'vue';

  const props = defineProps({
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

  const emit = defineEmits(['close']);

  watch(
    () => props.show,
    () => {
      if (props.show) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = null;
      }
    },
  );

  const close = () => {
    if (props.closeable) {
      emit('close');
    }
  };

  const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
      close();
    }
  };

  onMounted(() => document.addEventListener('keydown', closeOnEscape));

  onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
  });

  const maxWidthClass = computed(() => {
    return {
      sm: 'sm:max-w-sm',
      md: 'sm:max-w-md',
      lg: 'sm:max-w-lg',
      xl: 'sm:max-w-xl',
      '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
  });
</script>
