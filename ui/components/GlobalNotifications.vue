<script setup></script>

<template>
  <div>
    <v-snackbar
      v-if="$notify.text"
      :model-value="!!$notify.text"
      v-bind="$notify.props"
      :color="$notify.color"
      @input="!$event && $notify.close()"
    >
      {{ $notify.text }}
      <template v-slot:actions>
        <v-btn variant="plain" density="compact" size="small" @click="$notify.close()">
          Close
        </v-btn>
      </template>
    </v-snackbar>

    <v-dialog
      :value="!!$notify.confirmation.message"
      :max-width="300"
      @keydown.esc="$notify.handleConfirm(false)"
      @click:outside="$notify.handleConfirm(false)"
    >
      <v-card tile>
        <v-card-title>{{ $notify.confirmation.options?.title || 'Are you sure?' }}</v-card-title>
        <v-card-text>{{ $notify.confirmation.message }}</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary darken-1" text @click="$notify.handleConfirm(true)">Yes</v-btn>
          <v-btn color="grey" text @click="$notify.handleConfirm(false)">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
