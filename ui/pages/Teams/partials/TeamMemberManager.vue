<script setup>
  import { computed, ref } from 'vue';
  import ActionMessage from '@/components/ActionMessage.vue';
  import ActionSection from '@/components/ActionSection.vue';
  import ConfirmationModal from '@/components/ConfirmationModal.vue';
  import DangerButton from '@/components/DangerButton.vue';
  import DialogModal from '@/components/DialogModal.vue';
  import FormSection from '@/components/FormSection.vue';
  import InputError from '@/components/InputError.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SectionBorder from '@/components/SectionBorder.vue';
  import TextInput from '@/components/TextInput.vue';
  import useAuthStore from '@/stores/useAuthStore';
  import { http, useForm } from '@/plugins';
  import { notify } from '@/utils';

  const props = defineProps({
    team: { type: Object, required: true },
    availableRoles: { type: Array, required: true },
    userPermissions: { type: Object, required: true },
  });

  const authUser = computed(() => useAuthStore()?.user);

  const addTeamMemberForm = useForm({
    email: '',
    role: null,
  });

  const updateRoleForm = useForm({
    role: null,
  });

  const leaveTeamForm = useForm({});
  const removeTeamMemberForm = useForm({});

  const currentlyManagingRole = ref(false);
  const managingRoleFor = ref(null);
  const confirmingLeavingTeam = ref(false);
  const teamMemberBeingRemoved = ref(null);

  const addTeamMember = () => {
    addTeamMemberForm.post(`/api/v1/team/${props.team?.id}/members`, {
      onSuccess: () => {
        addTeamMemberForm.reset();
        notify.success('Team member was added successfully.');
      },
      onError: () => notify.error('Something went wrong.'),
    });
  };

  const cancelTeamInvitation = (invitation) => {
    http.delete(`/api/team/${props.team.id}/invitations/${invitation.id}`, {
      onSuccess: () => notify.success('Invitation was deleted successfully.'),
      onError: () => notify.error('Something went wrong.'),
    });
  };

  const manageRole = (teamMember) => {
    managingRoleFor.value = teamMember;
    updateRoleForm.role = teamMember.membership.role;
    currentlyManagingRole.value = true;
  };

  const updateRole = () => {
    updateRoleForm.put(route('team-members.update', [props.team, managingRoleFor.value]), {
      onSuccess: () => (currentlyManagingRole.value = false),
    });
  };

  const confirmLeavingTeam = () => {
    confirmingLeavingTeam.value = true;
  };

  const leaveTeam = () => {
    leaveTeamForm.delete(route('team-members.destroy', [props.team, authUser]));
  };

  const confirmTeamMemberRemoval = (teamMember) => {
    teamMemberBeingRemoved.value = teamMember;
  };

  const removeTeamMember = () => {
    removeTeamMemberForm.delete(
      route('team-members.destroy', [props.team, teamMemberBeingRemoved.value]),
      {
        errorBag: 'removeTeamMember',
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => (teamMemberBeingRemoved.value = null),
      },
    );
  };

  const displayableRole = (role) => {
    return props.availableRoles.find((r) => r.key === role).name;
  };
</script>

