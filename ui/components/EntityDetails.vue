<script setup>
  defineProps({
    fields: { type: Array, required: true },
    title: { type: String, required: true },
  });
</script>

<template>
  <div>
    <v-card-title class="!tw-flex tw-justify-between lg:tw-flex-row tw-flex-col !tw-p-0 !tw-py-2">
      <div>
        <h5 class="tw-text-font-bold tw-text-lg tw-capitalize">
          {{ title }}
        </h5>
        <slot name="subtitle" />
      </div>
      <div>
        <slot name="action" />
      </div>
    </v-card-title>
    <v-card class="tw-mt-2">
      <v-card-text>
        <v-row dense no-gutters>
          <v-col v-for="field in fields" :key="field.text" class="tw-m-0 tw-p-0" cols="12" md="4">
            <v-list>
              <v-list-item dense>
                <template #prepend>
                  <v-icon :color="field.color || ''">{{ field.icon }}</v-icon>
                </template>
                <v-list-item-title>
                  {{ field.text }}
                </v-list-item-title>
                <v-list-item-subtitle
                  v-if="field?.is_link && field?.value"
                  class="tw-text-indigo-700"
                  :href="field?.value"
                  tag="a"
                  target="_blank"
                >
                  {{ field.value || '....' }}
                </v-list-item-subtitle>
                <v-list-item-subtitle v-else>
                  {{ field.value || '....' }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>
