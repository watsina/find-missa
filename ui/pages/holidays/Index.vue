<script setup>
  import {
    mdiDeleteCircleOutline,
    mdiPencilOutline,
    mdiCalendarStar,
    mdiCheckCircle,
    mdiMinusCircleOutline,
  } from '@mdi/js';
  import DataTableServer from '@/components/DataTableServer';
  import AppLayout from '@/layouts/AppLayout';
  import HolidayModal from './Modal.vue';
  import { ref, computed } from 'vue';
  import { http, notify } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';

  const authStore = useAuthStore();
  const authUser = computed(() => authStore.user);

  const showingModal = ref(false);
  const isDeleting = ref(false);
  const holidayToEdit = ref(null);
  const holidays = ref([]);

  function showModal(holiday = null) {
    holidayToEdit.value = holiday;
    showingModal.value = true;
  }

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Slug', value: 'slug' },
    { title: 'Type', value: 'type' },
    { title: 'Holy Day', value: 'is_holy_day_of_obligation' },
    { title: 'Universal', value: 'is_universal' },
    { title: 'Color', value: 'liturgical_color' },
    { title: '', value: 'actions' },
  ];

  function updateHolidays(updated) {
    holidays.value = holidays.value?.map((holiday) => {
      if (holiday?.id === updated?.id) {
        return updated;
      }
      return holiday;
    });
  }

  async function deleteHoliday(holiday) {
    if (!holiday?.id) {
      return;
    }

    // Check permissions - only super admin can delete universal holidays
    if (holiday.is_universal && !authUser.value?.is_admin) {
      notify.error('Only super admins can delete universal holidays.');
      return;
    }

    if (
      !(await notify.confirm(
        'This will also delete all holiday instances. This action is not reversible.',
      ))
    ) {
      return;
    }

    isDeleting.value = true;

    http
      .delete(`/api/v1/holidays/${holiday.id}`)
      .then(() => {
        notify.success('Holiday deleted successfully.');
        holidays.value = holidays.value?.filter((h) => holiday?.id !== h?.id);
      })
      .catch(() => notify.error('There was an error deleting the holiday.'))
      .finally(() => (isDeleting.value = false));
  }

  function getTypeLabel(type) {
    const types = {
      fixed: 'Fixed Date',
      'easter-relative': 'Easter Relative',
      computed: 'Computed',
    };
    return types[type] || type;
  }

  function getColorClass(color) {
    const colors = {
      white: 'tw:bg-gray-100 tw:text-gray-800',
      red: 'tw:bg-red-100 tw:text-red-800',
      purple: 'tw:bg-purple-100 tw:text-purple-800',
      green: 'tw:bg-green-100 tw:text-green-800',
      rose: 'tw:bg-pink-100 tw:text-pink-800',
      gold: 'tw:bg-yellow-100 tw:text-yellow-800',
    };
    return colors[color] || 'tw:bg-gray-100 tw:text-gray-800';
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">Holidays</h2>
    </template>
    <v-container>
      <v-row class="tw:py-12">
        <v-col class="tw:flex" cols="12">
          <holiday-modal
            v-model="showingModal"
            :holiday="holidayToEdit"
            @created="holidays.push($event)"
            @updated="updateHolidays"
          />
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">
            Manage liturgical holidays and holy days of obligation.
          </p>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            :disabled="isDeleting"
            :prepend-icon="mdiCalendarStar"
            @click="showModal()"
          >
            Add Holiday
          </v-btn>
        </v-col>
        <v-col cols="12">
          <DataTableServer v-model:items="holidays" :headers="headers" url="/api/v1/holidays">
            <template #item.type="{ item }">
              <v-chip size="small" variant="outlined">
                {{ getTypeLabel(item?.type) }}
              </v-chip>
            </template>
            <template #item.is_holy_day_of_obligation="{ item }">
              <v-icon
                v-if="item?.is_holy_day_of_obligation"
                color="success"
                :icon="mdiCheckCircle"
              />
              <v-icon v-else color="grey" :icon="mdiMinusCircleOutline" />
            </template>
            <template #item.is_universal="{ item }">
              <v-chip v-if="item?.is_universal" color="primary" size="small">Universal</v-chip>
              <v-chip v-else color="secondary" size="small">Team</v-chip>
            </template>
            <template #item.liturgical_color="{ item }">
              <span
                v-if="item?.liturgical_color"
                class="tw:px-2 tw:py-1 tw:rounded tw:text-xs tw:font-medium tw:capitalize"
                :class="getColorClass(item?.liturgical_color)"
              >
                {{ item?.liturgical_color }}
              </span>
            </template>
            <template #item.actions="{ item }">
              <v-btn
                color="primary"
                :disabled="isDeleting"
                :icon="mdiPencilOutline"
                title="Edit Holiday"
                variant="text"
                @click="showModal(item)"
              ></v-btn>
              <v-btn
                color="error"
                :disabled="isDeleting || (item?.is_universal && !authUser?.is_admin)"
                :icon="mdiDeleteCircleOutline"
                title="Delete Holiday"
                variant="text"
                @click="deleteHoliday(item)"
              ></v-btn>
            </template>
          </DataTableServer>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
