<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { useDisplay } from 'vuetify';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppLayout from '@/layouts/AppLayout';
  import UserModal from './UserModal.vue';

  const { smAndDown } = useDisplay();
  const users = ref([]);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);
  const userToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Email', value: 'email' },
    { title: 'Has Two Factor', value: 'two_factor_enabled' },
    { title: 'Super Admin', value: 'is_admin' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchUsers();
  });

  const fetchUsers = () => {
    isFetching.value = true;
    http.get('/api/v1/users').then(({ data }) => {
      users.value = data?.data;
    });
  };

  function showModal(data = null) {
    userToEdit.value = data;
    showingModal.value = true;
  }

  function updateChurch(updates) {
    users.value = users.value?.map((user) => {
      if (user?.id === updates?.id) return updates;
      return user;
    });
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Users</h2>
    </template>
    <user-modal
      v-model:show-modal="showingModal"
      :user="userToEdit"
      @created="users.push($event)"
      @updated="updateChurch"
    />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the users.</p>
          <PrimaryButton @click="showModal"> Add Church</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <v-data-table
            :headers="headers"
            :items="users || []"
            :mobile="smAndDown"
            mobile-breakpoint="md"
          >
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
          </v-data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
