<script setup>
  import { computed } from 'vue';

  const emit = defineEmits(['update:checked']);

  const props = defineProps({
    checked: {
      type: [Array, Boolean],
      default: false,
    },
    value: {
      type: String,
      default: null,
    },
    label: {
      type: String,
      default: null,
    },
    hint: {
      type: String,
      default: null,
    },
  });

  const proxyChecked = computed({
    get() {
      return props.checked;
    },

    set(val) {
      emit('update:checked', val);
    },
  });
</script>

<template>
  <div class="tw-relative tw-flex tw-items-start">
    <div class="tw-flex tw-h-6 tw-items-center">
      <input
        v-model="proxyChecked"
        class="tw-text-indigo-600 tw-border-gray-300 tw-rounded tw-shadow-sm focus:tw-border-indigo-300 focus:tw-ring focus:tw-ring-indigo-200 focus:tw-ring-opacity-50"
        type="checkbox"
        :value="value"
      />
    </div>
    <div v-if="label" class="tw-ml-3 tw-text-sm tw-leading-6">
      <label class="tw-font-medium tw-text-gray-900" for="label">
        {{ label }}
      </label>
      <p v-if="hint" id="label-hint" class="tw-text-gray-500">{{ hint }}</p>
    </div>
  </div>
</template>
