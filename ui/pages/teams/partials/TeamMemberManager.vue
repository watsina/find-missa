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
  import { mdiCheckCircleOutline } from '@mdi/js';

  const props = defineProps({
    availableRoles: { type: Array, required: true },
  });

  const team = defineModel('team', { type: Object, required: true });

  const authStore = useAuthStore();
  // const authUser = computed(() => authStore?.user);
  const canAddTeamMembers = computed(() => authStore.hasPermission('team.members.write'));
  const canRemoveTeamMembers = computed(() => authStore.hasPermission('team.members.write'));
  const canUpdateTeamMembers = computed(() => authStore.hasPermission('team.members.write'));

  const addTeamMemberForm = useForm({
    name: '',
    email: '',
    role_id: null,
  });

  const updateRoleForm = useForm({
    role_id: null,
  });

  const leaveTeamForm = useForm({});
  const removeTeamMemberForm = useForm({});

  const currentlyManagingRole = ref(false);
  const managingRoleFor = ref(null);
  const confirmingLeavingTeam = ref(false);
  const teamMemberBeingRemoved = ref(null);

  const addTeamMember = () => {
    addTeamMemberForm.post(`/api/v1/team/${props.team?.id}/invitations`, {
      onSuccess: ({ data }) => {
        addTeamMemberForm.reset();
        console.info({ data });
        team.value.team_invitations?.push(data?.data || data);
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
    // updateRoleForm.put(route('team-members.update', [props.team, managingRoleFor.value]), {
    // onSuccess: () => (currentlyManagingRole.value = false),
    // });
  };

  const confirmLeavingTeam = () => {
    confirmingLeavingTeam.value = true;
  };

  const leaveTeam = () => {
    // leaveTeamForm.delete(route('team-members.destroy', [props.team, authUser]));
  };

  const confirmTeamMemberRemoval = (teamMember) => {
    teamMemberBeingRemoved.value = teamMember;
  };

  const removeTeamMember = () => {
    // removeTeamMemberForm.delete(
    //   route('team-members.destroy', [props.team, teamMemberBeingRemoved.value]),
    //   {
    //     errorBag: 'removeTeamMember',
    //     preserveScroll: true,
    //     preserveState: true,
    //     onSuccess: () => (teamMemberBeingRemoved.value = null),
    //   },
    // );
  };

  const displayableRole = (role) => {
    return props.availableRoles.find((r) => r.key === role).name;
  };
</script>

<template>
  <div>
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
                v-model="addTeamMemberForm.name"
                class="tw-mt-1 tw-block tw-w-full"
                :error="addTeamMemberForm.errors.name"
                hint="Please provide the name of the person you would like to add to this team."
                label="Name"
                persistent-hint
                type="text"
              />
            </v-col>
            <v-col cols="12">
              <TextInput
                v-model="addTeamMemberForm.email"
                class="tw-mt-1 tw-block tw-w-full"
                :error="addTeamMemberForm.errors.email"
                hint="Please provide the email address of the person you would like to add to this team."
                label="Email"
                persistent-hint
                type="email"
              />
            </v-col>
            <!-- Role -->
            <v-col
              v-if="availableRoles?.length > 0"
              class="tw-col-span-6 lg:tw-col-span-4"
              cols="12"
            >
              <InputLabel for="roles" value="Role" />
              <InputError class="tw-mt-2" :message="addTeamMemberForm.errors.role_id" />

              <v-list lines="two" selectable>
                <v-list-item
                  v-for="(role, i) in availableRoles"
                  :key="role.id"
                  @click="addTeamMemberForm.role_id = role.id"
                >
                  <!-- Role title -->
                  <v-list-item-title
                    class="tw-text-sm !tw-capitalize"
                    :class="{ 'tw-font-semibold': addTeamMemberForm.role_id === role.id }"
                  >
                    {{ role.name?.replace(/-/g, ' ') }}
                  </v-list-item-title>
                  <!-- Role Description -->
                  <v-list-item-subtitle class="tw-text-xs">
                    {{ role.description }}
                  </v-list-item-subtitle>
                  <template #append>
                    <v-icon
                      v-if="addTeamMemberForm.role_id == role.id"
                      color="success"
                      :icon="mdiCheckCircleOutline"
                    ></v-icon>
                  </template>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>
        </template>

        <template #actions>
          <ActionMessage class="me-3" :on="addTeamMemberForm.recentlySuccessful">
            Added.
          </ActionMessage>

          <PrimaryButton
            :disabled="addTeamMemberForm.processing"
            :loading="addTeamMemberForm.processing"
            type="submit"
          >
            Invite
          </PrimaryButton>
        </template>
      </FormSection>
    </div>

    <div v-if="team.team_invitations?.length > 0 && canAddTeamMembers">
      <SectionBorder />

      <!-- Team Member Invitations -->
      <ActionSection class="tw-mt-10 sm:tw-mt-0">
        <template #title> Pending Team Invitations </template>

        <template #description>
          These people have been invited to your team and have been sent an invitation email. They
          may join the team by accepting the email invitation.
        </template>

        <!-- Pending Team Member Invitation List -->
        <template #content>
          <div class="tw-space-y-6">
            <div
              v-for="invitation in team.team_invitations"
              :key="invitation.id"
              class="tw-flex tw-items-center tw-justify-between"
            >
              <div class="tw-text-gray-600">
                {{ invitation.name(invitation.email) }}
              </div>

              <div class="tw-flex tw-items-center">
                <!-- Cancel Team Invitation -->
                <button
                  v-if="canRemoveTeamMembers"
                  class="tw-cursor-pointer ms-6 tw-text-sm tw-text-red-500 focus:tw-outline-none"
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

    <div v-if="team.members?.length > 0">
      <SectionBorder />

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
                  v-if="$authUser?.id === member.id"
                  class="tw-cursor-pointer ms-6 tw-text-sm tw-text-red-500"
                  @click="confirmLeavingTeam"
                >
                  Leave
                </button>

                <!-- Remove Team Member -->
                <button
                  v-else-if="canRemoveTeamMembers"
                  class="tw-cursor-pointer ms-6 tw-text-sm tw-text-red-500"
                  @click="confirmTeamMemberRemoval(member)"
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
          <div
            class="tw-relative tw-z-0 tw-mt-1 tw-border tw-border-gray-200 tw-rounded-lg tw-cursor-pointer"
          >
            <button
              v-for="(role, i) in availableRoles"
              :key="role.key"
              class="tw-relative tw-px-4 tw-py-3 tw-inline-flex tw-w-full tw-rounded-lg focus:tw-z-10 focus:tw-outline-none focus:tw-border-indigo-500 focus:tw-ring-2 focus:tw-ring-indigo-500"
              :class="{
                'tw-border-t tw-border-gray-200 focus:tw-border-none tw-rounded-t-none': i > 0,
                'tw-rounded-b-none': i !== Object.keys(availableRoles).length - 1,
              }"
              type="button"
              @click="updateRoleForm.role = role.key"
            >
              <div
                :class="{
                  'tw-opacity-50': updateRoleForm.role && updateRoleForm.role !== role.key,
                }"
              >
                <!-- Role Name -->
                <div class="tw-flex tw-items-center">
                  <div
                    class="tw-text-sm tw-text-gray-600"
                    :class="{ 'tw-font-semibold': updateRoleForm.role === role.key }"
                  >
                    {{ role.name }}
                  </div>

                  <svg
                    v-if="updateRoleForm.role == role.key"
                    class="ms-2 tw-h-5 tw-w-5 tw-text-green-400"
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
                <div class="tw-mt-2 tw-text-xs tw-text-gray-600">
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
          :class="{ 'tw-opacity-25': updateRoleForm.processing }"
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
          :class="{ 'tw-opacity-25': leaveTeamForm.processing }"
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
          :class="{ 'tw-opacity-25': removeTeamMemberForm.processing }"
          :disabled="removeTeamMemberForm.processing"
          @click="removeTeamMember"
        >
          Remove
        </DangerButton>
      </template>
    </ConfirmationModal>
  </div>
</template>

<style lang="scss" scoped></style>
