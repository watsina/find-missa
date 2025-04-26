<script setup>
  import { ref } from 'vue';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import { useForm } from '@/plugins';

  defineProps({
    availableRoles: { type: Array, required: true },
  });

  const updateRoleForm = useForm({
    role_id: null,
  });

  const currentlyManagingRole = ref(false);
  const managingRoleFor = ref(null);
  // const confirmingLeavingTeam = ref(false);
  // const teamMemberBeingRemoved = ref(null);

  const updateRole = () => {
    // updateRoleForm.put(route('team-members.update', [props.team, managingRoleFor.value]), {
    // onSuccess: () => (currentlyManagingRole.value = false),
    // });
  };
</script>

<template>
  <DialogModal :show="currentlyManagingRole" @close="currentlyManagingRole = false">
    <template #title> Manage Role </template>

    <template #content>
      <div v-if="managingRoleFor">
        <div
          class="tw:relative tw:z-0 tw:mt-1 tw:border tw:border-gray-200 tw:rounded-lg tw:cursor-pointer"
        >
          <button
            v-for="(role, i) in availableRoles"
            :key="role.key"
            class="tw:relative tw:px-4 tw:py-3 tw:inline-flex tw:w-full tw:rounded-lg tw:focus:z-10 tw:focus:outline-hidden tw:focus:border-indigo-500 tw:focus:ring-2 tw:focus:ring-indigo-500"
            :class="{
              'tw:border-t tw:border-gray-200 tw:focus:border-none tw:rounded-t-none': i > 0,
              'tw:rounded-b-none': i !== Object.keys(availableRoles).length - 1,
            }"
            type="button"
            @click="updateRoleForm.role = role.key"
          >
            <div
              :class="{
                'tw:opacity-50': updateRoleForm.role && updateRoleForm.role !== role.key,
              }"
            >
              <!-- Role Name -->
              <div class="tw:flex tw:items-center">
                <div
                  class="tw:text-sm tw:text-gray-600"
                  :class="{ 'tw:font-semibold': updateRoleForm.role === role.key }"
                >
                  {{ role.name }}
                </div>

                <svg
                  v-if="updateRoleForm.role == role.key"
                  class="ms-2 tw:h-5 tw:w-5 tw:text-green-400"
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
              <div class="tw:mt-2 tw:text-xs tw:text-gray-600">
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
        :class="{ 'tw:opacity-25': updateRoleForm.processing }"
        :disabled="updateRoleForm.processing"
        @click="updateRole"
      >
        Save
      </PrimaryButton>
    </template>
  </DialogModal>
</template>
