<script setup>
  import { notify } from '@/utils';
  import {
    mdiClipboardMultipleOutline,
    mdiClose,
    mdiEmailOutline,
    mdiFacebook,
    mdiShareOutline,
    mdiTwitter,
    mdiWhatsapp,
  } from '@mdi/js';
  import { computed, ref } from 'vue';

  const props = defineProps({
    title: { type: String, default: 'Share page' },
    url: { type: String, default: null },
    description: { type: String, default: null },
  });

  const pageUrl = computed(() => window.location.href);

  const dialog = ref(false);

  const shareUrl = computed(() => props.url || pageUrl.value);
  const shareText = computed(() => props.description || 'Checkout mass times in the FindMissaApp');
  // url: new URL(window.location.href),
  // text: 'Find Missa',
  // description: `${form.type} at ${form.start_time}`,
  // quote: 'Find mass, confession and adoration in Uganda',
  // hashtags: ['findMissa', 'watsinaTechnologies'],
  // via: 'FindMissa2025'

  function closeShareModal() {
    dialog.value = false;
  }

  // Copy URL to clipboard
  function copyUrl() {
    navigator.clipboard
      .writeText(shareUrl)
      .then(() => {
        notify.success('Link copied to clipboard!');
        closeShareModal();
      })
      .catch(() => {
        notify.error('Failed to copy link.');
      });
  }

  // Share on WhatsApp
  function shareOnWhatsApp() {
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(shareText.value + ' ' + shareUrl.value)}`;
    window.open(whatsappUrl, '_blank');
    closeShareModal();
  }

  // Share on Facebook
  function shareOnFacebook() {
    const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
    window.open(facebookUrl, '_blank');
    closeShareModal();
  }

  // Share on Twitter (X)
  function shareOnTwitter() {
    const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}&url=${encodeURIComponent(shareUrl)}`;
    window.open(twitterUrl, '_blank', 'width=600,height=400');
    closeShareModal();
  }

  // Share via Email
  function shareViaEmail() {
    const subject = 'Check out the FindMissaApp!';
    const body = `${shareText.value}\n${shareUrl.value}`;
    const emailUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = emailUrl;
    closeShareModal();
  }

  const shareOptions = [
    {
      name: 'Copy Link',
      color: 'primary',
      icon: mdiClipboardMultipleOutline,
      function: copyUrl,
    },
    {
      name: 'WhatsApp',
      color: 'success',
      icon: mdiWhatsapp,
      function: shareOnWhatsApp,
    },
    {
      name: 'Facebook',
      color: 'blue',
      icon: mdiFacebook,
      function: shareOnFacebook,
    },
    {
      name: 'Twitter',
      color: 'secondary',
      icon: mdiTwitter,
      function: shareOnTwitter,
    },
    {
      name: 'Email',
      color: 'info',
      icon: mdiEmailOutline,
      function: shareViaEmail,
    },
  ];
</script>

<template>
  <div class="text-center pa-4">
    <v-btn color="primary" variant="tonal" @click="dialog = true">
      <v-icon color="primary" start>{{ mdiShareOutline }}</v-icon>
      Share
    </v-btn>

    <v-dialog v-model="dialog" closable width="auto">
      <v-card rounded="lg">
        <v-card-title class="!tw-capitalize !tw-flex">
          <span>
            {{ title }}
          </span>
          <v-icon class="tw-ms-auto" :icon="mdiClose" size="x-small" @click="closeShareModal" />
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="tw-flex tw-justify-around !tw-pt-0 !tw-px-2">
          <v-slide-group class="!tw-px-0" show-arrows>
            <v-slide-group-item v-for="media in shareOptions" :key="media.name">
              <div class="tw-flex tw-px-4 tw-flex-col tw-items-center">
                <v-btn
                  :color="media.color"
                  :icon="media.icon"
                  rounded="xl"
                  size="small"
                  @click="media.function"
                >
                </v-btn>
                <span class="tw-text-sm"> {{ media.name }}</span>
              </div>
            </v-slide-group-item>
          </v-slide-group>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
