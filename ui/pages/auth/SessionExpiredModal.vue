<template>
  <v-dialog v-model="model" max-width="500" :persistent="false" overlay-opacity="0.7" persistent>
    <v-card title="Session Expired">
      <v-card-text> Your session has expired. Please login again to continue. </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn :to="{ name: 'auth.login' }" outlined color="primary">
          <v-icon :icon="mdiLoginVariant" />
          Go to login
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { mdiLoginVariant } from '@mdi/js';

  const model = defineModel({
    type: Boolean,
    required: true,
  });
</script>
