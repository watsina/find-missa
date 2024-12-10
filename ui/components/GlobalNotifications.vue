<script setup></script>

<template>
  <div>
    <v-snackbar
      v-if="$notify.text"
      v-bind="$notify.props"
      :color="$notify.color"
      :model-value="!!$notify.text"
      @input="!$event && $notify.close()"
    >
      {{ $notify.text }}
      <template #actions>
        <v-btn density="compact" size="small" variant="plain" @click="$notify.close()">
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>
