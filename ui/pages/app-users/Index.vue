<script setup>
  import DateInput from '@/components/DateInput.vue';
  import NavLink from '@/components/NavLink.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppUserLayout from '@/layouts/AppUserLayout.vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import { ref, onMounted, computed } from 'vue';
  import { useDate } from 'vuetify/lib/framework.mjs';

  const results = ref(null);
  const dateUsed = ref(null);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const date = ref(parser.date(currentDate));

  const form = useForm({
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
        <v-col class="tw-flex tw-justify-around" cols="12">
          <v-btn-toggle v-model="form.type" color="primary" group rounded>
            <v-btn value="mass">Mass</v-btn>
            <v-btn value="confession">Confession</v-btn>
            <v-btn value="adoration">Adoration</v-btn>
          </v-btn-toggle>
        </v-col>
        <v-col class="tw-flex tw-justify-around tw-gap-2 tw-items-center" cols="12">
          <DateInput
            v-model="date"
            density="compact"
            label="Date"
            :min="currentDate"
            variant="outlined"
          ></DateInput>
          <TextInput
            v-model="form.church_name"
            clearable
            label="Church Name"
            variant="outlined"
          ></TextInput>
          <TextInput
            v-model="form.district_name"
            clearable
            label="District"
            variant="outlined"
          ></TextInput>
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
                No activities found
              </v-card-title>
            </v-card>
            <v-timeline v-else class="tw-ml-4" density="comfortable" side="end">
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
