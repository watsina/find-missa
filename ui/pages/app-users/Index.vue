<script setup>
  import TextInput from '@/components/TextInput.vue';
  import AppUserLayout from '@/layouts/AppUserLayout.vue';
  import { http } from '@/plugins';
  import { notify } from '@/utils';
  import { ref, onMounted } from 'vue';
  import { useDate } from 'vuetify/lib/framework.mjs';

  const isFetching = ref(false);
  const results = ref(null);
  const type = ref('mass');
  const date = ref(useDate().format('keyboardDate'));

  onMounted(() => {
    isFetching.value = true;
    http
      .get('/api/v1/search', {
        params: { type: type.value, date: date.value },
      })
      .then(({ data }) => {
        results.value = data?.data || [];
      })
      .catch(() => notify.error('Something went wrong...'))
      .finally(() => (isFetching.value = false));
  });
</script>

<template>
  <AppUserLayout>
    <v-container>
      <v-row class="py-4">
        <v-col class="tw-flex tw-justify-around" cols="12">
          <v-btn-toggle v-model="type" color="primary" group rounded>
            <v-btn value="mass">Mass</v-btn>
            <v-btn value="confession">Confession</v-btn>
            <v-btn value="adoration">Adoration</v-btn>
          </v-btn-toggle>
        </v-col>
        <v-col cols="12">
          <TextInput label="Search" rounded variant="solo"></TextInput>
        </v-col>
        <template v-if="isFetching">
          <v-col v-for="i in 6" :key="i" cols="12" md="4">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </template>
        <v-col cols="12">
          <v-sheet>
            <v-card
              v-if="!results || !results?.length"
              class="tw-flex tw-justify-around"
              height="300"
            >
              <v-card-content class="tw-mx-auto tw-p-12 tw-my-auto">
                No activities found
              </v-card-content>
            </v-card>
            <v-timeline v-else align="start" side="end">
              <v-timeline-item
                v-for="activity in results"
                :key="activity?.id"
                dot-color="teal-lighten-3"
                size="small"
              >
                <div class="d-flex">
                  <strong class="me-4">
                    {{ `${activity?.start_time} - ${activity?.end_time}` }}
                  </strong>
                  <div>
                    <strong class="tw-capitalize">
                      {{ `${activity?.occurrence} ${activity?.type}` }}
                    </strong>
                    <div class="text-caption">
                      {{ `${activity?.church?.name} - ${activity?.church?.city}` }}
                    </div>
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
          </v-sheet>
        </v-col>
      </v-row>
    </v-container>
  </AppUserLayout>
</template>
