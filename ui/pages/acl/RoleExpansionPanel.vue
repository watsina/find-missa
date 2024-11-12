<script setup>
  import { onMounted, ref, watch } from 'vue';
  import { http, useForm } from '@/plugins';
  import { notify } from '@/utils';

  const props = defineProps({
    dataRole: { type: Object, required: true },
  });

  const role = ref(props.dataRole || {});

  watch(
    () => props.dataRole,
    (value) => {
      role.value = value;
    },
  );

  const form = useForm({
    permission_ids: role.value?.permisisons || [],
  });

  const roles = ref([]);
  const isFetching = ref(false);

  onMounted(() => {
    fetchRoles();
  });

  const fetchRoles = () => {
    isFetching.value = true;
    http
      .get('/api/v1/roles')
      .then(({ data }) => {
        roles.value = data?.data || [];
      })
      .catch(() => notify.error('Something went wrong while fetching roles.'))
      .finally(() => (isFetching.value = false));
  };

  const updateRole = () => {
    form.post(`/api/v1/roles/${role.value.id}`, {
      onSuccess: () => {
        notify.success('Role updated successfully.');
      },
    });
  };
</script>

<template>
  <v-expansion-panel :title="role.name"></v-expansion-panel>
</template>
