<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { http } from '@/plugins';
  import AppUserLayout from '@/layouts/AppUserLayout';
  import { notify } from '@/utils';
  import EntityDetails from '@/components/EntityDetails.vue';
  import {
    mdiCity,
    mdiDirections,
    mdiEmailFast,
    mdiHomeCity,
    mdiPhoneClassic,
    mdiTownHall,
    mdiWeb,
  } from '@mdi/js';
  import { groupBy } from 'lodash';
  import { useSeoMeta } from '@unhead/vue';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
  });

  const church = ref(null);

  const pageTitle = computed(() =>
    church.value?.id ? `${church.value.name} - ${church.value.city || ''}` : 'Find Missa',
  );
  const pageDescription = computed(() =>
    church.value?.id
      ? `Mass, Confession and Adoration times at ${church.value.name} - ${church.value.city}`
      : 'Church Details: Mass, Confession and Adoration times.',
  );

  useSeoMeta({
    title: () => pageTitle.value,
    description: () => pageDescription.value,
    ogTitle: () => pageTitle.value,
    ogDescription: () => pageDescription.value,
    ogUrl: `https://findmissa.com/church-details/${props.churchId}`,
  });

  const isFetching = ref(false);
  const mass = ref([]);
  const confession = ref([]);
  const adoration = ref([]);
  const panel = ref(0);

  const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  const fields = computed(() => {
    const data = church.value;

    return [
      { icon: mdiHomeCity, text: 'Parish', value: data?.parish?.name },
      { icon: mdiCity, text: 'City/Town', value: data?.city },
      { icon: mdiTownHall, text: 'District/State', value: data?.district },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      { icon: mdiWeb, text: 'Website', value: data?.website_url, is_link: true },
      { icon: mdiPhoneClassic, text: 'Contact Phone', value: data?.contact_phone },
    ];
  });

  onMounted(() => {
    fetchChurchDetails();
  });

  const fetchChurchDetails = () => {
    if (!props.churchId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/church-details/${props.churchId}`)
      .then(({ data }) => {
        church.value = data?.data?.details || {};
        mass.value = groupBy(data?.data?.mass || [], 'day_of_week');
        adoration.value = groupBy(data?.data?.adoration || [], 'day_of_week');
        confession.value = groupBy(data?.data?.confession || [], 'day_of_week');
      })
      .catch(() => notify.error('Something went wrong fetching the church details.'))
      .finally(() => (isFetching.value = false));
  };
</script>

<template>
  <AppUserLayout>
    <template #head>
      <h2>Church Details</h2>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card"></v-skeleton-loader>
      <v-card v-else-if="!church">
        <v-card-title> No church found</v-card-title>
      </v-card>
      <v-row v-else class="tw:py-4">
        <!-- show church details -->
        <v-col cols="12">
          <EntityDetails :fields="fields" :title="`${church?.name} - ${church?.city}`">
            <template #subtitle>
              <v-btn
                v-if="church?.google_maps_link"
                class="tw:text-sm"
                color="primary"
                :href="church?.google_maps_link"
                :ripple="false"
                size="small"
                slim
                target="_blank"
                variant="plain"
              >
                Get Directions
                <v-icon end :icon="mdiDirections" size="large"></v-icon>
              </v-btn>
            </template>
          </EntityDetails>
        </v-col>
        <v-col class="tw:flex tw:justify-between" cols="12">
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">A list of church activities.</p>
        </v-col>
        <v-col cols="12">
          <v-expansion-panels v-model="panel">
            <v-expansion-panel>
              <v-expansion-panel-title
                :class="{
                  'tw:text-md! tw:uppercase! tw:font-semibold!': true,
                  'tw:bg-gray-100!': panel === 0,
                }"
              >
                Mass
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <v-table>
                  <thead>
                    <tr>
                      <th class="tw:uppercase tw:text-left tw:font-bold">Day of Week</th>
                      <th class="tw:uppercase tw:text-left tw:font-bold">Mass Schedule</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(day, index) in daysOfWeek" :key="index">
                      <td>{{ day }}</td>
                      <td>
                        <ul>
                          <li v-for="dayMass in mass[day]" :key="dayMass.id" class="tw:mr-2">
                            {{ dayMass?.start_time }} ({{ dayMass?.language }})
                          </li>
                        </ul>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </v-expansion-panel-text>
            </v-expansion-panel>
            <v-expansion-panel>
              <v-expansion-panel-title
                :class="{
                  'tw:text-md! tw:uppercase! tw:font-semibold!': true,
                  'tw:bg-gray-100!': panel === 1,
                }"
              >
                Confession
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <div v-if="!Object.keys(confession)?.length">No data available</div>
                <v-table v-else>
                  <thead>
                    <tr>
                      <th class="tw:uppercase tw:text-left tw:font-bold">Day of Week</th>
                      <th class="tw:uppercase tw:text-left tw:font-bold">Confession Schedule</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template v-for="(day, index) in daysOfWeek" :key="index">
                      <tr v-if="confession[day]">
                        <td>{{ day }}</td>
                        <td>
                          <ul>
                            <li v-for="conf in confession[day]" :key="conf.id" class="tw:mr-2">
                              {{ conf?.start_time }} ({{ conf?.language }})
                            </li>
                          </ul>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </v-table>
              </v-expansion-panel-text>
            </v-expansion-panel>
            <v-expansion-panel>
              <v-expansion-panel-title
                :class="{
                  'tw:text-md! tw:uppercase! tw:font-semibold!': true,
                  'tw:bg-gray-100!': panel === 2,
                }"
              >
                Adoration
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <div v-if="!Object.keys(adoration)?.length">No data available</div>
                <v-table v-else>
                  <thead>
                    <tr>
                      <th class="tw:uppercase tw:text-left tw:font-bold">Day of Week</th>
                      <th class="tw:uppercase tw:text-left tw:font-bold">Adoration Schedule</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template v-for="(day, index) in daysOfWeek" :key="index">
                      <tr v-if="adoration[day]">
                        <td>{{ day }}</td>
                        <td>
                          <ul>
                            <li v-for="ador in adoration[day]" :key="ador.id" class="tw:mr-2">
                              {{ ador?.start_time }} ({{ ador?.language }})
                            </li>
                          </ul>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </v-table>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </v-col>
      </v-row>
    </v-container>
  </AppUserLayout>
</template>
