<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="tw-mb-4 tw-text-sm tw-text-gray-600">
      Before continuing, could you verify your email address by clicking on the link we just emailed
      to you? If you didn't receive the email, we will gladly send you another.
    </div>

    <div v-if="verificationLinkSent" class="tw-mb-4 tw-text-sm tw-font-medium tw-text-green-600">
      A new verification link has been sent to the email address you provided in your profile
      settings.
    </div>

    <form @submit.prevent="submit">
      <div class="tw-flex tw-items-center tw-justify-between tw-mt-4">
        <PrimaryButton :class="{ 'tw-opacity-25': form.processing }" :disabled="form.processing">
          Resend Verification Email
        </PrimaryButton>

        <div>
          <a
            :href="route('profile.show')"
            class="tw-text-sm tw-text-gray-600 tw-underline hover:tw-text-gray-900"
          >
            Edit Profile
          </a>

          <a
            :href="route('logout')"
            method="post"
            as="button"
            class="tw-ml-2 tw-text-sm tw-text-gray-600 tw-underline hover:tw-text-gray-900"
          >
            Log Out
          </a>
        </div>
      </div>
    </form>
  </AuthenticationCard>
</template>

<script setup>
  import { computed } from 'vue';
  import { useForm } from '@/plugins';
  import AuthenticationCard from '@/Components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';

  const props = defineProps({
    status: String,
  });

  const form = useForm();

  const submit = () => {
    form.post(route('verification.send'));
  };

  const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>
