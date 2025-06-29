<script setup>
  import { computed } from 'vue';
  import FormSection from '@/components/FormSection.vue';
  import ActionMessage from '@/components/ActionMessage.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import SectionBorder from '@/components/SectionBorder.vue';
  import { useForm } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';
  import { notify } from '@/utils';

  const props = defineProps({
    availableRoles: { type: Array, required: true },
  });

  const team = defineModel('team', { type: Object, required: true });

  const authStore = useAuthStore();

  const canAddTeamMembers = computed(() => authStore.hasPermission('team.members.write'));

  const form = useForm({
    name: '',
    email: '',
    role_id: '',
  });

  const itemProps = (item) => {
    return {
      title: item.name?.replace(/-/g, ' ')?.replace(/\b\w/g, (l) => l.toUpperCase()),
      subtitle: item.description,
      value: item.id,
    };
  };

  const addTeamMember = () => {
    form.post(`/api/v1/team/${props.team?.id}/invitations`, {
      onSuccess: ({ data }) => {
        form.reset();
        team.value.invitations?.push(data?.data || data);
        notify.success('Team member was added successfully.');
      },
      onError: () => notify.error('Something went wrong.'),
    });
  };
</script>

<template>
  <div v-if="canAddTeamMembers">
    <SectionBorder />

    <!-- Add Team Member -->
    <FormSection @submitted="addTeamMember">
      <template #title> Add Team Member </template>

      <template #description>
        Add a new team member to your team, allowing them to collaborate with you.
      </template>

      <template #form>
        <v-row>
          <v-col cols="12">
            <TextInput
              v-model="form.name"
              class="tw:mt-1 tw:block tw:w-full"
              :error="form.errors.name"
              hint="Please provide the name of the person you would like to add to this team."
              label="Name"
              persistent-hint
              required
              type="text"
            />
          </v-col>
          <v-col cols="12">
            <TextInput
              v-model="form.email"
              class="tw:mt-1 tw:block tw:w-full"
              :error="form.errors.email"
              hint="Please provide the email address of the person you would like to add to this team."
              label="Email"
              persistent-hint
              required
              type="email"
            />
          </v-col>
          <!-- Role -->
          <v-col v-if="availableRoles?.length > 0" class="tw:col-span-6 tw:lg:col-span-4" cols="12">
            <SelectInput
              v-model="form.role_id"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.role_id"
              hint="Please provide the role you would like to assign to the team member."
              :item-props="itemProps"
              item-subtitle="description"
              :items="availableRoles"
              label="Role*"
              persistent-hint
              required
            />
          </v-col>
        </v-row>
      </template>

      <template #actions>
        <ActionMessage class="me-3" :on="form.recentlySuccessful"> Added. </ActionMessage>

        <PrimaryButton :disabled="form.processing" :loading="form.processing" type="submit">
          Invite
        </PrimaryButton>
      </template>
    </FormSection>
  </div>
</template>
