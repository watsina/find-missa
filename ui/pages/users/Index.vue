<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppLayout from '@/layouts/AppLayout';
  // import UserInvitationModal from './UserInvitationModal.vue';

  const users = ref([]);
  const invitedUsers = ref([]);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Email', value: 'email' },
    { title: 'Has Two Factor', value: 'two_factor_enabled' },
    { title: 'Super Admin', value: 'is_admin' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchUsers();
    fetchInvitedUsers();
  });

  const fetchUsers = () => {
    isFetching.value = true;
    http.get('/api/v1/users').then(({ data }) => {
      users.value = data?.data;
    });
  };

  const fetchInvitedUsers = () => {
    isFetching.value = true;
    http.get('/api/v1/user-invitations').then(({ data }) => {
      invitedUsers.value = data?.data;
    });
  };

  function showModal() {
    showingModal.value = true;
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Users</h2>
    </template>
    <user-invitation-modal v-model:show-modal="showingModal" @created="invitedUsers.push($event)" />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the users.</p>
          <PrimaryButton @click="showModal"> Invite User</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <data-table :headers="headers" :items="users || []" :search="search">
            <template #top>
              <TextInput
                id="search"
                v-model="search"
                autofocus
                class="tw-block tw-mb-2 tw-mx-2"
                error=""
                label="Search"
                placeholder="Search users..."
                required
                type="text"
              />
            </template>
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
              <!-- <v-icon color="primary" :icon="mdiPencilOutline" @click="showModal(item)"></v-icon> -->
            </template>
          </data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
