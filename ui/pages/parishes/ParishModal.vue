<script setup>
  import { ref, computed, watch, onMounted } from 'vue';
  import { http, useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TextInput from '@/components/TextInput.vue';

  const props = defineProps({
    parish: { type: Object, default: null },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const dioceses = ref([]);

  onMounted(() => {
    http.get('/api/v1/diocese').then(({ data }) => {
      dioceses.value = data?.data;
    });
  });

  const form = useForm({
    name: props.parish?.name || '',
    diocese_id: props.parish?.diocese_id || '',
    office_address: props.parish?.office_address || '',
    city: props.parish?.city || '',
  });

  watch(
    () => props.parish,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.parish?.name || '';
        form.diocese_id = props.parish?.diocese_id || '';
        form.office_address = props.parish?.office_address || '';
        form.city = props.parish?.city || '';
      }
    },
  );

  watch(
    () => props.showModal,
    (newValue) => {
      if (!newValue) {
        form.reset();
        form.clearErrors();
      }
    },
  );

  const isEditing = computed(() => !!props.parish?.id);

  const submit = () => {
    const url = isEditing.value ? `/api/v1/parish/${props.parish?.id}` : `/api/v1/parish`;

    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Parish was updated successfully'
          : 'Parish was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    if (data && !isEditing.value) emit('created', data);
    if (data && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Parish' : 'New Parish' }} </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="Parish Name*"
              placeholder="Christ The King - Kampala"
              required
              type="text"
            />
          </div>

          <div class="tw-mt-4">
            <SelectInput
              v-model="form.diocese_id"
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.diocese_id"
              item-title="name"
              item-value="id"
              :items="dioceses"
              label="Diocese*"
              required
              type="text"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.office_address"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.office_address"
              label="Office Address"
              placeholder="Office Address"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.city"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.city"
              label="City"
              placeholder="Kampala"
              required
            />
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw-ml-3 tw-capitalize"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update Parish' : 'Add Parish' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
