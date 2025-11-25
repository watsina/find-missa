<script setup>
  import { notify } from '@/utils';
  import {
    mdiClose,
    mdiContentCopy,
    mdiEmailOutline,
    mdiFacebook,
    mdiShareOutline,
    mdiTwitter,
    mdiWhatsapp,
  } from '@mdi/js';
  import { computed, ref } from 'vue';

  const props = defineProps({
    title: { type: String, default: 'Share' },
    url: { type: String, default: null },
    description: { type: String, default: null },
  });

  const pageUrl = computed(() => window.location.href);
  const dialog = ref(false);

  const shareUrl = computed(() => props.url || pageUrl.value);
  const shareText = computed(() => props.description || 'Checkout mass times in the FindMissaApp');

  // Social media sharing metadata
  const shareQuote = 'Find mass, confession and adoration in Uganda';
  const shareHashtags = ['findMissa', 'watsinaTechnologies'];
  const shareVia = 'FindMissa2025';

  // Check if Web Share API is available (mainly mobile devices)
  const canUseNativeShare = computed(() => {
    return typeof navigator !== 'undefined' && navigator.share !== undefined;
  });

  function closeShareModal() {
    dialog.value = false;
  }

  // Main share handler - uses native share on mobile, modal on desktop
  async function handleShare() {
    // Try native share first if available (mobile devices)
    if (canUseNativeShare.value) {
      try {
        await navigator.share({
          title: props.title,
          text: shareText.value,
          url: shareUrl.value,
        });
      } catch (err) {
        // User cancelled the share (AbortError) - don't show error or modal
        if (err.name === 'AbortError') {
          return;
        }
        // If native share fails for other reasons, fall back to modal
        console.error('Native share failed:', err);
        dialog.value = true;
      }
    } else {
      // Desktop or unsupported browsers - show modal
      dialog.value = true;
    }
  }

  // Copy URL to clipboard with description (YouTube-style)
  function copyLink() {
    const copyText = `${shareText.value}\n\n${shareUrl.value}`;
    navigator.clipboard
      .writeText(copyText)
      .then(() => {
        notify.success('Link copied to clipboard!');
      })
      .catch(() => {
        notify.error('Failed to copy link.');
      });
  }

  // Share on WhatsApp
  function shareOnWhatsApp() {
    const message = `${shareText.value}\n\n${shareQuote}\n\n${shareUrl.value}\n\n#${shareHashtags.join(' #')}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
  }

  // Share on Facebook
  function shareOnFacebook() {
    const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl.value)}&quote=${encodeURIComponent(shareQuote)}`;
    window.open(facebookUrl, '_blank');
  }

  // Share on Twitter (X)
  function shareOnTwitter() {
    const hashtags = shareHashtags.join(',');
    const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}&url=${encodeURIComponent(shareUrl.value)}&hashtags=${encodeURIComponent(hashtags)}&via=${encodeURIComponent(shareVia)}`;
    window.open(twitterUrl, '_blank', 'width=600,height=400');
  }

  // Share via Email
  function shareViaEmail() {
    const subject = 'Check out the FindMissaApp!';
    const body = `${shareText.value}\n\n${shareQuote}\n\nVisit: ${shareUrl.value}\n\n#${shareHashtags.join(' #')}`;
    const emailUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = emailUrl;
  }

  const shareOptions = [
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
      color: 'black',
      icon: mdiTwitter,
      function: shareOnTwitter,
    },
    {
      name: 'Email',
      color: 'red',
      icon: mdiEmailOutline,
      function: shareViaEmail,
    },
  ];
</script>

<template>
  <div class="text-center pa-4">
    <v-btn color="primary" variant="tonal" @click="handleShare">
      <v-icon color="primary" start>{{ mdiShareOutline }}</v-icon>
      Share
    </v-btn>

    <!-- YouTube-style desktop share modal -->
    <v-dialog v-model="dialog" closable max-width="500">
      <v-card rounded="lg">
        <v-card-title class="tw:flex! tw:items-center tw:justify-between tw:py-4">
          <span class="tw:text-lg tw:capitalize tw:font-medium">{{ title }}</span>
          <v-btn :icon="mdiClose" size="small" variant="text" @click="closeShareModal"></v-btn>
        </v-card-title>

        <v-divider></v-divider>

        <!-- Link section with input and copy button (YouTube-style) -->
        <v-card-text class="tw:py-4">
          <div class="tw:mb-6">
            <div
              class="tw:flex tw:items-center tw:gap-2 tw:bg-gray-100 tw:rounded-lg tw:px-4 tw:py-3"
            >
              <input
                class="tw:flex-1 tw:bg-transparent tw:outline-none tw:text-sm tw:text-gray-700"
                readonly
                :value="shareUrl"
                @click="$event.target.select()"
              />
              <v-btn color="primary" size="small" variant="flat" @click="copyLink">
                <v-icon size="small" start>{{ mdiContentCopy }}</v-icon>
                Copy
              </v-btn>
            </div>
          </div>

          <!-- Social media sharing options -->
          <div class="tw:mb-2">
            <p class="tw:text-sm tw:text-gray-600 tw:mb-3">Share via</p>
            <div class="tw:flex tw:gap-3 tw:justify-start">
              <v-tooltip
                v-for="media in shareOptions"
                :key="media.name"
                location="bottom"
                :text="media.name"
              >
                <template v-slot:activator="{ props: tooltipProps }">
                  <v-btn
                    v-bind="tooltipProps"
                    :color="media.color"
                    :icon="media.icon"
                    rounded="full"
                    size="large"
                    @click="media.function"
                  >
                  </v-btn>
                </template>
              </v-tooltip>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
