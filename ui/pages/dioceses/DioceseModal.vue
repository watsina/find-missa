<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import { useRouter } from 'vue-router';
  import SelectInput from '@/components/SelectInput.vue';
  const router = useRouter();

  const props = defineProps({
    diocese: { type: Object, default: null },
  });

  const showModal = defineModel('showModal', { type: Boolean });

  const emit = defineEmits(['created', 'updated']);

  const form = useForm({
    name: props.diocese?.name || '',
    type: props.diocese?.type || '',
    office_address: props.diocese?.office_address || '',
    postal_address: props.diocese?.postal_address || '',
    website_url: props.diocese?.website_url || '',
    contact_email: props.diocese?.contact_email || '',
  });

  const dioceseTypes = [
    { title: 'Diocese', value: 'diocese' },
    { title: 'ArchDiocese', value: 'archdiocese' },
  ];

  watch(
    () => props.diocese,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.diocese?.name || '';
        form.type = props.diocese?.type || '';
        form.website_url = props.diocese?.website_url || '';
        form.contact_email = props.diocese?.contact_email || '';
        form.office_address = props.diocese?.office_address || '';
        form.postal_address = props.diocese?.postal_address || '';
      }
    },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
    }
  });

  const isEditing = computed(() => !!props.diocese?.id);

  const submit = () => {
    const url = isEditing.value ? `/api/v1/dioceses/${props.diocese?.id}` : `/api/v1/dioceses`;

    const method = isEditing.value ? 'put' : 'post';

    form.type = form.type?.toLowerCase();

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Diocese was updated successfully'
          : 'Diocese was added successfully.';
        notify.success(message);
        closeModal(data);
        if (!isEditing?.value) {
          router.push({ name: 'dioceses.show', params: { dioceseId: data?.code_name } });
        }
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const syncCodeName = () => {
    if (!isEditing.value) return;

    form.post(`/api/v1/dioceses/${props.diocese?.id}/sync-codename`, {
      onSuccess: ({ data }) => {
        notify.success('Code name synced successfully');
        closeModal(data);
      },
      onError: () => notify.error('Failed to sync code name. Check for conflicts.'),
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
    <dialog-modal :show="showModal" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Diocese' : 'New Diocese' }} </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <TextInput
              v-model="form.name"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.name"
              label="Diocese Name*"
              placeholder="Kabale Diocese"
              required
              type="text"
            />
          </div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.type"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.type"
              :items="dioceseTypes"
              label="Type*"
              placeholder="diocese"
              required
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.contact_email"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.contact_email"
              label="Contact Email"
              placeholder="diocese@email.com"
              required
              type="email"
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.website_url"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.website_url"
              label="Website Url"
              placeholder="www.diocese.com"
              required
              type="url"
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.office_address"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.office_address"
              label="Office Address"
              placeholder="Office Address"
              required
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.postal_address"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.postal_address"
              label="Postal Address"
              placeholder="Postal Address"
              required
            />
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <SecondaryButton
          v-if="isEditing"
          class="tw:ml-3"
          :disabled="form.processing"
          :loading="form.processing"
          @click="syncCodeName"
        >
          Sync Code Name
        </SecondaryButton>

        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
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
