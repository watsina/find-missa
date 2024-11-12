<script setup>
  import { ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DataTable from '@/components/DataTable.vue';

  const props = defineProps({
    dataRole: { type: Array, required: true },
    permissions: { type: Array, required: true },
  });

  const role = ref(props.dataRole || {});

  const headers = [
    { title: 'Name', key: 'name' },
    { title: 'Description', key: 'description' },
    { title: '', key: 'actions' },
  ];

  watch(
    () => props.dataRole,
    (value) => {
      role.value = value;
    },
  );

  const form = useForm({
    permission_ids: role.value?.permissions?.map((permission) => permission?.id) || [],
  });

  const updateRole = () => {
    form.put(`/api/v1/roles/${role.value.id}`, {
      onSuccess: () => {
        notify.success('Role updated successfully.');
      },
      onError: () => notify.error('There was an error updating the role.'),
    });
  };
</script>

<template>
  <v-expansion-panel>
    <v-expansion-panel-title>
      {{ role.name }}
    </v-expansion-panel-title>
    <v-expansion-panel-text>
      <div class="tw-border-t tw-border-gray-200"></div>
      <data-table :headers="headers" :items="permissions">
        <template #item.actions="{ item }">
          <v-checkbox-btn v-model="form.permission_ids" :value="item.id"></v-checkbox-btn>
        </template>
      </data-table>
      <div class="tw-flex tw-justify-self-end">
        <v-btn
          class="tw-flex tw-justify-self-end"
          color="primary"
          :disabled="form.processing"
          :loading="form.processing"
          size="small"
          variant="outlined"
          @click="updateRole"
        >
          Save
        </v-btn>
      </div>
    </v-expansion-panel-text>
  </v-expansion-panel>
</template>
