<template>
  <v-app-bar elevation="1" flat scroll-behavior="elevate">
    <template #prepend>
      <NavLink class="tw-border-none" :href="{ name: 'home' }">
        <div class="tw-flex tw-items-center shrink-0 tw-mx-4">
          <ApplicationMark class="tw-block tw-w-auto tw-h-9" />
        </div>
        <v-app-bar-title>Find Missa</v-app-bar-title>
      </NavLink>
    </template>
    <template v-if="$slots.header" #extension>
      <header class="tw-font-semibold tw-text-xl tw-text-gray-800 tw-leading-tight">
        <div class="tw-px-4 tw-mx-auto md:tw-px-6 lg:tw-px-8">
          <slot name="header" />
        </div>
      </header>
    </template>
    <v-spacer></v-spacer>
  </v-app-bar>
</template>

<script setup>
  import NavLink from '@/components/NavLink';
  import ApplicationMark from '@/components/ApplicationMark';
</script>
