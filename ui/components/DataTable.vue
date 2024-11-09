<script setup>
  import { useDisplay } from 'vuetify';

  defineProps({
    headers: { type: Array, required: true },
    items: { type: Array, required: true },
  });

  const { smAndDown } = useDisplay();
</script>

<template>
  <v-data-table
    v-bind="$attrs"
    :headers="headers"
    :items="items"
    :mobile="smAndDown"
    mobile-breakpoint="md"
  >
    <!-- Expose all slots of the third-party component -->
    <template v-for="(_, name) in $slots" #[name]="slotProps">
      <slot :name="name" v-bind="slotProps || {}"></slot>
    </template>
  </v-data-table>
</template>