<template>
  <div>
    <div v-if="userPermissions.canAddTeamMembers">
      <SectionBorder />

      <!-- Add Team Member -->
      <FormSection @submitted="addTeamMember">
        <template #title> Add Team Member </template>

        <template #description>
          Add a new team member to your team, allowing them to collaborate with you.
        </template>

        <template #form>
          <div class="col-span-6">
            <div class="max-w-xl text-sm text-gray-600">
              Please provide the email address of the person you would like to add to this team.
            </div>
          </div>

          <!-- Member Email -->
          <div class="col-span-6 sm:col-span-4">
            <InputLabel for="email" value="Email" />
            <TextInput
              id="email"
              v-model="addTeamMemberForm.email"
              class="mt-1 block w-full"
              type="email"
            />
            <InputError class="mt-2" :message="addTeamMemberForm.errors.email" />
          </div>

          <!-- Role -->
          <div v-if="availableRoles.length > 0" class="col-span-6 lg:col-span-4">
            <InputLabel for="roles" value="Role" />
            <InputError class="mt-2" :message="addTeamMemberForm.errors.role" />

            <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
              <button
                v-for="(role, i) in availableRoles"
                :key="role.key"
                class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                :class="{
                  'border-t border-gray-200 focus:border-none rounded-t-none': i > 0,
                  'rounded-b-none': i != Object.keys(availableRoles).length - 1,
                }"
                type="button"
                @click="addTeamMemberForm.role = role.key"
              >
                <div
                  :class="{
                    'opacity-50': addTeamMemberForm.role && addTeamMemberForm.role != role.key,
                  }"
                >
                  <!-- Role Name -->
                  <div class="flex items-center">
                    <div
                      class="text-sm text-gray-600"
                      :class="{ 'font-semibold': addTeamMemberForm.role == role.key }"
                    >
                      {{ role.name }}
                    </div>

                    <svg
                      v-if="addTeamMemberForm.role == role.key"
                      class="ms-2 h-5 w-5 text-green-400"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.5"
                      viewBox="0 0 24 24"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </div>

                  <!-- Role Description -->
                  <div class="mt-2 text-xs text-gray-600 text-start">
                    {{ role.description }}
                  </div>
                </div>
              </button>
            </div>
          </div>
        </template>

        <template #actions>
          <ActionMessage class="me-3" :on="addTeamMemberForm.recentlySuccessful">
            Added.
          </ActionMessage>

          <PrimaryButton
            :class="{ 'opacity-25': addTeamMemberForm.processing }"
            :disabled="addTeamMemberForm.processing"
          >
            Add
          </PrimaryButton>
        </template>
      </FormSection>
    </div>

    <div v-if="team.team_invitations.length > 0 && userPermissions.canAddTeamMembers">
      <SectionBorder />

      <!-- Team Member Invitations -->
      <ActionSection class="mt-10 sm:mt-0">
        <template #title> Pending Team Invitations </template>

        <template #description>
          These people have been invited to your team and have been sent an invitation email. They
          may join the team by accepting the email invitation.
        </template>

        <!-- Pending Team Member Invitation List -->
        <template #content>
          <div class="space-y-6">
            <div
              v-for="invitation in team.team_invitations"
              :key="invitation.id"
              class="flex items-center justify-between"
            >
              <div class="text-gray-600">
                {{ invitation.email }}
              </div>

              <div class="flex items-center">
                <!-- Cancel Team Invitation -->
                <button
                  v-if="userPermissions.canRemoveTeamMembers"
                  class="cursor-pointer ms-6 text-sm text-red-500 focus:outline-none"
                  @click="cancelTeamInvitation(invitation)"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </template>
      </ActionSection>
    </div>

    <div v-if="team.users.length > 0">
      <SectionBorder />

      <!-- Manage Team Members -->
      <ActionSection class="mt-10 sm:mt-0">
        <template #title> Team Members </template>

        <template #description> All of the people that are part of this team. </template>

        <!-- Team Member List -->
        <template #content>
          <div class="space-y-6">
            <div
              v-for="user in team.users"
              :key="user.id"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <img
                  :alt="user.name"
                  class="w-8 h-8 rounded-full object-cover"
                  :src="user.profile_photo_url"
                />
                <div class="ms-4">
                  {{ user.name }}
                </div>
              </div>

              <div class="flex items-center">
                <!-- Manage Team Member Role -->
                <button
                  v-if="userPermissions.canUpdateTeamMembers && availableRoles.length"
                  class="ms-2 text-sm text-gray-400 underline"
                  @click="manageRole(user)"
                >
                  {{ displayableRole(user.membership.role) }}
                </button>

                <div v-else-if="availableRoles.length" class="ms-2 text-sm text-gray-400">
                  {{ displayableRole(user.membership.role) }}
                </div>

                <!-- Leave Team -->
                <button
                  v-if="$authUser?.id === user.id"
                  class="cursor-pointer ms-6 text-sm text-red-500"
                  @click="confirmLeavingTeam"
                >
                  Leave
                </button>

                <!-- Remove Team Member -->
                <button
                  v-else-if="userPermissions.canRemoveTeamMembers"
                  class="cursor-pointer ms-6 text-sm text-red-500"
                  @click="confirmTeamMemberRemoval(user)"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </template>
      </ActionSection>
    </div>

    <!-- Role Management Modal -->
    <DialogModal :show="currentlyManagingRole" @close="currentlyManagingRole = false">
      <template #title> Manage Role </template>

      <template #content>
        <div v-if="managingRoleFor">
          <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
            <button
              v-for="(role, i) in availableRoles"
              :key="role.key"
              class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
              :class="{
                'border-t border-gray-200 focus:border-none rounded-t-none': i > 0,
                'rounded-b-none': i !== Object.keys(availableRoles).length - 1,
              }"
              type="button"
              @click="updateRoleForm.role = role.key"
            >
              <div
                :class="{ 'opacity-50': updateRoleForm.role && updateRoleForm.role !== role.key }"
              >
                <!-- Role Name -->
                <div class="flex items-center">
                  <div
                    class="text-sm text-gray-600"
                    :class="{ 'font-semibold': updateRoleForm.role === role.key }"
                  >
                    {{ role.name }}
                  </div>

                  <svg
                    v-if="updateRoleForm.role == role.key"
                    class="ms-2 h-5 w-5 text-green-400"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>

                <!-- Role Description -->
                <div class="mt-2 text-xs text-gray-600">
                  {{ role.description }}
                </div>
              </div>
            </button>
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="currentlyManagingRole = false"> Cancel </SecondaryButton>

        <PrimaryButton
          class="ms-3"
          :class="{ 'opacity-25': updateRoleForm.processing }"
          :disabled="updateRoleForm.processing"
          @click="updateRole"
        >
          Save
        </PrimaryButton>
      </template>
    </DialogModal>

    <!-- Leave Team Confirmation Modal -->
    <ConfirmationModal :show="confirmingLeavingTeam" @close="confirmingLeavingTeam = false">
      <template #title> Leave Team </template>

      <template #content> Are you sure you would like to leave this team? </template>

      <template #footer>
        <SecondaryButton @click="confirmingLeavingTeam = false"> Cancel </SecondaryButton>

        <DangerButton
          class="ms-3"
          :class="{ 'opacity-25': leaveTeamForm.processing }"
          :disabled="leaveTeamForm.processing"
          @click="leaveTeam"
        >
          Leave
        </DangerButton>
      </template>
    </ConfirmationModal>

    <!-- Remove Team Member Confirmation Modal -->
    <ConfirmationModal :show="teamMemberBeingRemoved" @close="teamMemberBeingRemoved = null">
      <template #title> Remove Team Member </template>

      <template #content>
        Are you sure you would like to remove this person from the team?
      </template>

      <template #footer>
        <SecondaryButton @click="teamMemberBeingRemoved = null"> Cancel </SecondaryButton>

        <DangerButton
          class="ms-3"
          :class="{ 'opacity-25': removeTeamMemberForm.processing }"
          :disabled="removeTeamMemberForm.processing"
          @click="removeTeamMember"
        >
          Remove
        </DangerButton>
      </template>
    </ConfirmationModal>
  </div>
</template>
