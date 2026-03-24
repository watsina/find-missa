<script setup>
  import { computed, ref } from 'vue';
  import ActionSection from '@/components/ActionSection.vue';
  import SectionBorder from '@/components/SectionBorder.vue';
  import useAuthStore from '@/stores/useAuthStore';
  import { http } from '@/plugins';
  import { notify } from '@/utils';

  const props = defineProps({
    availableRoles: { type: Array, required: true },
  });

  const team = defineModel('team', { type: Object, required: true });

  const authStore = useAuthStore();

  const canRemoveTeamMembers = computed(() => authStore.hasPermission('team-members.write'));
  const canAddTeamMembers = computed(() => authStore.hasPermission('team-members.write'));

  const removingInvite = ref(false);

  const cancelTeamInvitation = (invitation) => {
    removingInvite.value = true;
    http
      .delete(`/api/v1/team/${props.team.id}/invitations/${invitation.id}`)
      .then(() => {
        notify.success('Invitation was deleted successfully.');
        team.value.invitations = team.value.invitations?.filter(
          (invite) => invite?.id !== invitation?.id,
        );
      })
      .catch(() => notify.error('Something went wrong.'))
      .finally(() => (removingInvite.value = false));
  };
</script>

<template>
  <div v-if="team.invitations?.length > 0 && canAddTeamMembers">
    <SectionBorder />

    <!-- Team Member Invitations -->
    <ActionSection class="tw:mt-10 tw:sm:mt-0">
      <template #title> Pending Team Invitations </template>

      <template #description>
        These people have been invited to your team and have been sent an invitation email. They may
        join the team by accepting the email invitation.
      </template>

      <!-- Pending Team Member Invitation List -->
      <template #content>
        <v-list lines="two">
          <v-list-item v-for="invitation in team.invitations" :key="invitation.id">
            <v-list-item-title class="tw:text-sm tw:capitalize!">
              {{ invitation.name }} [{{ invitation?.role?.name?.replace(/-/g, ' ') }}]
            </v-list-item-title>
            <v-list-item-subtitle class="tw:text-xs tw:lowercase!">
              {{ invitation.email }}
            </v-list-item-subtitle>
            <template #append>
              <!-- Cancel Team Invitation -->
              <v-btn
                v-if="canRemoveTeamMembers"
                class="tw:text-red-500"
                color="error"
                :disabled="removingInvite"
                :loading="removingInvite"
                size="x-small"
                variant="text"
                @click="cancelTeamInvitation(invitation)"
              >
                Cancel
              </v-btn>
            </template>
          </v-list-item>
        </v-list>
      </template>
    </ActionSection>
  </div>
</template>
