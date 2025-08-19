<script setup>
  import { mdiEmail, mdiFacebook, mdiTwitter } from '@mdi/js';

  const icons = [
    {
      icon: mdiFacebook,
      href: 'https://www.facebook.com/profile.php?id=61572950859081&sk=about',
    },
    { icon: mdiTwitter, href: 'https://x.com/FindMissa2025' },
    {
      icon: mdiEmail,
      href: 'mailto:info@findmissa.org',
    },
  ];
</script>

<template>
  <v-footer
    class="tw:flex tw:items-center tw:justify-center tw:gap-2 tw:flex-wrap tw:flex-grow-1 tw:py-3"
    color="indigo-lighten-1"
  >
    <div class="tw:flex tw:gap-3">
      <v-btn
        v-for="icon in icons"
        :key="icon.href"
        density="comfortable"
        :href="icon.href"
        :icon="icon.icon"
        target="_blank"
        variant="text"
      ></v-btn>
    </div>
    <v-divider class="tw:my-1" thickness="2" width="50"></v-divider>

    <div class="tw:flex tw:items-center tw:justify-center tw:gap-2 tw:flex-wrap">
      Built with ❤️ by
      <strong><a href="https://watsina.io" target="_blank">Watsina Technologies</a></strong>
    </div>
  </v-footer>
</template>
