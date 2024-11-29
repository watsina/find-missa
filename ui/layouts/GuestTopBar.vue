<template>
  <v-app-bar app elevate-on-scroll elevation="1" flat>
    <template #prepend>
      <div class="tw-flex tw-items-center shrink-0 tw-mx-4">
        <!-- TODO: add link for app home page -->
        <ApplicationMark class="tw-block tw-w-auto tw-h-9" />
      </div>
      <v-app-bar-title>Find Missa</v-app-bar-title>
    </template>
    <template v-if="$slots.header" #extension>
      <header class="">
        <div class="tw-px-4 tw-mx-auto md:tw-px-6 lg:tw-px-8">
          <slot name="header" />
        </div>
      </header>
    </template>
    <v-spacer></v-spacer>
  </v-app-bar>
</template>

<script setup>
  import ApplicationMark from '@/components/ApplicationMark';
</script>
