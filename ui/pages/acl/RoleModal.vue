<script setup>
  import { computed, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import { mdiChevronDown, mdiChevronUp } from '@mdi/js';

  const props = defineProps({
    role: { type: Object, default: null },
    permissionCategories: { type: Array, default: () => [] },
  });

  const showModal = defineModel({ type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const expandedPanels = ref([]);

  const form = useForm({
    name: props.role?.name || '',
    description: props.role?.description || '',
    permission_ids: props.role?.permissions?.map((p) => p.id) || [],
  });

  watch(
    () => props.role,
    (newValue) => {
      if (newValue?.id) {
        form.name = newValue?.name || '';
        form.description = newValue?.description || '';
        form.permission_ids = newValue?.permissions?.map((p) => p.id) || [];
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
      expandedPanels.value = [];
    }
  });

  const isEditing = computed(() => !!props.role?.id);

  const isCategoryFullySelected = (category) => {
    if (!category.permissions?.length) return false;
    return category.permissions.every((p) => form.permission_ids.includes(p.id));
  };

  const isCategoryPartiallySelected = (category) => {
    if (!category.permissions?.length) return false;
    const selected = category.permissions.filter((p) => form.permission_ids.includes(p.id));
    return selected.length > 0 && selected.length < category.permissions.length;
  };

  const toggleCategoryPermissions = (category) => {
    const permissionIds = category.permissions.map((p) => p.id);

    if (isCategoryFullySelected(category)) {
      form.permission_ids = form.permission_ids.filter((id) => !permissionIds.includes(id));
    } else {
      const newIds = permissionIds.filter((id) => !form.permission_ids.includes(id));
      form.permission_ids = [...form.permission_ids, ...newIds];
    }
  };

  const submit = () => {
    const url = isEditing.value ? `/api/v1/roles/${props.role?.id}` : `/api/v1/roles`;
    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Role was updated successfully'
          : 'Role was created successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the role.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    if (data?.id && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);
    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="700" :show="showModal" @close="closeModal">
      <template #title>{{ isEditing ? 'Update Role' : 'New Role' }}</template>

      <template #content>
        <form>
          <div class="tw:grid tw:grid-cols-1 tw:gap-4">
            <div>
              <TextInput
                v-model="form.name"
                class="tw:block tw:w-full"
                :error="form.errors.name"
                label="Name*"
                placeholder="e.g. Content Editor"
                required
              />
            </div>

            <div>
              <TextInput
                v-model="form.description"
                class="tw:block tw:w-full"
                :error="form.errors.description"
                label="Description"
                placeholder="Brief description of this role"
              />
            </div>

            <div v-if="permissionCategories.length > 0">
              <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
                Permissions
              </label>

              <v-expansion-panels v-model="expandedPanels" multiple variant="accordion">
                <v-expansion-panel
                  v-for="category in permissionCategories"
                  :key="category.key"
                  :value="category.key"
                >
                  <v-expansion-panel-title>
                    <div class="tw:flex tw:items-center tw:gap-3">
                      <v-checkbox
                        color="primary"
                        density="compact"
                        hide-details
                        :indeterminate="isCategoryPartiallySelected(category)"
                        :model-value="isCategoryFullySelected(category)"
                        @click.stop
                        @update:model-value="toggleCategoryPermissions(category)"
                      />
                      <span class="tw:font-medium">{{ category.label }}</span>
                      <v-chip class="tw:ml-2" size="x-small" variant="tonal">
                        {{
                          category.permissions.filter((p) => form.permission_ids.includes(p.id))
                            .length
                        }}
                        / {{ category.permissions.length }}
                      </v-chip>
                    </div>
                    <template #actions="{ expanded }">
                      <v-icon :icon="expanded ? mdiChevronUp : mdiChevronDown" />
                    </template>
                  </v-expansion-panel-title>

                  <v-expansion-panel-text>
                    <div class="tw:space-y-1">
                      <div
                        v-for="permission in category.permissions"
                        :key="permission.id"
                        class="tw:flex tw:items-center tw:py-1"
                      >
                        <v-checkbox
                          v-model="form.permission_ids"
                          color="primary"
                          density="compact"
                          hide-details
                          :value="permission.id"
                        >
                          <template #label>
                            <div>
                              <span class="tw:font-medium">{{ permission.name }}</span>
                              <span
                                v-if="permission.description"
                                class="tw:text-gray-500 tw:text-sm tw:ml-2"
                              >
                                - {{ permission.description }}
                              </span>
                            </div>
                          </template>
                        </v-checkbox>
                      </div>
                    </div>
                  </v-expansion-panel-text>
                </v-expansion-panel>
              </v-expansion-panels>
            </div>

            <div v-else class="tw:text-center tw:py-4 tw:text-gray-500 tw:bg-gray-50 tw:rounded-lg">
              No permissions available. Please run the ACL seeder.
            </div>
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update Role' : 'Create Role' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
