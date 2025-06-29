<script setup>
  import DateInput from '@/components/DateInput.vue';
  import NavLink from '@/components/NavLink.vue';
  import AppUserLayout from '@/layouts/AppUserLayout.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import ShareButton from '@/components/ShareButton.vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import { ref, onMounted, computed, watch } from 'vue';
  import { useDate, useDisplay } from 'vuetify';
  import { debounce } from 'lodash';
  import { useSeoMeta } from '@unhead/vue';
  import { mdiMagnify, mdiShareAllOutline } from '@mdi/js';
  import { useRoute, useRouter } from 'vue-router';

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
  const startTime = parser.format(new Date(), 'fullTime24h')?.substring(0, 5);

  const router = useRouter();
  const route = useRoute();
  function convertTo12Hour(time24) {
    // Split the time string into hours and minutes
    const [hours, minutes] = time24.split(':');

    // Convert hours to number
    const hoursNum = parseInt(hours, 10);

    // Determine AM or PM
    const period = hoursNum >= 12 ? 'PM' : 'AM';

    // Convert to 12-hour format
    let hours12 = hoursNum % 12;
    hours12 = hours12 === 0 ? 12 : hours12; // Handle midnight (0 becomes 12)

    // Return formatted string
    return `${hours12}:${minutes} ${period}`;
  }

  const searchedQuery = ref({});

  const shareTitle = computed(() => `Share ${searchedQuery.value?.type || 'mass'} times`);

  const shareDescription = computed(() => {
    const query = searchedQuery.value;

    let description = `Checkout ${query.type || 'mass'} times on ${parser.format(dateUsed.value, 'fullDateWithWeekday')}`;

    if (query?.location) {
      description += ` in ${query.location}`;
    } else if (query?.name) {
      description += ` in ${query.name}`;
    }

    if (query?.fromTime) {
      description += ` from ${convertTo12Hour(query.fromTime)} onwards.`;
    }

    return description;
  });

  const queryParams = computed(() => {
    const query = route.query;
    // validate that pattern matches HH:MM
    const timeReg = /^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/;
    if (!query?.fromTime || !timeReg.test(query?.fromTime)) {
      query.fromTime = startTime;
    }
    // validate date
    if (!query?.date || useDayjs(query.date).isValid()) {
      query.date = parser.date(currentDate);
    }
    //validate types
    if (!['mass', 'confession', 'adoration'].includes(query?.type)) {
      query.type = 'mass';
    }

    return query;
  });

  const form = useForm({
    start_time: queryParams.value?.fromTime || startTime,
    church_name: queryParams.value?.name || '',
    district: queryParams.value?.location || '',
    type: queryParams.value?.type || 'mass',
    date: queryParams.value?.date || parser.date(currentDate),
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

        searchedQuery.value = {
          date: form.date,
          fromTime: form.start_time,
          location: form.district,
          type: form.type,
          name: form.church_name,
        };

        router.push({
          query: searchedQuery.value,
        });
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
          <v-alert class="tw:font-semibold tw:text-md" prominent type="info" variant="tonal">
            We are still gathering information from various Catholic parishes in Uganda. If you
            notice any inaccuracies or missing details, please share updates with us using this
            Google Form
            <a href="https://forms.gle/kGiX1A36dW3is1BJA" target="_blank">(click here)</a> .
          </v-alert>
        </v-col>
        <v-col class="tw:flex tw:justify-around" cols="12">
          <v-btn-toggle v-model="form.type" color="primary" group mandatory rounded>
            <v-btn rounded="0" value="mass">Mass</v-btn>
            <v-btn rounded="0" value="confession">Confession</v-btn>
            <v-btn rounded="0" value="adoration">Adoration</v-btn>
          </v-btn-toggle>
        </v-col>
        <v-col cols="12" md="3">
          <DateInput
            v-model="date"
            :hide-actions="false"
            label="Date"
            :min="currentDate"
            variant="underlined"
          ></DateInput>
        </v-col>
        <v-col cols="12" md="2">
          <TimeInput
            v-model="form.start_time"
            :error="form.errors.start_time"
            label="From Time (24h)"
            required
            title="Select Mass Time"
            variant="underlined"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="form.district"
            clearable
            density="default"
            label="District/Town"
            variant="underlined"
          ></v-text-field>
        </v-col>
        <v-col class="tw:flex tw:items-end tw:justify-between" cols="12" md="4">
          <v-text-field
            v-model="form.church_name"
            class="tw:mr-3"
            clearable
            density="default"
            label="Parish/Church Name"
            variant="underlined"
          ></v-text-field>
          <v-btn color="primary" :prepend-icon="mdiMagnify" variant="elevated" @click="getResults">
            Search
          </v-btn>
        </v-col>
        <share-button :description="shareDescription" :title="shareTitle" />
        <template v-if="form.processing">
          <v-col cols="12">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </template>
        <v-col v-else cols="12">
          <v-sheet>
            <v-divider opacity="10"></v-divider>
            <v-card-title class="tw:flex! tw:justify-between">
              <span :class="{ 'tw:text-base': smAndDown, 'tw:text-md': !smAndDown }">
                {{ showDate }}
              </span>
              <span class="tw:text-base">
                {{
                  `${!smAndDown ? 'Showing:' : ''} ${results?.data?.length} of ${results?.meta?.total} results`
                }}
              </span>
            </v-card-title>
            <v-divider opacity="10"></v-divider>
            <v-card
              v-if="!results?.data || !results?.data?.length"
              class="tw:flex tw:justify-around"
              height="150"
            >
              <v-card-title class="tw:mx-auto tw:p-12 tw:my-auto">
                No {{ form.type || 'activities' }} found for the
                {{
                  form.district || form.church_name ? ' selected search query.' : ' selected time. '
                }}
              </v-card-title>
            </v-card>
            <v-timeline v-else class="tw:md:ml-4" density="comfortable" side="end">
              <v-timeline-item
                v-for="activity in results?.data"
                :key="activity?.id"
                dot-color="primary"
                size="small"
              >
                <div class="tw:flex">
                  <strong class="tw:me-4!">
                    {{ `${activity?.start_time}` }}
                  </strong>
                  <div>
                    <strong class="tw:capitalize">
                      {{ `${activity?.activity_type_description} (${activity?.language})` }}
                    </strong>
                    <div>
                      <NavLink
                        class="tw:text-indigo-600"
                        :href="{
                          name: 'church.details',
                          params: { churchId: activity?.church?.code_name },
                        }"
                      >
                        {{
                          `${activity?.church?.name} - ${activity?.church?.district} (${activity?.church?.city})`
                        }}
                        <v-icon end :icon="mdiShareAllOutline"></v-icon>
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
