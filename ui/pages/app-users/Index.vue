<script setup>
  import DateInput from '@/components/DateInput.vue';
  import NavLink from '@/components/NavLink.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppUserLayout from '@/layouts/AppUserLayout.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import { ref, onMounted, computed, watch } from 'vue';
  import { useDate } from 'vuetify/lib/framework.mjs';
  import { debounce } from 'lodash';
  import { useHead } from '@unhead/vue';

  useHead({
    title: 'Find Missa',
    meta: [
      {
        name: 'description',
        content:
          'Find Missa is an application that helps you find mass, confession and adoration in a parish or subparish near you. Choose the country and search for mass, adoration and confession by church name, town or district.',
      },
    ],
  });

  const results = ref(null);
  const dateUsed = ref(null);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const date = ref(parser.date(currentDate));

  const form = useForm({
    start_time: parser.format(new Date(), 'fullTime24h')?.substring(0, 5),
    church_name: '',
    district_name: '',
    type: 'mass',
    date: parser.date(currentDate),
  });

  const showDate = computed(() =>
    useDayjs().isSame(useDayjs(dateUsed.value).format('YYYY-MM-DD'), 'day')
      ? 'Today'
      : parser.format(dateUsed.value, 'fullDateWithWeekday'),
  );

  watch(
    () => form.type,
    debounce(() => {
      getResults();
    }, 300),
  );

  function getResults() {
    form.date = useDayjs(date.value).format('YYYY-MM-DD');
    form.get('/api/v1/search', {
      onSuccess: ({ data }) => {
        results.value = data || [];
        dateUsed.value = form.date;
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
        <v-col cols="12">
          <v-alert
            class="tw-uppercase tw-font-semibold tw-text-md"
            prominent
            type="warning"
            variant="tonal"
          >
            This app is still in development, we are still gathering data and the app is undergoing
            testing and is not yet ready for public use.
          </v-alert>
        </v-col>
        <v-col class="tw-flex tw-justify-around" cols="12">
          <v-btn-toggle v-model="form.type" color="primary" group rounded>
            <v-btn value="mass">Mass</v-btn>
            <v-btn value="confession">Confession</v-btn>
            <v-btn value="adoration">Adoration</v-btn>
          </v-btn-toggle>
        </v-col>
        <v-col cols="12" md="3">
          <DateInput
            v-model="date"
            density="compact"
            label="Date"
            :min="currentDate"
            variant="outlined"
          ></DateInput>
        </v-col>
        <v-col cols="12" md="2">
          <TimeInput
            v-model="form.start_time"
            :error="form.errors.start_time"
            label="From Time (24h)"
            required
            variant="outlined"
          />
        </v-col>
        <v-col cols="12" md="3">
          <TextInput
            v-model="form.church_name"
            clearable
            label="Parish/Church Name"
            variant="outlined"
          ></TextInput>
        </v-col>
        <v-col cols="12" md="3">
          <TextInput
            v-model="form.district_name"
            clearable
            label="District"
            variant="outlined"
          ></TextInput>
        </v-col>
        <v-col class="d-flex align-end" cols="12" md="1">
          <PrimaryButton variant="outlined" @click="getResults"> Search </PrimaryButton>
        </v-col>
        <template v-if="form.processing">
          <v-col cols="12">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </template>
        <v-col v-else cols="12">
          <v-sheet>
            <v-divider opacity="10"></v-divider>
            <v-card-title>
              {{ showDate }}
            </v-card-title>
            <v-divider opacity="10"></v-divider>
            <v-card
              v-if="!results || !results?.length"
              class="tw-flex tw-justify-around"
              height="150"
            >
              <v-card-title class="tw-mx-auto tw-p-12 tw-my-auto">
                No {{ form.type || 'activities' }} found
              </v-card-title>
            </v-card>
            <v-timeline v-else class="md:tw-ml-4" density="comfortable" side="end">
              <v-timeline-item
                v-for="activity in results"
                :key="activity?.id"
                dot-color="primary"
                size="small"
              >
                <div class="d-flex">
                  <strong class="me-4">
                    {{ `${activity?.start_time}` }}
                  </strong>
                  <div>
                    <strong class="tw-capitalize">
                      {{ `${activity?.occurrence} ${activity?.type} (${activity?.language})` }}
                    </strong>
                    <div class="text-caption">
                      <NavLink
                        class="tw-text-indigo-600"
                        :href="{
                          name: 'church.details',
                          params: { churchId: activity?.church?.id },
                        }"
                      >
                        {{
                          `${activity?.church?.name} - ${activity?.church?.district} (${activity?.church?.city})`
                        }}
                      </NavLink>
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
