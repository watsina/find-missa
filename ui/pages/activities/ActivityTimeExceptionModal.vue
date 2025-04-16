<script setup>
  import { computed, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useDate } from 'vuetify';

  const props = defineProps({
    exception: { type: Object, default: null },
    activityId: { type: [String, Number], required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const form = useForm({
    activity_time_id: props.activityId,
    reason: props.exception?.reason || '',
    start_date: props.exception?.start_date || null,
    end_date: props.exception?.end_date || null,
  });
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const start_date = ref(parser.date(props.exception?.start_date || currentDate));
  const end_date = ref(parser.date(props.exception?.end_date || currentDate));

  watch(
    () => props.exception,
    (newValue) => {
      if (newValue?.id) {
        form.reason = newValue?.reason || '';
        form.start_date = newValue?.start_date || null;
        form.end_date = newValue?.end_date || null;
        start_date.value = parser.date(newValue?.start_date || currentDate);
        end_date.value = parser.date(newValue?.end_date || currentDate);
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

  const isEditing = computed(() => !!props.exception?.id);

  const submit = () => {
    const url = isEditing.value
      ? `/api/v1/activity-time-exceptions/${props.exception?.id}`
      : `/api/v1/activity-time-exceptions`;

    const method = isEditing.value ? 'put' : 'post';

    form.activity_time_id = props.activityId;
    form.start_date = useDayjs(start_date.value).format('YYYY-MM-DD');
    form.end_date = useDayjs(end_date.value).format('YYYY-MM-DD');

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Activity Exception was updated successfully'
          : 'Activity Exception was added successfully.';
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
      <template #title>
        {{ isEditing ? 'Update Activity Exception' : 'New Activity Exception' }}
      </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.reason"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.reason"
              label="Reason*"
              placeholder="Public Holiday"
              required
            />
          </div>

          <div class="tw-mt-4">
            <DateInput
              v-model="start_date"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.start_date"
              label="Start Date"
              landscape
              required
            />
          </div>

          <div class="tw-mt-4">
            <DateInput
              v-model="end_date"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.end_date"
              label="End Date"
              landscape
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
          {{ isEditing ? 'Update Activity Exception' : 'Add Activity Exception' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
