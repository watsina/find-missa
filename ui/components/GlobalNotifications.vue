<script setup></script>

<template>
  <div>
    <v-alert
      v-if="$notify.text"
      :type="$notify.type"
      v-bind="$notify.props"
      dismissible
      tile
      class="tw-fixed tw-z-[1000] tw-top-0 tw-left-0 tw-right-0"
      @input="!$event && $notify.close()"
    >
      {{ $notify.text }}
    </v-alert>

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
