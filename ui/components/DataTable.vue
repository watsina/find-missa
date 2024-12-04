<script setup>
  import { ref } from 'vue';
  import { useDisplay } from 'vuetify';
  import TextInput from './TextInput.vue';

  defineProps({
    headers: { type: Array, required: true },
    items: { type: Array, required: true },
    hasSearch: Boolean,
  });

  const { smAndDown } = useDisplay();

  const search = ref('');
</script>

<template>
  <v-data-table
    v-bind="$attrs"
    :headers="headers"
    :hide-default-header="smAndDown"
    :items="items"
    :mobile="smAndDown"
    mobile-breakpoint="md"
    :search="search"
  >
    <template v-if="hasSearch" #top>
      <TextInput
        v-model="search"
        class="tw-block tw-mb-2 tw-mx-2"
        clearable
        label="Search"
        placeholder="Search..."
        type="text"
      />
    </template>
    <!-- Expose all slots of the third-party component -->
    <template v-for="(_, name) in $slots" #[name]="slotProps">
      <slot :name="name" v-bind="slotProps || {}"></slot>
    </template>
  </v-data-table>
</template>
