<script setup>
  import ConfirmationModal from '@/components/ConfirmationModal.vue';
  import DangerButton from '@/components/DangerButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';

  const teamMemberBeingRemoved = defineModel('teamMemberBeingRemoved', {
    type: Object,
    default: null,
  });
  const team = defineModel('team', { type: Object, required: true });

  const removeTeamMemberForm = useForm({});

  defineEmits(['removed']);

  const removeTeamMember = () => {
    removeTeamMemberForm.delete(
      `/api/v1/team/${team.value?.id}/members/${teamMemberBeingRemoved.value?.membership?.user_id}`,
      {
        onSuccess: () => {
          team.value.members = team.value.members?.filter(
            (member) => member?.email !== teamMemberBeingRemoved.value?.email,
          );

          teamMemberBeingRemoved.value = null;

          notify.success('Team member was removed successfully.');
        },
        onError: () => notify.error('Something went wrong while removing team member'),
      },
    );
  };
</script>

<template>
  <ConfirmationModal :show="teamMemberBeingRemoved" @close="teamMemberBeingRemoved = null">
    <template #title> Remove Team Member </template>

    <template #content> Are you sure you would like to remove this person from the team? </template>

    <template #footer>
      <SecondaryButton @click="teamMemberBeingRemoved = null"> Cancel </SecondaryButton>

      <DangerButton
        class="ms-3"
        :disabled="removeTeamMemberForm.processing"
        :processing="removeTeamMemberForm.processing"
        @click="removeTeamMember"
      >
        Remove
      </DangerButton>
    </template>
  </ConfirmationModal>
</template>
