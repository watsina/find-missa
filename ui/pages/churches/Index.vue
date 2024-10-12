<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { useDisplay } from 'vuetify';
  import TextInput from '@/components/TextInput.vue';
  import AppLayout from '@/layouts/AppLayout';

  const { smAndDown } = useDisplay();
  const churches = ref([]);
  const search = ref('');
  const isFetching = ref(false);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Coordinates', value: 'coordinates' },
    { title: 'Contact Email', value: 'contact_email' },
    { title: 'Postal Address', value: 'postal_address' },
    { title: 'Physical Address', value: 'physical_address' },
    { title: 'Parish', value: 'parish' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchDioceses();
  });

  const fetchDioceses = () => {
    isFetching.value = true;
    http.get('/api/v1/church').then(({ data }) => {
      console.info({ data });
      churches.value = data?.data;
    });
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Churches</h2>
    </template>
    <v-container>
      <v-row class="tw-py-12">
        <v-col cols="12" md="8">
          <p class="tw-mt-2 tw-text-sm tw-text-gray-700">A list of all the churches.</p>
        </v-col>
        <v-col cols="12">
          <v-data-table
            :headers="headers"
            :items="churches || []"
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
                placeholder="Search churches..."
                required
                type="text"
              />
            </template>
            <template #item.actions="{ item }"> </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
