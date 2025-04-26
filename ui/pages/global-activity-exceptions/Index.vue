<script setup>
  import { mdiDeleteCircleOutline, mdiPencilOutline } from '@mdi/js';
  import DataTableServer from '@/components/DataTableServer';
  import AppLayout from '@/layouts/AppLayout';
  import GlobalExceptionModal from './Modal.vue';
  import { ref } from 'vue';
  import { http, notify } from '@/plugins';

  const showingModal = ref(false);
  const isDeleting = ref(false);
  const exceptionToEdit = ref(null);
  const exceptions = ref([]);

  function showModal(exception = null) {
    exceptionToEdit.value = exception;

    showingModal.value = true;
  }

  const headers = [
    { title: 'Reason', value: 'reason' },
    { title: 'Message', value: 'message' },
    { title: 'Start Date', value: 'start_date' },
    { title: 'End Date', value: 'end_date' },
    { title: '', value: 'actions' },
  ];

  function updateExceptions(updated) {
    exceptions.value = exceptions.value?.map((exception) => {
      if (exception?.id === updated?.id) {
        return updated;
      }
      return exception;
    });
  }

  async function deleteException(exception) {
    if (!exception?.id) {
      return;
    }

    if (!(await notify.confirm('This action is not reversible.'))) {
      return;
    }

    isDeleting.value = true;

    http
      .delete(`/api/v1/global-activity-time-exceptions/${exception.id}`)
      .then(() => {
        notify.success('Exception deleted successfully.');
        exceptions.value = exceptions.value?.filter((exc) => exception?.id !== exc?.id);
      })
      .catch(() => notify.error('There was an error deleting the exception.'))
      .finally(() => (isDeleting.value = false));
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">Activities</h2>
    </template>
    <v-container>
      <v-row class="tw:py-12">
        <v-col class="tw:flex" cols="12">
          <global-exception-modal
            v-model="showingModal"
            :exception="exceptionToEdit"
            @created="exceptions.push($event)"
            @updated="updateExceptions"
          />
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">
            A list of all the global activity time exceptions.
          </p>
          <v-spacer></v-spacer>
          <v-btn color="primary" :disabled="isDeleting" @click="showModal"
            >Add Global Exception</v-btn
          >
        </v-col>
        <v-col cols="12">
          <DataTableServer
            v-model:items="exceptions"
            :headers="headers"
            url="/api/v1/global-activity-time-exceptions"
          >
            <template #item.actions="{ item }">
              <v-btn
                color="primary"
                :disabled="isDeleting"
                :icon="mdiPencilOutline"
                title="Edit Global Exception"
                variant="text"
                @click="showModal(item)"
              ></v-btn>
              <v-btn
                color="error"
                :disabled="isDeleting"
                :icon="mdiDeleteCircleOutline"
                title="Delete Global Exception"
                variant="text"
                @click="deleteException(item)"
              ></v-btn>
            </template>
          </DataTableServer>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
