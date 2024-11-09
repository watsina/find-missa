<script setup>
  import { computed, ref } from 'vue';
  import ActionSection from '@/components/ActionSection.vue';
  import SectionBorder from '@/components/SectionBorder.vue';
  import RemoveTeamMemberConfirmation from './RemoveTeamMemberConfirmationModal.vue';
  import useAuthStore from '@/stores/useAuthStore';
  import { useForm } from '@/plugins';

  const props = defineProps({
    availableRoles: { type: Array, required: true },
  });

  const team = defineModel('team', { type: Object, required: true });

  const authStore = useAuthStore();
  const authUser = computed(() => authStore?.user);
  const canRemoveTeamMembers = computed(() => authStore.hasPermission('team.members.write'));
  const canUpdateTeamMembers = computed(() => authStore.hasPermission('team.members.write'));

  const updateRoleForm = useForm({
    role_id: null,
  });

  const currentlyManagingRole = ref(false);
  const managingRoleFor = ref(null);
  const confirmingLeavingTeam = ref(false);
  const teamMemberBeingRemoved = ref(null);

  const manageRole = (teamMember) => {
    managingRoleFor.value = teamMember;
    updateRoleForm.role = teamMember.membership.role;
    currentlyManagingRole.value = true;
  };

  const confirmLeavingTeam = () => {
    confirmingLeavingTeam.value = true;
  };

  const confirmTeamMemberRemoval = (teamMember) => {
    teamMemberBeingRemoved.value = teamMember;
  };

  const displayableRole = (role) => {
    // return props.availableRoles.find((r) => r.id === role).name;
  };
</script>

<template>
  <div v-if="team.members?.length > 0">
    <SectionBorder />
    <RemoveTeamMemberConfirmation
      v-model:team="team"
      v-model:team-member-being-removed="teamMemberBeingRemoved"
    />

    <!-- Manage Team Members -->
    <ActionSection class="tw-mt-10 sm:tw-mt-0">
      <template #title> Team Members </template>

      <template #description> All of the people that are part of this team. </template>

      <!-- Team Member List -->
      <template #content>
        <div class="tw-space-y-6">
          <div
            v-for="member in team.members"
            :key="member.id"
            class="tw-flex tw-items-center tw-justify-between"
          >
            <div class="tw-flex tw-items-center">
              <img
                :alt="member.name"
                class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover"
                :src="member.profile_photo_url"
              />
              <div class="ms-4">
                {{ member.name }}
              </div>
            </div>

            <div class="tw-flex tw-items-center">
              <!-- Manage Team Member Role -->
              <button
                v-if="canUpdateTeamMembers && availableRoles.length"
                class="ms-2 tw-text-sm tw-text-gray-400 tw-underline"
                @click="manageRole(member)"
              >
                {{ displayableRole(member.membership.role) }}
              </button>

              <div v-else-if="availableRoles.length" class="ms-2 tw-text-sm tw-text-gray-400">
                {{ displayableRole(member.membership.role) }}
              </div>

              <!-- Leave Team -->
              <button
                v-if="authUser?.id === member.id && !authUser?.is_admin"
                class="tw-cursor-pointer ms-6 tw-text-sm tw-text-red-500"
                @click="confirmLeavingTeam"
              >
                Leave
              </button>

              <!-- Remove Team Member -->
              <v-btn
                v-else-if="canRemoveTeamMembers && !member?.is_admin"
                color="error"
                :disabled="teamMemberBeingRemoved"
                :loading="teamMemberBeingRemoved?.email === member?.email"
                size="x-small"
                variant="text"
                @click="confirmTeamMemberRemoval(member)"
              >
                Remove
              </v-btn>
            </div>
          </div>
        </div>
      </template>
    </ActionSection>
  </div>
</template>
