<script setup>
  import { computed, onMounted, ref } from 'vue';
  import AppLayout from '@/layouts/AppLayout.vue';
  import { http } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';
  import RoleExpansionPanel from './RoleExpansionPanel.vue';
  import RoleModal from './RoleModal.vue';
  import { notify } from '@/utils';
  import { mdiPlus, mdiShieldAccount } from '@mdi/js';

  const roles = ref([]);
  const permissionCategories = ref([]);
  const isFetchingRoles = ref(false);
  const isFetchingPermissions = ref(false);
  const showRoleModal = ref(false);
  const selectedRole = ref(null);

  const authStore = useAuthStore();
  const authUser = computed(() => authStore?.user);
  const currentTeam = computed(() => authStore?.currentTeam);

  const canManageRoles = computed(
    () =>
      authUser.value?.is_admin ||
      authUser.value?.owns_current_team ||
      authUser.value?.permissions?.includes('roles.write'),
  );

  const canDeleteRoles = computed(
    () => authUser.value?.is_admin || authUser.value?.owns_current_team,
  );

  onMounted(() => {
    fetchRoles();
    fetchPermissionCategories();
  });

  const fetchRoles = () => {
    isFetchingRoles.value = true;
    http
      .get('/api/v1/roles')
      .then(({ data }) => {
        roles.value = data?.data || [];
      })
      .catch(() => notify.error('Something went wrong while fetching roles.'))
      .finally(() => (isFetchingRoles.value = false));
  };

  const fetchPermissionCategories = () => {
    isFetchingPermissions.value = true;
    http
      .get('/api/v1/permissions/categories')
      .then(({ data }) => {
        permissionCategories.value = data?.data || [];
      })
      .catch(() => notify.error('Something went wrong while fetching permissions.'))
      .finally(() => (isFetchingPermissions.value = false));
  };

  const openCreateModal = () => {
    selectedRole.value = null;
    showRoleModal.value = true;
  };

  const openEditModal = (role) => {
    selectedRole.value = role;
    showRoleModal.value = true;
  };

  const onRoleCreated = (role) => {
    roles.value.push(role);
  };

  const onRoleUpdated = (updatedRole) => {
    const index = roles.value.findIndex((r) => r.id === updatedRole.id);
    if (index !== -1) {
      roles.value[index] = updatedRole;
    }
  };

  const onRoleDeleted = (roleId) => {
    roles.value = roles.value.filter((r) => r.id !== roleId);
  };
</script>

<template>
  <AppLayout title="Roles & Permissions">
    <template #head>
      <div class="tw:flex tw:items-center tw:justify-between">
        <h2>Roles & Permissions</h2>
        <v-btn
          v-if="canManageRoles"
          color="primary"
          :prepend-icon="mdiPlus"
          size="small"
          @click="openCreateModal"
        >
          Create Role
        </v-btn>
      </div>
    </template>

    <v-container>
      <v-skeleton-loader
        v-if="isFetchingRoles || isFetchingPermissions"
        type="list-item-three-line@3"
      ></v-skeleton-loader>

      <template v-else-if="!canManageRoles">
        <v-card class="tw:text-center tw:py-8">
          <v-card-text>
            <v-icon class="tw:mb-4" color="grey" :icon="mdiShieldAccount" size="64" />
            <p class="tw:text-lg">You don't have permission to manage roles.</p>
          </v-card-text>
        </v-card>
      </template>

      <template v-else-if="roles.length === 0">
        <v-card class="tw:text-center tw:py-12">
          <v-card-text>
            <v-icon class="tw:mb-4" color="grey-lighten-1" :icon="mdiShieldAccount" size="80" />
            <h3 class="tw:text-xl tw:font-medium tw:text-gray-600 tw:mb-2">No Roles Yet</h3>
            <p class="tw:text-gray-500 tw:mb-6">
              Create roles to manage permissions for
              {{ currentTeam?.name || 'your team' }}.
            </p>
            <v-btn color="primary" :prepend-icon="mdiPlus" @click="openCreateModal">
              Create Your First Role
            </v-btn>
          </v-card-text>
        </v-card>
      </template>

      <template v-else>
        <v-row class="tw:max-w-7xl tw:mx-auto tw:py-10 tw:sm:px-6 tw:lg:px-8">
          <v-expansion-panels variant="default">
            <role-expansion-panel
              v-for="role in roles"
              :key="role.id"
              :can-delete="canDeleteRoles"
              :can-edit="canManageRoles"
              :data-role="role"
              :permission-categories="permissionCategories"
              @deleted="onRoleDeleted"
              @edit="openEditModal"
            />
          </v-expansion-panels>
        </v-row>
      </template>
    </v-container>

    <RoleModal
      v-model="showRoleModal"
      :permission-categories="permissionCategories"
      :role="selectedRole"
      @created="onRoleCreated"
      @updated="onRoleUpdated"
    />
  </AppLayout>
</template>
