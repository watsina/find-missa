<template>
  <FormSection @submitted="updateProfileInformation">
    <template #title> Profile Information </template>

    <template #description> Update your account's profile information and email address. </template>

    <template #form>
      <v-container>
        <v-row>
          <!-- Profile Photo -->
          <v-col cols="12" md="12" sm="6">
            <InputLabel for="photo" value="Photo" />
            <!-- Current Profile Photo -->
            <div v-show="!photoPreview" class="tw:mt-2">
              <img
                :alt="user.name"
                class="tw:object-cover tw:w-40 tw:h-40 tw:rounded-full"
                :src="user.profile_photo_url"
              />
            </div>

            <!-- New Profile Photo Preview -->
            <div v-show="photoPreview" class="tw:mt-2">
              <span
                class="tw:block tw:w-40 tw:h-40 tw:bg-center tw:bg-no-repeat tw:bg-cover tw:rounded-full"
                :style="'background-image: url(\'' + photoPreview + '\');'"
              />
            </div>

            <!-- Profile Photo File Input -->
            <div class="tw:flex tw:justify-between">
              <FileInput
                v-model="form.photo"
                accept="image/png,image/jpeg,image/jpg, image/webp"
                autofocus
                class="tw:mt-2 tw:w-1/2 tw:mr-2"
                density="compact"
                :error="form.errors.photo"
                label="Upload Profile Photo (jpg, jpeg, png, webp)"
                placeholder="Choose a file..."
                :prepend-icon="mdiCamera"
                variant="underlined"
              />

              <SecondaryButton
                v-if="user.profile_photo_path"
                class="tw:mt-2"
                type="button"
                @click="deletePhoto"
              >
                Remove Photo
              </SecondaryButton>
            </div>
          </v-col>
          <!--Name -->
          <v-col cols="12" md="12" sm="6">
            <TextInput
              v-model="form.name"
              autocomplete="name"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.name"
              label="Name"
              type="text"
            />
          </v-col>
          <!-- Email -->
          <v-col cols="12" md="12" sm="6">
            <TextInput
              v-model="form.email"
              class="tw:block tw:w-full tw:mt-1"
              disabled
              :error="form.errors.email"
              label="Email"
              type="email"
            />

            <div v-if="user.email_verified_at === null">
              <p class="tw:mt-2 tw:text-sm">
                Your email address is unverified.

                <a
                  as="button"
                  class="tw:text-gray-600 tw:underline tw:hover:text-gray-900"
                  :href="route('verification.send')"
                  method="post"
                  @click.prevent="sendEmailVerification"
                >
                  Click here to re-send the verification email.
                </a>
              </p>

              <div
                v-show="verificationLinkSent"
                class="tw:mt-2 tw:text-sm tw:font-medium tw:text-green-600"
              >
                A new verification link has been sent to your email address.
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </template>

    <template #actions>
      <ActionMessage class="tw:mr-3" :on="form.recentlySuccessful"> Saved. </ActionMessage>

      <PrimaryButton
        :class="{ 'tw:opacity-25': form.processing }"
        :disabled="form.processing"
        type="submit"
      >
        Save
      </PrimaryButton>
    </template>
  </FormSection>
</template>

<script setup>
  import { ref, watch, computed } from 'vue';
  import { http, useForm } from '@/plugins';
  import { notify } from '@/utils';
  import { mdiCamera } from '@mdi/js';
  import ActionMessage from '@/components/ActionMessage.vue';
  import FormSection from '@/components/FormSection.vue';
  import FileInput from '@/components/FileInput.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import useAuthStore from '@/stores/useAuthStore';

  const store = useAuthStore();

  const user = computed(() => store?.user);

  const form = useForm({
    _method: 'PUT',
    name: user.value?.name,
    email: user.value?.email,
    photo: null,
  });

  watch(
    () => form.photo,
    () => updatePhotoPreview(),
  );

  const verificationLinkSent = ref(null);
  const photoPreview = ref(null);

  const updateProfileInformation = () => {
    form.post('/api/v1/user/profile-information', {
      headers: { 'Content-Type': 'multipart/form-data' },
      onSuccess: ({ data }) => {
        clearPhotoFileInput();
        store.updateUser(data);
        notify.success('Profile Information Updated successfully.');
      },
    });
  };

  const sendEmailVerification = () => {
    verificationLinkSent.value = true;
  };

  const updatePhotoPreview = () => {
    const photo = form.photo;

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
      photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
  };

  const deletePhoto = () => {
    http
      .delete('/api/v1/auth/profile-photo')
      .then(({ data }) => {
        photoPreview.value = null;
        clearPhotoFileInput();
        store.updateUser(data?.data);
        notify.success('Profile photo removed successfully');
      })
      .catch(() => notify.error('Something went wrong while trying to delete profile photo.'));
  };

  const clearPhotoFileInput = () => {
    if (form.photo) {
      form.photo = null;
    }
  };
</script>
