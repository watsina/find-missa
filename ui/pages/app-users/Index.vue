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
  import { useDate, useDisplay } from 'vuetify';
  import { debounce } from 'lodash';
  import { useSeoMeta } from '@unhead/vue';
  import { mdiMagnify, mdiPageNextOutline, mdiShareAllOutline } from '@mdi/js';

  const title = 'Find Catholic Mass - Uganda';
  const description =
    'A web app that helps you find mass, confession and adoration in a parish or subparish near you. Search for mass, adoration and confession by time, church/parish name and town/district...';

  useSeoMeta({
    title: title,
    description: description,
    ogTitle: title,
    ogDescription: description,
    ogUrl: 'https://findmissa.com',
  });

  const { smAndDown } = useDisplay();
  const results = ref(null);
  const dateUsed = ref(null);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const date = ref(parser.date(currentDate));

  const form = useForm({
    start_time: parser.format(new Date(), 'fullTime24h')?.substring(0, 5),
    church_name: '',
    district: '',
    type: 'mass',
    date: parser.date(currentDate),
  });

  const showDate = computed(() => {
    const date = smAndDown.value
      ? parser.format(dateUsed.value, 'fullDate')
      : parser.format(dateUsed.value, 'fullDateWithWeekday');

    return useDayjs().isSame(useDayjs(dateUsed.value).format('YYYY-MM-DD'), 'day') ? 'Today' : date;
  });

  watch(
    () => form.type,
    debounce(() => {
      getResults();
    }, 300),
  );

  function getResults() {
    form.date = useDayjs(date.value).format('YYYY-MM-DD');
    form.get('/api/v1/search', {
      onSuccess: (data) => {
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
          <v-alert class="tw-font-semibold tw-text-md" prominent type="info" variant="tonal">
            We are still gathering information from various Catholic parishes in Uganda. If you
            notice any inaccuracies or missing details, please share updates with us using this
            Google Form
            <a href="https://forms.gle/kGiX1A36dW3is1BJA" target="_blank">(click here)</a> .
          </v-alert>
        </v-col>
        <v-col class="tw-flex tw-justify-around" cols="12">
          <v-btn-toggle v-model="form.type" color="primary" mandatory group rounded>
            <v-btn rounded="0" value="mass">Mass</v-btn>
            <v-btn rounded="0" value="confession">Confession</v-btn>
            <v-btn rounded="0" value="adoration">Adoration</v-btn>
          </v-btn-toggle>
        </v-col>
        <v-col cols="12" md="3">
          <DateInput
            v-model="date"
            label="Date"
            :min="currentDate"
            variant="underlined"
          ></DateInput>
        </v-col>
        <v-col cols="12" md="2">
          <TimeInput
            v-model="form.start_time"
            title="Select Mass Time"
            :error="form.errors.start_time"
            label="From Time (24h)"
            required
            variant="underlined"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="form.district"
            variant="underlined"
            density="default"
            clearable
            label="District/Town"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="4" class="tw-flex tw-items-end tw-justify-between">
          <v-text-field
            class="tw-mr-3"
            v-model="form.church_name"
            density="default"
            variant="underlined"
            clearable
            label="Parish/Church Name"
          ></v-text-field>
          <v-btn :prepend-icon="mdiMagnify" variant="elevated" rounded="xl" @click="getResults">
            Search
          </v-btn>
        </v-col>
        <template v-if="form.processing">
          <v-col cols="12">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </template>
        <v-col v-else cols="12">
          <v-sheet>
            <v-divider opacity="10"></v-divider>
            <v-card-title class="!tw-flex tw-justify-between">
              <span :class="{ 'tw-text-base': smAndDown, 'tw-text-md': !smAndDown }">
                {{ showDate }}
              </span>
              <span class="tw-text-base">
                {{
                  `${!smAndDown ? 'Showing:' : ''} ${results?.data?.length} of ${results?.meta?.total} results`
                }}
              </span>
            </v-card-title>
            <v-divider opacity="10"></v-divider>
            <v-card
              v-if="!results?.data || !results?.data?.length"
              class="tw-flex tw-justify-around"
              height="150"
            >
              <v-card-title class="tw-mx-auto tw-p-12 tw-my-auto">
                No {{ form.type || 'activities' }} found
              </v-card-title>
            </v-card>
            <v-timeline v-else class="md:tw-ml-4" density="comfortable" side="end">
              <v-timeline-item
                v-for="activity in results?.data"
                :key="activity?.id"
                dot-color="primary"
                size="small"
              >
                <div class="tw-flex">
                  <strong class="tw-me-4">
                    {{ `${activity?.start_time}` }}
                  </strong>
                  <div>
                    <strong class="tw-capitalize">
                      {{ `${activity?.type} (${activity?.language})` }}
                    </strong>
                    <div>
                      <NavLink
                        class="tw-text-indigo-600"
                        :href="{
                          name: 'church.details',
                          params: { churchId: activity?.church?.code_name },
                        }"
                      >
                        {{
                          `${activity?.church?.name} - ${activity?.church?.district} (${activity?.church?.city})`
                        }}
                        <v-icon :icon="mdiShareAllOutline" end></v-icon>
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
