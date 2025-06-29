<script setup>
  import AppLayout from '@/layouts/AppLayout.vue';
  import { useForm } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';
  import FormSection from '@/components/FormSection.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import { computed } from 'vue';
  import { notify } from '@/utils';
  import { useRouter } from 'vue-router';

  const form = useForm({
    name: '',
    country: '',
  });

  const router = useRouter();
  const authStore = useAuthStore();
  const authUser = computed(() => authStore?.user);
  const canCreateTeam = computed(() => authUser.value?.is_admin);

  const createTeam = () => {
    form.post('/api/v1/teams', {
      onSuccess: async () => {
        notify.success('Team created successfully');
        await router.push({ name: 'dashboard.index' });
        window.location.reload();
      },
    });
  };
</script>

<template>
  <AppLayout title="Create Team">
    <template #head>
      <h2 class="tw:font-semibold tw:text-xl tw:text-gray-800 tw:leading-tight">Create Team</h2>
    </template>

    <v-container>
      <v-card v-if="!canCreateTeam"> Sorry you don't have permission to create a team </v-card>
      <v-row v-else class="tw:max-w-7xl tw:mx-auto tw:py-10 tw:sm:px-6 tw:lg:px-8">
        <FormSection @submitted="createTeam">
          <template #title> Team Details </template>

          <template #description>
            Create a new team to collaborate with others on projects.
          </template>

          <template #form>
            <v-col cols="12">
              <InputLabel value="Team Owner" />

              <div class="tw:flex tw:items-center tw:mt-2">
                <img
                  :alt="authUser?.name"
                  class="tw:object-cover tw:w-12 tw:h-12 tw:rounded-full"
                  :src="authUser?.profile_photo_url"
                />

                <div class="ms-4 tw:leading-tight">
                  <div class="tw:text-gray-900">{{ authUser?.name }}</div>
                  <div class="tw:text-sm tw:text-gray-700">
                    {{ authUser?.email }}
                  </div>
                </div>
              </div>
            </v-col>
            <v-col cols="12">
              <TextInput
                v-model="form.name"
                autofocus
                class="tw:block tw:w-full tw:mt-1"
                :error="form.errors.name"
                label="Team Name"
                type="text"
              />
            </v-col>
            <v-col cols="12">
              <TextInput
                v-model="form.country"
                class="tw:block tw:w-full tw:mt-1"
                :error="form.errors.country"
                label="Team Country"
                type="text"
              />
            </v-col>
          </template>

          <template #actions>
            <PrimaryButton :disabled="form.processing" :loading="form.processing" type="submit">
              Create
            </PrimaryButton>
          </template>
        </FormSection>
      </v-row>
    </v-container>
  </AppLayout>
</template>
