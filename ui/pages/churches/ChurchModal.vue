<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TextInput from '@/components/TextInput.vue';

  const props = defineProps({
    church: { type: Object, default: null },
    parish: { type: Object, required: true },
    parishType: {
      required: true,
      validator(value, props) {
        return ['parish', 'sub-parish'].includes(value);
      },
    },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const churchTypes = computed(() => {
    if (props.parishType === 'sub-parish') {
      return [{ title: 'SubParish', value: 'sub-parish' }];
    }
    return [
      { title: 'Parish Church', value: 'parish' },
      { title: 'Chapel', value: 'chapel' },
      { title: 'Cathedral', value: 'cathedral' },
      { title: 'Basilica', value: 'basilica' },
      { title: 'Minor Basilica', value: 'minor-basilica' },
    ];
  });

  const form = useForm({
    name: props.church?.name || '',
    type: props.church?.type || '',
    parishable_id: props.parish?.id,
    latitude: props.church?.latitude || '',
    longitude: props.church?.longitude || '',
    contact_email: props.church?.contact_email || '',
    website_url: props.church?.website_url || '',
    google_maps_link: props.church?.google_maps_link || '',
    contact_phone: props.church?.contact_phone || '',
    street_address: props.church?.street_address || '',
    city: props.church?.city || props.parish?.city || '',
    district: props.church?.district || props.parish?.district || '',
    postal_address: props.church?.postal_address || '',
  });

  watch(
    () => props.church,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.church?.name || '';
        form.type = props.church?.type || '';
        form.latitude = props.church?.latitude || '';
        form.longitude = props.church?.longitude || '';
        form.contact_email = props.church?.contact_email || '';
        form.website_url = props.church?.website_url || '';
        form.google_maps_link = props.church?.google_maps_link || '';
        form.contact_phone = props.church?.contact_phone || '';
        form.street_address = props.church?.street_address || '';
        form.city = props.church?.city || '';
        form.district = props.church?.district || '';
        form.postal_address = props.church?.postal_address || '';
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
    }
  });

  const isEditing = computed(() => !!props.church?.id);

  const submit = () => {
    const url = isEditing.value ? `/api/v1/churches/${props.church?.id}` : `/api/v1/churches`;

    const method = isEditing.value ? 'put' : 'post';

    form.type = form.type?.toLowerCase();
    form.parishable_id = props.parish?.id;

    return form[method](url, {
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
    if (data?.id && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal height="600" max-width="500" scrollable :show="showModal" @close="closeModal">
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
              v-model="form.type"
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.type"
              :items="churchTypes"
              label="Type*"
              required
              type="text"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.parishable_id"
              hint="Name of parish/subParish that the church belongs to."
              :label="parishType === 'sub-parish' ? 'SubParish*' : 'Parish*'"
              :model-value="`${parish?.name} - ${parish?.city || ''}`"
              readonly
              type="text"
              variant="solo"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.city"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.city"
              label="City/Town*"
              placeholder="Kampala"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.district"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.district"
              label="District/State*"
              placeholder="Kampala"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.google_maps_link"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.google_maps_link"
              label="Google Maps Link*"
              required
              type="url"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.website_url"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.website_url"
              label="Website Url"
              placeholder="www.church.com"
              required
              type="url"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.latitude"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.latitude"
              label="Latitude"
              placeholder="43.88"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.longitude"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.longitude"
              label="Longitude"
              placeholder="93.78"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.contact_email"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.contact_email"
              label="Contact Email"
              placeholder="church@email.com"
              required
              type="email"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.contact_phone"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.contact_phone"
              label="Contact Phone"
              placeholder="+256 700 000000"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.street_address"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.street_address"
              label="Street Address"
              placeholder="Plot 2, Kyadondo Road"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.postal_address"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.postal_address"
              label="Postal Address"
              placeholder="PO BOX 123 Kampala"
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
