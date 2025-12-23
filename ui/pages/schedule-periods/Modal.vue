<script setup>
  import { computed, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import TextareaInput from '@/components/TextareaInput.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useDate } from 'vuetify';
  import useAuthStore from '@/stores/useAuthStore';

  const props = defineProps({
    period: { type: Object, default: null },
  });

  const authStore = useAuthStore();
  const authUser = computed(() => authStore.user);

  const showModal = defineModel({ type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const startDate = ref(parser.date(props.period?.start_date || currentDate));
  const endDate = ref(parser.date(props.period?.end_date || currentDate));

  const form = useForm({
    name: props.period?.name || '',
    slug: props.period?.slug || '',
    description: props.period?.description || '',
    start_date: props.period?.start_date || null,
    end_date: props.period?.end_date || null,
    is_active: props.period?.is_active ?? true,
    is_recurring: props.period?.is_recurring || false,
  });

  watch(
    () => props.period,
    (newValue) => {
      if (newValue?.id) {
        form.name = newValue?.name || '';
        form.slug = newValue?.slug || '';
        form.description = newValue?.description || '';
        form.start_date = newValue?.start_date || null;
        form.end_date = newValue?.end_date || null;
        form.is_active = newValue?.is_active ?? true;
        form.is_recurring = newValue?.is_recurring || false;
        startDate.value = parser.date(newValue?.start_date || currentDate);
        endDate.value = parser.date(newValue?.end_date || currentDate);
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
      startDate.value = parser.date(currentDate);
      endDate.value = parser.date(currentDate);
    }
  });

  const isEditing = computed(() => !!props.period?.id);
  const canEditUniversal = computed(() => authUser.value?.is_admin);
  const isUniversalPeriod = computed(() => !props.period?.team_id);

  // Auto-generate slug from name
  watch(
    () => form.name,
    (newValue) => {
      if (!isEditing.value && newValue) {
        form.slug = newValue
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/(^-|-$)/g, '');
      }
    },
  );

  const submit = () => {
    form.start_date = useDayjs(startDate.value).format('YYYY-MM-DD');
    form.end_date = useDayjs(endDate.value).format('YYYY-MM-DD');

    const url = isEditing.value
      ? `/api/v1/schedule-periods/${props.period?.id}`
      : `/api/v1/schedule-periods`;
    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Schedule period was updated successfully'
          : 'Schedule period was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the schedule period.'),
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
      <template #title>{{ isEditing ? 'Update Schedule Period' : 'New Schedule Period' }}</template>

      <template #content>
        <v-alert
          v-if="isEditing && isUniversalPeriod && !canEditUniversal"
          class="tw:mb-4"
          color="warning"
          type="warning"
        >
          Only super admins can modify universal schedule periods.
        </v-alert>

        <form>
          <div class="tw:mt-4">
            <TextInput
              v-model="form.name"
              class="tw:block tw:w-full"
              :disabled="isEditing && isUniversalPeriod && !canEditUniversal"
              :error="form.errors.name"
              label="Name*"
              placeholder="Lent 2025"
              required
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.slug"
              class="tw:block tw:w-full"
              :disabled="isEditing && isUniversalPeriod && !canEditUniversal"
              :error="form.errors.slug"
              label="Slug*"
              placeholder="lent-2025"
              required
            />
          </div>

          <div class="tw:mt-4">
            <TextareaInput
              v-model="form.description"
              class="tw:block tw:w-full"
              :disabled="isEditing && isUniversalPeriod && !canEditUniversal"
              :error="form.errors.description"
              label="Description"
              placeholder="The 40-day season of penance and preparation for Easter"
            />
          </div>

          <div class="tw:mt-4">
            <DateInput
              v-model="startDate"
              class="tw:block tw:w-full"
              :disabled="isEditing && isUniversalPeriod && !canEditUniversal"
              :error="form.errors.start_date"
              label="Start Date*"
              required
            />
          </div>

          <div class="tw:mt-4">
            <DateInput
              v-model="endDate"
              class="tw:block tw:w-full"
              :disabled="isEditing && isUniversalPeriod && !canEditUniversal"
              :error="form.errors.end_date"
              label="End Date*"
              required
            />
          </div>

          <div class="tw:mt-4 tw:flex tw:flex-wrap tw:gap-4">
            <v-checkbox
              v-model="form.is_active"
              color="primary"
              :disabled="isEditing && isUniversalPeriod && !canEditUniversal"
              hide-details="auto"
              label="Active"
            ></v-checkbox>
            <v-checkbox
              v-model="form.is_recurring"
              color="primary"
              :disabled="isEditing && isUniversalPeriod && !canEditUniversal"
              hide-details="auto"
              label="Recurring (auto-generate yearly)"
            ></v-checkbox>
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
          :disabled="form.processing || (isEditing && isUniversalPeriod && !canEditUniversal)"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update Period' : 'Add Period' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
