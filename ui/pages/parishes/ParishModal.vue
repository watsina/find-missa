<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import { useRouter } from 'vue-router';

  const router = useRouter();

  const props = defineProps({
    parish: { type: Object, default: null },
    diocese: { type: Object, required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const form = useForm({
    name: props.parish?.name || '',
    diocese_id: props.diocese?.id,
    office_address: props.parish?.office_address || '',
    city: props.parish?.city || '',
    district: props.parish?.district || '',
    website_url: props.parish?.website_url || '',
    contact_email: props.parish?.contact_email || '',
  });

  watch(
    () => props.parish,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.parish?.name || '';
        form.office_address = props.parish?.office_address || '';
        form.contact_email = props.parish?.contact_email || '';
        form.office_address = props.parish?.office_address || '';
        form.city = props.parish?.city || '';
        form.district = props.parish?.district || '';
      }
    },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
    }
  });

  const isEditing = computed(() => !!props.parish?.id);

  const submit = () => {
    const url = isEditing.value ? `/api/v1/parishes/${props.parish?.id}` : `/api/v1/parishes`;

    const method = isEditing.value ? 'put' : 'post';

    form.diocese_id = props.diocese?.id;

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Parish was updated successfully'
          : 'Parish was added successfully.';
        notify.success(message);
        closeModal(data);
        if (!isEditing?.value) {
          router.push({ name: 'parishes.show', params: { parishId: data?.id } });
        }
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const syncCodeName = () => {
    if (!isEditing.value) return;

    form.post(`/api/v1/parishes/${props.parish?.id}/sync-codename`, {
      onSuccess: ({ data }) => {
        notify.success('Code name synced successfully');
        closeModal(data);
      },
      onError: () => notify.error('Failed to sync code name. Check for conflicts.'),
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
    <dialog-modal :show="showModal" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Parish' : 'New Parish' }} </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.name"
              label="Parish Name*"
              placeholder="Christ The King - Kampala"
              required
              type="text"
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.diocese_id"
              label="Diocese*"
              :model-value="diocese?.name"
              readonly
              required
              type="text"
              variant="solo"
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.city"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.city"
              label="City/Town*"
              placeholder="Kampala"
              required
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.district"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.district"
              label="District/State*"
              placeholder="Kampala"
              required
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
              v-model="form.contact_email"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.contact_email"
              label="Contact Email"
              placeholder="parish@email.com"
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
              placeholder="www.parish.com"
              required
              type="url"
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
          {{ isEditing ? 'Update Parish' : 'Add Parish' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
