<script setup>
  import { ref, watch } from 'vue';
  import { http } from '@/plugins';
  import { notify } from '@/utils';
  import { mdiDelete, mdiPencil } from '@mdi/js';
  import ConfirmationModal from '@/components/ConfirmationModal.vue';

  const props = defineProps({
    dataRole: { type: Object, required: true },
    permissionCategories: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
    canDelete: { type: Boolean, default: false },
  });

  const emit = defineEmits(['edit', 'deleted']);

  const role = ref(props.dataRole || {});
  const showDeleteConfirmation = ref(false);
  const isDeleting = ref(false);

  watch(
    () => props.dataRole,
    (value) => {
      role.value = value;
    },
  );

  const getSelectedCount = (category) => {
    if (!role.value?.permissions) return 0;
    const rolePermissionNames = role.value.permissions.map((p) => p.name);
    return category.permissions.filter((p) => rolePermissionNames.includes(p.name)).length;
  };

  const hasPermission = (permissionName) => {
    if (!role.value?.permissions) return false;
    return role.value.permissions.some((p) => p.name === permissionName);
  };

  const handleEdit = () => {
    emit('edit', role.value);
  };

  const confirmDelete = () => {
    showDeleteConfirmation.value = true;
  };

  const deleteRole = () => {
    isDeleting.value = true;
    http
      .delete(`/api/v1/roles/${role.value.id}`)
      .then(() => {
        notify.success('Role deleted successfully.');
        emit('deleted', role.value.id);
      })
      .catch(() => notify.error('There was an error deleting the role.'))
      .finally(() => {
        isDeleting.value = false;
        showDeleteConfirmation.value = false;
      });
  };
</script>

<template>
  <v-expansion-panel>
    <v-expansion-panel-title>
      <div class="tw:flex tw:items-center tw:justify-between tw:w-full tw:pr-4">
        <div class="tw:flex tw:items-center tw:gap-3">
          <span class="tw:font-medium">{{ role.name }}</span>
          <v-chip v-if="role.description" size="x-small" variant="tonal">
            {{ role.description }}
          </v-chip>
        </div>
        <div class="tw:flex tw:items-center tw:gap-2" @click.stop>
          <v-btn
            v-if="canEdit"
            color="primary"
            :icon="mdiPencil"
            size="x-small"
            variant="text"
            @click="handleEdit"
          />
          <v-btn
            v-if="canDelete"
            color="error"
            :icon="mdiDelete"
            size="x-small"
            variant="text"
            @click="confirmDelete"
          />
        </div>
      </div>
    </v-expansion-panel-title>

    <v-expansion-panel-text>
      <div class="tw:border-t tw:border-gray-200 tw:pt-4">
        <div v-if="permissionCategories.length === 0" class="tw:text-gray-500 tw:text-center">
          No permissions configured.
        </div>

        <div v-else class="tw:space-y-4">
          <div
            v-for="category in permissionCategories"
            :key="category.key"
            class="tw:border tw:rounded-lg tw:p-3"
          >
            <div class="tw:flex tw:items-center tw:justify-between tw:mb-2">
              <span class="tw:font-medium tw:text-gray-700">{{ category.label }}</span>
              <v-chip color="primary" size="x-small" variant="tonal">
                {{ getSelectedCount(category) }} / {{ category.permissions.length }}
              </v-chip>
            </div>

            <div class="tw:flex tw:flex-wrap tw:gap-2">
              <v-chip
                v-for="permission in category.permissions"
                :key="permission.name"
                :color="hasPermission(permission.name) ? 'success' : 'grey'"
                size="small"
                :variant="hasPermission(permission.name) ? 'flat' : 'outlined'"
              >
                {{ permission.name }}
              </v-chip>
            </div>
          </div>
        </div>
      </div>
    </v-expansion-panel-text>
  </v-expansion-panel>

  <ConfirmationModal
    v-model="showDeleteConfirmation"
    :loading="isDeleting"
    title="Delete Role"
    @confirm="deleteRole"
  >
    <p>
      Are you sure you want to delete the role
      <strong>{{ role.name }}</strong>
      ? This action cannot be undone.
    </p>
    <p class="tw:mt-2 tw:text-sm tw:text-gray-500">
      Users assigned to this role will lose all permissions associated with it.
    </p>
  </ConfirmationModal>
</template>
