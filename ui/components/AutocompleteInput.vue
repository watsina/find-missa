<template>
  <Combobox
    as="div"
    :model-value="modelValue"
    @update:model-value="(value) => emit('update:modelValue', value)"
  >
    <ComboboxLabel v-if="label" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">
      {{ label }}
    </ComboboxLabel>
    <div class="tw:relative tw:mt-1 tw:rounded-md tw:shadow-sm">
      <ComboboxInput
        :class="[
          'tw:w-full tw:py-2 tw:pl-3 tw:pr-10 tw:bg-white tw:border tw:border-gray-300 tw:rounded-md tw:shadow-sm tw:focus:border-indigo-500 tw:focus:outline-hidden tw:focus:ring-1 tw:focus:ring-indigo-500 tw:sm:text-sm',
          error &&
            'tw:border-red-300 tw:text-red-900 tw:focus:border-red-500 tw:focus:ring-red-500 tw:focus:ring-opacity-10 tw:placeholder-red-300',
        ]"
        :display-value="(item) => item?.text"
        @change="query = $event.target.value"
      />
      <ComboboxButton
        class="tw:absolute tw:inset-y-0 tw:right-0 tw:flex tw:items-center tw:px-2 tw:rounded-r-md tw:focus:outline-hidden"
      >
        <ChevronUpDownIcon aria-hidden="true" class="tw:w-5 tw:h-5 tw:text-gray-400" />
      </ComboboxButton>

      <transition
        leave-active-class="tw:transition tw:duration-100 tw:ease-in"
        leave-from-class="tw:opacity-100"
        leave-to-class="tw:opacity-0"
      >
        <ComboboxOptions
          v-if="filteredItems.length > 0"
          class="tw:absolute tw:z-10 tw:w-full tw:py-1 tw:mt-1 tw:overflow-auto tw:text-base tw:bg-white tw:rounded-md tw:shadow-lg tw:max-h-60 tw:ring-1 tw:ring-black tw:ring-opacity-5 tw:focus:outline-hidden tw:sm:text-sm"
        >
          <ComboboxOption
            v-for="item in filteredItems"
            :key="item.value"
            v-slot="{ active, selected }"
            as="template"
            :value="item"
          >
            <li
              :class="[
                active ? 'tw:text-white tw:bg-indigo-600' : 'tw:text-gray-900',
                'tw:relative tw:cursor-default tw:select-none tw:py-2 tw:pl-8 tw:pr-4',
              ]"
            >
              <span
                :class="[selected ? 'tw:font-semibold' : 'tw:font-normal', 'tw:block tw:truncate']"
              >
                {{ item.text }}
              </span>

              <span
                v-if="selected"
                :class="[
                  active ? 'tw:text-white' : 'tw:text-indigo-600',
                  'tw:absolute tw:inset-y-0 tw:left-0 tw:flex tw:items-center tw:pl-1.5',
                ]"
              >
                <CheckIcon aria-hidden="true" class="tw:w-5 tw:h-5" />
              </span>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </transition>
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
        {{ error }}
      </p>
    </div>
  </Combobox>
</template>

<script setup>
  import { ref, computed } from 'vue';
  import { ExclamationCircleIcon, CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';
  import {
    Combobox,
    ComboboxButton,
    ComboboxLabel,
    ComboboxOption,
    ComboboxOptions,
    ComboboxInput,
  } from '@headlessui/vue';

  const props = defineProps({
    items: { type: Array, required: true },
    modelValue: { type: [String, Number, Boolean, Object], required: true },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
  });
  const emit = defineEmits(['update:modelValue']);
  const query = ref('');
  const filteredItems = computed(() =>
    query.value === ''
      ? props.items
      : props.items.filter((item) => {
          return item.text.toLowerCase().includes(query.value.toLowerCase());
        }),
  );
</script>

<script>
  export default {
    inheritAttrs: false,
  };
</script>
