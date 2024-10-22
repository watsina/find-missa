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
    priest: { type: Object, default: null },
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
    name: props.priest?.name || '',
    parish_id: props.priest?.parish_id || '',
    email: props.priest?.email || '',
    phone: props.priest?.phone || '',
  });

  watch(
    () => props.priest,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.priest?.name || '';
        form.parish_id = props.priest?.parish_id || '';
        form.email = props.priest?.email || '';
        form.phone = props.priest?.phone || '';
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

  const isEditing = computed(() => !!props.priest?.id);

  const submit = () => {
    const url = isEditing.value ? `/api/v1/priest/${props.priest?.id}` : `/api/v1/priest`;

    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Priest was updated successfully'
          : 'Priest was added successfully.';
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
      <template #title> {{ isEditing ? 'Update Priest' : 'New Priest' }} </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="Priest Name*"
              placeholder="Fr Priest"
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
              v-model="form.email"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.email"
              label="Email"
              placeholder="priest@email.com"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.phone"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.phone"
              label="Phone Number"
              placeholder="+256 777 777777"
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
          {{ isEditing ? 'Update Priest' : 'Add Priest' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
