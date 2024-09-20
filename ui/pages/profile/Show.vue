<template>
  <AppLayout title="Profile">
    <template #header>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Profile</h2>
    </template>
    <div class="tw-py-12">
      <div class="tw-mx-auto tw-max-w-7xl sm:tw-px-6 lg:tw-px-8">
        <div class="sm:tw-rounded-lg">
          <!-- start -->
          <div class="tw-pb-10 tw-max-w-7xl sm:tw-px-6 lg:tw-px-8">
            <UpdateProfileInformationForm :user="store?.user" />

            <SectionBorder />

            <UpdatePasswordForm class="tw-mt-10 sm:tw-mt-0" />

            <SectionBorder />

            <TwoFactorAuthenticationForm
              :requires-confirmation="confirmsTwoFactorAuthentication"
              class="tw-mt-10 sm:tw-mt-0"
            />

            <SectionBorder />

            <LogoutOtherBrowserSessionsForm :sessions="sessions" class="tw-mt-10 sm:tw-mt-0" />

            <template v-if="false">
              <SectionBorder />

              <DeleteUserForm class="tw-mt-10 sm:tw-mt-0" />
            </template>
          </div>
          <!-- end -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
  import AppLayout from '@/layouts/AppLayout.vue';
  import DeleteUserForm from './DeleteUserForm.vue';
  import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm.vue';
  import SectionBorder from '@/components/SectionBorder.vue';
  import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm.vue';
  import UpdatePasswordForm from './UpdatePasswordForm.vue';
  import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue';
  import useAuthStore from '@/stores/useAuthStore';

  const store = useAuthStore();

  defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
  });
</script>
