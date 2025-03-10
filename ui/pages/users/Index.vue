<script setup>
  import { ref } from 'vue';
  import DataTableServer from '@/components/DataTableServer';
  import AppLayout from '@/layouts/AppLayout';
  import { mdiPencilOutline } from '@mdi/js';
  import UserModal from './UserModal.vue';

  const users = ref([]);
  const showingModal = ref(false);
  const userToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Email', value: 'email' },
    { title: 'Has Two Factor', value: 'two_factor_enabled' },
    { title: 'Super Admin', value: 'is_admin' },
    { title: '', value: 'actions' },
  ];

  function showModal(user) {
    userToEdit.value = user;
    showingModal.value = true;
  }

  function updateUser(updatedUser) {
    users.value = users.value?.map((user) => {
      if (user?.id === updatedUser?.id) {
        return { ...user, ...updatedUser };
      }
      return user;
    });
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Users</h2>
    </template>
    <user-modal v-model:show-modal="showingModal" :user="userToEdit" @updated="updateUser" />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the users.</p>
        </v-col>
        <v-col cols="12">
          <data-table-server :headers="headers" url="/api/v1/users" v-model:items="users">
            <template #item.two_factor_enabled="{ item }">
              <v-chip :color="item.two_factor_enabled ? 'primary' : ''" size="small" variant="flat">
                {{ item.two_factor_enabled ? 'true' : 'false' }}
              </v-chip>
            </template>
            <template #item.is_admin="{ item }">
              <v-chip :color="item.is_admin ? 'primary' : ''" size="small" variant="flat">
                {{ item.is_admin ? 'true' : 'false' }}
              </v-chip>
            </template>
            <template #item.actions="{ item }">
              <v-icon color="primary" :icon="mdiPencilOutline" @click="showModal(item)"></v-icon>
            </template>
          </data-table-server>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
