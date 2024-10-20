<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const props = defineProps({
    modalValue: Boolean,
    diocese: { type: Object, default: null },
  });

  const emit = defineEmits(['update:modalValue']);

  const form = useForm({
    name: props.diocese?.name || '',
    type: props.diocese?.type || '',
    office_address: props.diocese?.office_address || '',
    postal_address: props.diocese?.postal_address || '',
    country: 'Uganda',
  });

  watch(
    () => props.diocese,
    (newValue) => {
      if (newValue?.id) {
        form.first_name = newValue?.first_name;
        form.last_name = newValue?.last_name;
        form.email = newValue?.email;
        form.phone_number = newValue?.phone_number;
        form.unique_identifier = newValue?.unique_identifier;
        form.category_id = newValue?.category?.id;
        form.birth_date = newValue?.birth_date;
      }
    },
  );

  watch(
    () => props.modalValue,
    (newValue) => {
      if (!newValue) {
        form.reset();
        form.clearErrors();
      }
    },
  );

  const isEditing = computed(() => !!props.diocese?.id);

  const submit = () => {
    const url = isEditing.value ? `api/v1/diocese/${props.diocese?.id}` : `api/v1/diocese`;

    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: ({ data }) => {
        console.info({ data });
        notify.success('Diocese added successfully.');
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    if (data) emit('created', data);
    emit('update:modalValue', false);
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="modalValue" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Diocese' : 'New Diocese' }} </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="Diocese Name*"
              placeholder="Kabale Diocese"
              required
              type="text"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.type"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.type"
              label="Type*"
              placeholder="diocese"
              hint="diocese/archdiocese"
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
              v-model="form.postal_address"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.postal_address"
              label="Postal Address"
              placeholder="Postal Address"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.country"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.country"
              label="Country"
              placeholder="Country"
              required
              disabled
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
          {{ isEditing ? 'Update diocese' : 'Add diocese' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
