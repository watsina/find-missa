<script setup>
  import { computed, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TextInput from '@/components/TextInput.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useDate } from 'vuetify/lib/framework.mjs';

  const props = defineProps({
    activity: { type: Object, default: null },
    church: { type: Object, required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const date = ref(parser.date(currentDate));

  const activityTypes = [
    { title: 'Mass', value: 'mass' },
    { title: 'Confession', value: 'confession' },
    { title: 'Adoration', value: 'adoration' },
  ];

  const activityTypeDescriptions = [
    { title: 'Mass', value: 'mass' },
    { title: 'Confession', value: 'confession' },
    { title: 'Adoration', value: 'adoration' },
    { title: 'Benediction', value: 'benediction' },
    { title: 'Exposition', value: 'exposition' },
    { title: 'Traditional Latin Mass', value: 'traditional-latin-mass' },
    { title: 'Anticipation Mass', value: 'anticipation-mass' },
    { title: 'Mass of the Lords Supper', value: 'mass-of-the-lords-supper' },
    { title: 'Good Friday Service', value: 'good-friday-service' },
    { title: 'Easter Vigil Mass', value: 'easter-vigil-mass' },
    { title: 'Easter Mass', value: 'easter-mass' },
    { title: 'Easter Monday Mass', value: 'easter-monday-mass' },
    { title: 'Christmas Vigil Mass', value: 'christmas-vigil-mass' },
    { title: 'Christmas Mass', value: 'christmas-mass' },
  ];

  const form = useForm({
    type: props.activity?.type || '',
    activity_type_description: props.activity?.activity_type_description || '',
    start_time: props.activity?.start_time || '',
    end_time: props.activity?.end_time || '',
    language: props.activity?.language || '',
    date: props.activity?.date || null,
    church_id: props.church?.id || '',
  });

  watch(
    () => props.activity,
    (newValue) => {
      if (newValue?.id) {
        form.type = newValue?.type || '';
        form.activity_type_description = newValue?.activity_type_description || '';
        form.start_time = newValue?.start_time || '';
        form.end_time = newValue?.end_time || '';
        form.language = newValue?.language || '';
        form.date = newValue?.date || null;
        form.church_id = newValue?.church_id || '';

        date.value = parser.date(newValue?.date || currentDate);
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.clearErrors();
    }
  });

  const isEditing = computed(() => !!props.activity?.id);

  const submit = () => {
    if (!props.church?.id) {
      return notify.error('Church details not provided');
    }

    const url = isEditing.value
      ? `/api/v1/one-time-activities/${props.activity?.id}`
      : `/api/v1/one-time-activities`;

    const method = isEditing.value ? 'put' : 'post';

    form.date = useDayjs(date.value).format('YYYY-MM-DD');
    form.church_id = props.church.id;

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Activity was updated successfully'
          : 'Activity was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the activity.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    if ((data?.id || data?.length) && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title>
        {{ isEditing ? 'Update One-Time Activity' : 'New One-Time Activity' }}
      </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <v-text-field
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.church_id"
              label="Church*"
              :model-value="`${church?.name} - ${church?.city}`"
              readonly
              variant="solo"
            />
          </div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.type"
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.type"
              :items="activityTypes"
              label="Activity Type*"
              required
            />
          </div>

          <div class="tw:mt-4">
            <v-autocomplete
              v-model="form.activity_type_description"
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error-messages="form.errors.activity_type_description"
              :items="activityTypeDescriptions"
              label="Activity Type Description*"
              required
            />
          </div>

          <div class="tw:mt-4">
            <DateInput
              v-model="date"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.date"
              label="Date"
              landscape
              required
            />
          </div>

          <div class="tw:mt-4">
            <TimeInput
              v-model="form.start_time"
              :error="form.errors.start_time"
              label="Start Time"
              required
            />
          </div>

          <div class="tw:mt-4">
            <TimeInput
              v-model="form.end_time"
              :error="form.errors.end_time"
              label="End Time (Optional)"
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.language"
              autofocus
              class="tw:block tw:w-full tw:mt-1"
              density="default"
              :error="form.errors.language"
              label="Language"
              placeholder="English"
              required
            />
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update Activity' : 'Add Activity' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
