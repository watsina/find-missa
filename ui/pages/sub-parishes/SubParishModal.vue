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
    subParish: { type: Object, default: null },
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
    name: props.subParish?.name || '',
    parish_id: props.subParish?.parish_id || '',
    office_address: props.subParish?.office_address || '',
    district: props.subParish?.district || '',
  });

  watch(
    () => props.subParish,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.subParish?.name || '';
        form.parish_id = props.subParish?.parish_id || '';
        form.office_address = props.subParish?.office_address || '';
        form.district = props.subParish?.district || '';
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

  const isEditing = computed(() => !!props.subParish?.id);

  const submit = () => {
    const url = isEditing.value
      ? `/api/v1/sub-parish/${props.subParish?.id}`
      : `/api/v1/sub-parish`;

    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Sub Parish was updated successfully'
          : 'Sub Parish was added successfully.';
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
      <template #title> {{ isEditing ? 'Update Sub Parish' : 'New Sub Parish' }} </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="Sub Parish Name*"
              placeholder="Kanja Sub Parish"
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
          {{ isEditing ? 'Update Sub Parish' : 'Add Sub Parish' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
