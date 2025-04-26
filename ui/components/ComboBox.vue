<template>
  <div>
    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700" for="combo-box">{{
      label
    }}</label>
    <div class="tw:relative tw:mt-1 tw:rounded-md tw:shadow-sm">
      <input
        id="combo-box"
        class="tw:block tw:w-full tw:mt-1 tw:placeholder-gray-300 tw:border-gray-300 tw:rounded-md tw:shadow-sm tw:focus:border-indigo-300 tw:focus:ring tw:focus:ring-indigo-200 tw:focus:ring-opacity-50 tw:sm:text-sm"
        :class="{
          'tw:border-red-300 tw:text-red-900 tw:focus:border-red-500 tw:focus:ring-red-500 tw:focus:ring-opacity-10 tw:placeholder-red-300':
            error,
        }"
        list="combo-box"
        name="combo-box"
        type="text"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <datalist id="combo-box">
        <option v-for="item in items" :key="item.value" :value="item?.value">
          <!-- {{ item?.text }} -->
        </option>
      </datalist>
      <div
        v-if="error"
        class="tw:absolute tw:inset-y-0 tw:right-0 tw:flex tw:items-center tw:pr-3 tw:pointer-events-none"
      >
        <ExclamationCircleIcon aria-hidden="true" class="tw:w-5 tw:h-5 tw:text-red-500" />
      </div>
    </div>
    <div v-show="error || hint">
      <p v-if="hint && !error" class="tw:mt-1 tw:text-sm tw:text-gray-400">
        {{ hint }}
      </p>
      <p v-else-if="error" class="tw:mt-1 tw:text-sm tw:text-red-600">
        <template v-if="Array.isArray(error)">
          <span v-for="(message, index) in error" :key="index"> {{ message }} <br /></span>
        </template>
        <span v-else>{{ error }}</span>
      </p>
    </div>
  </div>
</template>

<script setup>
  //
  defineProps({
    items: { type: Array, required: true },
    modelValue: { type: [String, Number, Boolean, Object], required: true },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
  });

  defineEmits(['update:modelValue']);
</script>
