<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TextInput from '@/components/TextInput.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import DateInput from '@/components/DateInput.vue';

  const props = defineProps({
    activity: { type: Object, default: null },
    church: { type: Object, required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const activityTypes = [
    { title: 'Mass', value: 'mass' },
    { title: 'Confession', value: 'confession' },
    { title: 'Adoration', value: 'adoration' },
  ];

  const occurrences = [
    { title: 'Weekly', value: 'weekly' },
    { title: 'Daily', value: 'daily' },
    { title: 'One Time', value: 'one-time' },
  ];

  const form = useForm({
    type: props.activity?.type || '',
    start_time: props.activity?.start_time || '',
    end_time: props.activity?.end_time || '',
    language: props.activity?.language || '',
    occurrence: props.activity?.occurrence || '',
    date: props.activity?.date || null,
    day_of_week: props.activity?.day_of_week || '',
    church_id: props.church?.id || '',
  });

  watch(
    () => props.activity,
    (newValue) => {
      if (newValue?.id) {
        form.type = newValue?.type || '';
        form.start_time = newValue?.start_time || '';
        form.end_time = newValue?.end_time || '';
        form.language = newValue?.language || '';
        form.occurrence = newValue?.occurrence || '';
        form.date = newValue?.date || null;
        form.day_of_week = newValue?.day_of_week || '';
        form.church_id = newValue?.church_id || '';
      }
    },
    { deep: true },
  );

  watch(
    () => props.showModal,
    (newValue) => {
      if (!newValue) {
        form.reset();
        form.clearErrors();
      }
    },
    { deep: true },
  );

  const isEditing = computed(() => !!props.activity?.id);

  const submit = () => {
    if (!props.church?.id) {
      return notify.error('Church details not provided');
    }

    const url = isEditing.value
      ? `/api/v1/activity-time/${props.activity?.id}`
      : `/api/v1/activity-time`;

    const method = isEditing.value ? 'put' : 'post';

    // some clean up to ensure that the data matches what we expect
    if (form.occurrence === 'weekly') {
      form.date = null;
    } else if (form.occurrence === 'daily') {
      form.day_of_week = '';
      form.date = null;
    } else if (form.occurrence === 'one-time') {
      form.day_of_week = '';
    }

    form.church_id = props.church.id;

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Activity was updated successfully'
          : 'Activity was added successfully.';
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
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Activity Time' : 'New Activity Time' }} </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <v-text-field
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.church_id"
              label="Church*"
              :model-value="`${church.name} - ${church.city}`"
              readonly
              variant="solo"
            />
          </div>

          <div class="tw-mt-4">
            <SelectInput
              v-model="form.type"
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.type"
              :items="activityTypes"
              label="Activity Type*"
              required
            />
          </div>

          <div class="tw-mt-4">
            <SelectInput
              v-model="form.occurrence"
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.occurrence"
              :items="occurrences"
              label="occurrence*"
              required
            />
          </div>

          <div v-if="form.occurrence === 'one-time'" class="tw-mt-4">
            <DateInput
              v-model="form.date"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.date"
              label="Date"
              landscape
              required
            />
          </div>

          <div v-if="form.occurrence === 'weekly'" class="tw-mt-4">
            <TextInput
              v-model="form.day_of_week"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.day_of_week"
              label="Day of Week"
              placeholder="Sunday"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TimeInput
              v-model="form.start_time"
              :error="form.errors.start_time"
              label="Start Time"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TimeInput
              v-model="form.end_time"
              :error="form.errors.end_time"
              label="End Time"
              required
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.language"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.language"
              label="Language"
              placeholder="Ruhororo"
              required
              type="text"
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
          {{ isEditing ? 'Update Activity Time' : 'Add Activity Time' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
