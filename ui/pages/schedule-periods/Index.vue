<script setup>
  import { mdiDeleteCircleOutline, mdiPencilOutline, mdiCalendarClock } from '@mdi/js';
  import DataTableServer from '@/components/DataTableServer';
  import AppLayout from '@/layouts/AppLayout';
  import SchedulePeriodModal from './Modal.vue';
  import { ref, computed } from 'vue';
  import { http, notify } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';

  const authStore = useAuthStore();
  const authUser = computed(() => authStore.user);

  const showingModal = ref(false);
  const isDeleting = ref(false);
  const periodToEdit = ref(null);
  const periods = ref([]);

  function showModal(period = null) {
    periodToEdit.value = period;
    showingModal.value = true;
  }

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Start Date', value: 'start_date' },
    { title: 'End Date', value: 'end_date' },
    { title: 'Status', value: 'status' },
    { title: 'Scope', value: 'is_universal' },
    { title: '', value: 'actions' },
  ];

  function updatePeriods(updated) {
    periods.value = periods.value?.map((period) => {
      if (period?.id === updated?.id) {
        return updated;
      }
      return period;
    });
  }

  async function deletePeriod(period) {
    if (!period?.id) {
      return;
    }

    // Check permissions - only super admin can delete universal periods
    if (!period.team_id && !authUser.value?.is_admin) {
      notify.error('Only super admins can delete universal schedule periods.');
      return;
    }

    if (
      !(await notify.confirm(
        'This will also remove this period from any associated activity times. This action is not reversible.',
      ))
    ) {
      return;
    }

    isDeleting.value = true;

    http
      .delete(`/api/v1/schedule-periods/${period.id}`)
      .then(() => {
        notify.success('Schedule period deleted successfully.');
        periods.value = periods.value?.filter((p) => period?.id !== p?.id);
      })
      .catch(() => notify.error('There was an error deleting the schedule period.'))
      .finally(() => (isDeleting.value = false));
  }

  function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  }

  function getStatusInfo(period) {
    const now = new Date();
    const startDate = new Date(period.start_date);
    const endDate = new Date(period.end_date);

    if (!period.is_active) {
      return { text: 'Inactive', color: 'grey' };
    }
    if (now < startDate) {
      return { text: 'Upcoming', color: 'info' };
    }
    if (now >= startDate && now <= endDate) {
      return { text: 'Current', color: 'success' };
    }
    return { text: 'Past', color: 'warning' };
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">
        Schedule Periods
      </h2>
    </template>
    <v-container>
      <v-row class="tw:py-12">
        <v-col class="tw:flex" cols="12">
          <schedule-period-modal
            v-model="showingModal"
            :period="periodToEdit"
            @created="periods.push($event)"
            @updated="updatePeriods"
          />
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">
            Manage liturgical schedule periods like Advent, Lent, Easter Season, and Ordinary Time.
          </p>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            :disabled="isDeleting"
            :prepend-icon="mdiCalendarClock"
            @click="showModal()"
          >
            Add Schedule Period
          </v-btn>
        </v-col>
        <v-col cols="12">
          <DataTableServer
            v-model:items="periods"
            :headers="headers"
            url="/api/v1/schedule-periods"
          >
            <template #item.start_date="{ item }">
              {{ formatDate(item?.start_date) }}
            </template>
            <template #item.end_date="{ item }">
              {{ formatDate(item?.end_date) }}
            </template>
            <template #item.status="{ item }">
              <v-chip :color="getStatusInfo(item).color" size="small">
                {{ getStatusInfo(item).text }}
              </v-chip>
              <v-chip
                v-if="item?.is_recurring"
                class="tw:ml-1"
                color="purple"
                size="small"
                variant="outlined"
              >
                Recurring
              </v-chip>
            </template>
            <template #item.is_universal="{ item }">
              <v-chip v-if="!item?.team_id" color="primary" size="small">Universal</v-chip>
              <v-chip v-else color="secondary" size="small">Team</v-chip>
            </template>
            <template #item.actions="{ item }">
              <v-btn
                color="primary"
                :disabled="isDeleting"
                :icon="mdiPencilOutline"
                title="Edit Schedule Period"
                variant="text"
                @click="showModal(item)"
              ></v-btn>
              <v-btn
                color="error"
                :disabled="isDeleting || (!item?.team_id && !authUser?.is_admin)"
                :icon="mdiDeleteCircleOutline"
                title="Delete Schedule Period"
                variant="text"
                @click="deletePeriod(item)"
              ></v-btn>
            </template>
          </DataTableServer>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
