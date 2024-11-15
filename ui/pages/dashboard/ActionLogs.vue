<script setup>
  import { onMounted, computed } from 'vue';
  import groupBy from 'lodash/groupBy';
  import { useDayjs } from '@/utils';
  import { useActivityLogs } from '@/composables/useActivityLogs';

  defineProps({
    heading: { type: String, default: 'Activity Logs' },
  });
  const { logs, fetchActionLogs, loading } = useActivityLogs();
  const groupedEvents = computed(() =>
    groupBy(logs.value, (log) => useDayjs(log.datetime).format('DD-MMM-YYYY')),
  );

  onMounted(() => fetchActionLogs());
</script>

<template>
  <v-card>
    <v-skeleton-loader v-if="loading" type="table"></v-skeleton-loader>
    <v-card-text
      v-else-if="!logs?.length"
      class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-min-h-[200px]"
    >
      <v-icon icon="$calendar"></v-icon>
      <p class="tw-mt4 tw-text-lg">No events registered yet.</p>
    </v-card-text>
    <div v-for="(items, date) in groupedEvents" v-else :key="date">
      <v-divider opacity="10"></v-divider>
      <v-card-title>{{ date }}</v-card-title>
      <v-divider opacity="10"></v-divider>
      <v-timeline class="tw-ml-6" density="comfortable" side="end">
        <v-timeline-item v-for="event in items" :key="event.id" density="compact" size="small">
          <template #icon>
            <v-avatar size="40">
              <v-img :alt="event.name" :src="event.avatar"></v-img>
            </v-avatar>
          </template>
          <div class="">
            <div>
              <p class="tw-text-sm tw-text-gray-500">
                <a class="tw-font-medium tw-text-gray-900" :href="event.href">
                  {{ event.username }}
                </a>
                {{ event.description }}
              </p>
            </div>
            <div class="tw-text-xs tw-text-gray-500 tw-whitespace-nowrap">
              <!-- <time :datetime="event.datetime"></time> -->
              {{ useDayjs(event.datetime).fromNow() }}
            </div>
          </div>
        </v-timeline-item>
      </v-timeline>
    </div>
  </v-card>
</template>
