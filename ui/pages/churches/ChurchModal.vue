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
  const subParishes = ref([]);

  const churchTypes = [
    { title: 'Parish', value: 'parish' },
    { title: 'SubParish', value: 'sub-parish' },
    { title: 'Basilica', value: 'basilica' },
    { title: 'Minor Basilica', value: 'minor-basilica' },
  ];

  onMounted(() => {
    // fetch parishes
    http.get('/api/v1/parish').then(({ data }) => {
      parishes.value = data?.data;
    });
    // fetch subparishes
    http.get('/api/v1/sub-parish').then(({ data }) => {
      subParishes.value = data?.data;
    });
  });

  const parishable = ref([]);

  const form = useForm({
    name: props.church?.name || '',
    type: props.church?.type || '',
    parishable_id: props.church?.parishable_id || '',
    latitude: props.church?.latitude || '',
    longitude: props.church?.longitude || '',
    contact_email: props.church?.contact_email || '',
    contact_phone: props.church?.contact_phone || '',
    street_address: props.church?.street_address || '',
    postal_address: props.church?.postal_address || '',
  });

  watch(
    () => form.type,
    (value) => {
      if (value === 'sub-parish') {
        parishable.value = subParishes.value;
      } else {
        parishable.value = parishes.value;
      }
    },
  );

  watch(
    () => props.church,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.church?.name || '';
        form.type = props.church?.type || '';
        form.latitude = props.church?.latitude || '';
        form.longitude = props.church?.longitude || '';
        form.contact_email = props.church?.contact_email || '';
        form.contact_phone = props.church?.contact_phone || '';
        form.street_address = props.church?.street_address || '';
        form.parishable_id = props.church?.parishable_id || '';
        form.postal_address = props.church?.postal_address || '';
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
    const url = isEditing.value ? `/api/v1/church/${props.church?.id}` : `/api/v1/church`;

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
            <SelectInput
              v-model="form.parishable_id"
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.parishable_id"
              hint="Name of parish/subParish that the church belongs to."
              item-title="name"
              item-value="id"
              :items="parishable"
              label="Parish*"
              required
              type="text"
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
              label="Plot 2, Kyadondo Road"
              placeholder="Street Address"
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
          {{ isEditing ? 'Update Church' : 'Add Church' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
