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
    church: { type: Object, default: null },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const parishes = ref([]);

  onMounted(() => {
    // fetch parishes
    http.get('/api/v1/parish').then(({ data }) => {
      parishes.value = data?.data;
    });
  });

  const form = useForm({
    name: props.church?.name || '',
    type: props.church?.type || '',
    latitude: props.church?.latitude || '',
    longitude: props.church?.longitude || '',
    contact_email: props.church?.contact_email || '',
    street_address: props.church?.street_address || '',
    parish_id: props.church?.parish_id || '',
    postal_address: props.church?.postal_address || '',
    contact_phone: props.church?.contact_phone || '',
    parish_priest_id: props.church?.parish_priest_id || '',
  });

  watch(
    () => props.church,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.church?.name || '';
        form.parish_id = props.church?.parish_id || '';
        form.office_address = props.church?.office_address || '';
        form.district = props.church?.district || '';
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

  const isEditing = computed(() => !!props.church?.id);

  const submit = () => {
    const url = isEditing.value ? `/api/v1/sub-parish/${props.church?.id}` : `/api/v1/sub-parish`;

    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Church was updated successfully'
          : 'Church was added successfully.';
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
      <template #title> {{ isEditing ? 'Update Church' : 'New Church' }} </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="Church Name*"
              placeholder="Kanja Church"
              required
              type="text"
            />
          </div>

          <div class="tw-mt-4">
            <SelectInput
              v-model="form.parish_id"
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.diocese_id"
              item-title="name"
              item-value="id"
              :items="parishes"
              label="Parish*"
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
              v-model="form.district"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.district"
              label="District"
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
          {{ isEditing ? 'Update Church' : 'Add Church' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
