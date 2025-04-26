<script setup>
  import { onMounted, ref } from 'vue';
  import AppLayout from '@/layouts/AppLayout.vue';
  import { http } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';
  import RoleExpansionPanel from './RoleExpansionPanel.vue';
  import { computed } from 'vue';
  import { notify } from '@/utils';

  const roles = ref([]);
  const permissions = ref([]);
  const isFetchingRoles = ref(false);
  const isFetchingPermissions = ref(false);
  const authStore = useAuthStore();
  const authUser = computed(() => authStore?.user);
  const canUpdateRoles = computed(() => authUser.value?.is_admin);

  onMounted(() => {
    fetchRoles();
    fetchPermissions();
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
  const fetchPermissions = () => {
    isFetchingPermissions.value = true;
    http
      .get('/api/v1/permissions')
      .then(({ data }) => {
        permissions.value = data?.data || [];
      })
      .catch(() => notify.error('Something went wrong while fetching permissions.'))
      .finally(() => (isFetchingPermissions.value = false));
  };
</script>

<template>
  <AppLayout title="Create Team">
    <template #head>
      <h2>Acl Roles</h2>
    </template>

    <v-container>
      <v-skeleton-loader
        v-if="isFetchingRoles || isFetchingPermissions"
        type="list-item-three-line@3"
      ></v-skeleton-loader>
      <v-card v-else-if="!canUpdateRoles"> Sorry you don't have permission to update roles</v-card>
      <v-row v-else class="tw:max-w-7xl tw:mx-auto tw:py-10 tw:sm:px-6 tw:lg:px-8">
        <v-expansion-panels variant="default">
          <role-expansion-panel
            v-for="role in roles"
            :key="role.id"
            :data-role="role"
            :permissions="permissions"
          ></role-expansion-panel>
        </v-expansion-panels>
      </v-row>
    </v-container>
  </AppLayout>
</template>
