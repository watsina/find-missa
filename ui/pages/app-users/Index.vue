<script setup>
  import DateInput from '@/components/DateInput.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppUserLayout from '@/layouts/AppUserLayout.vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import { ref, onMounted, computed } from 'vue';
  import { useDate } from 'vuetify/lib/framework.mjs';

  const results = ref(null);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');

  const form = useForm({
    church_name: '',
    district_name: '',
    type: 'mass',
    date: parser.date(currentDate),
  });

  const showDate = computed(() =>
    useDayjs().isSame(useDayjs(form.date).format('YYYY-MM-DD'), 'day')
      ? 'Today'
      : parser.format(form.date, 'fullDateWithWeekday'),
  );

  function getResults() {
    form.get('/api/v1/search', {
      onSuccess: ({ data }) => {
        results.value = data || [];
      },
      onError: () => notify.error('Something went wrong...'),
    });
  }

  onMounted(() => {
    getResults();
  });
</script>

<template>
  <AppUserLayout>
    <v-container>
      <v-row class="py-4">
        <v-col class="tw-flex tw-justify-around" cols="12">
          <v-btn-toggle v-model="form.type" color="primary" group rounded>
            <v-btn value="mass">Mass</v-btn>
            <v-btn value="confession">Confession</v-btn>
            <v-btn value="adoration">Adoration</v-btn>
          </v-btn-toggle>
        </v-col>
        <v-col class="tw-flex tw-justify-around tw-gap-2 tw-items-center" cols="12">
          <DateInput
            v-model="form.date"
            density="compact"
            label="Date"
            :min="currentDate"
            variant="outlined"
          ></DateInput>
          <TextInput v-model="form.church_name" label="Church Name" variant="outlined"></TextInput>
          <TextInput v-model="form.district_name" label="District" variant="outlined"></TextInput>
          <PrimaryButton variant="outlined" @click="getResults"> Search </PrimaryButton>
        </v-col>
        <template v-if="form.processing">
          <v-col cols="12">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </template>
        <v-col v-else cols="12">
          <v-sheet>
            <v-card
              v-if="!results || !results?.length"
              class="tw-flex tw-justify-around"
              height="300"
            >
              <v-card-text class="tw-mx-auto tw-p-12 tw-my-auto"> No activities found </v-card-text>
            </v-card>
            <div v-else>
              <v-divider opacity="10"></v-divider>
              <v-card-title>
                {{ showDate }}
              </v-card-title>
              <v-divider opacity="10"></v-divider>
              <v-timeline class="tw-ml-4" density="comfortable" side="end">
                <v-timeline-item
                  v-for="activity in results"
                  :key="activity?.id"
                  dot-color="primary"
                  size="small"
                >
                  <div class="d-flex">
                    <strong class="me-4">
                      {{ `${activity?.start_time} - ${activity?.end_time}` }}
                    </strong>
                    <div>
                      <strong class="tw-capitalize">
                        {{ `${activity?.occurrence} ${activity?.type} (${activity?.language})` }}
                      </strong>
                      <div class="text-caption">
                        {{ `${activity?.church?.name} - ${activity?.church?.city}` }}
                      </div>
                    </div>
                  </div>
                </v-timeline-item>
              </v-timeline>
            </div>
          </v-sheet>
        </v-col>
      </v-row>
    </v-container>
  </AppUserLayout>
</template>
