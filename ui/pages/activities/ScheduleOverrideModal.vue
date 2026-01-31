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
  import MultiTimeInput from '@/components/MultiTimeInput.vue';
  import { useDate } from 'vuetify/lib/framework.mjs';

  const props = defineProps({
    override: { type: Object, default: null },
    churchId: { type: [String, Number], default: null },
    dioceseId: { type: [String, Number], default: null },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const startDate = ref(parser.date(currentDate));
  const endDate = ref(parser.date(currentDate));

  const actionTypes = [
    { title: 'Exclude', value: 'exclude' },
    { title: 'Modify', value: 'modify' },
  ];

  const activityTypes = [
    { title: 'All Types', value: null },
    { title: 'Mass', value: 'mass' },
    { title: 'Confession', value: 'confession' },
    { title: 'Adoration', value: 'adoration' },
  ];

  const dayPatterns = [
    { title: 'Daily', value: 'daily' },
    { title: 'Weekdays', value: 'weekdays' },
    { title: 'Saturday', value: 'saturday' },
    { title: 'Sunday', value: 'sunday' },
    { title: 'Specific Days', value: 'specific' },
    { title: 'Monthly', value: 'monthly-nth' },
  ];

  const form = useForm({
    scope_type: props.dioceseId ? 'diocese' : 'church',
    scope_id: props.dioceseId || props.churchId || '',
    name: props.override?.name || '',
    reason: props.override?.reason || '',
    start_date: props.override?.start_date || null,
    end_date: props.override?.end_date || null,
    action: props.override?.action || 'exclude',
    activity_type: props.override?.activity_type || null,
    target_times: props.override?.target_times || [],
    target_day_patterns: props.override?.target_day_patterns || [],
    replacement_times: props.override?.replacement_times || [],
    is_active: props.override?.is_active ?? true,
  });

  // State for adding replacement times
  const replacementOriginal = ref('');
  const replacementNew = ref('');

  const isModifyAction = computed(() => form.action === 'modify');

  watch(
    () => props.override,
    (newValue) => {
      if (newValue?.id) {
        form.scope_type = newValue.scope_type;
        form.scope_id = newValue.scope_id;
        form.name = newValue.name || '';
        form.reason = newValue.reason || '';
        form.action = newValue.action || 'exclude';
        form.activity_type = newValue.activity_type || null;
        form.target_times = newValue.target_times || [];
        form.target_day_patterns = newValue.target_day_patterns || [];
        form.replacement_times = newValue.replacement_times || [];
        form.is_active = newValue.is_active;

        startDate.value = parser.date(newValue.start_date || currentDate);
        endDate.value = parser.date(newValue.end_date || currentDate);
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.clearErrors();
    } else {
      if (!form.scope_id) {
        if (props.churchId) {
          form.scope_type = 'church';
          form.scope_id = props.churchId;
        } else if (props.dioceseId) {
          form.scope_type = 'diocese';
          form.scope_id = props.dioceseId;
        }
      }
    }
  });

  const isEditing = computed(() => !!props.override?.id);

  const addReplacementTime = () => {
    if (replacementOriginal.value && replacementNew.value) {
      form.replacement_times.push({
        original: replacementOriginal.value,
        replacement: replacementNew.value,
      });
      replacementOriginal.value = '';
      replacementNew.value = '';
    }
  };

  const removeReplacementTime = (index) => {
    form.replacement_times.splice(index, 1);
  };

  const submit = () => {
    if (!form.scope_id) {
      return notify.error('Scope ID missing');
    }

    const url = isEditing.value
      ? `/api/v1/schedule-overrides/${props.override?.id}`
      : `/api/v1/schedule-overrides`;

    const method = isEditing.value ? 'put' : 'post';

    form.start_date = useDayjs(startDate.value).format('YYYY-MM-DD');
    form.end_date = useDayjs(endDate.value).format('YYYY-MM-DD');

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Override was updated successfully'
          : 'Override was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the override.'),
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
    <dialog-modal max-width="700" :show="showModal" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Exception' : 'New Exception' }} </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw:block tw:w-full tw:mt-1"
              density="default"
              :error="form.errors.name"
              label="Name (Optional)"
              placeholder="e.g. Christmas Schedule"
            />
          </div>

          <div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
            <DateInput
              v-model="startDate"
              class="tw:block tw:w-full"
              :error="form.errors.start_date"
              label="Start Date*"
              required
            />
            <DateInput
              v-model="endDate"
              class="tw:block tw:w-full"
              :error="form.errors.end_date"
              label="End Date*"
              required
            />
          </div>

          <div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
            <SelectInput
              v-model="form.action"
              class="tw:block tw:w-full"
              :error="form.errors.action"
              :items="actionTypes"
              label="Action*"
              required
            />
            <SelectInput
              v-model="form.activity_type"
              class="tw:block tw:w-full"
              clearable
              :error="form.errors.activity_type"
              :items="activityTypes"
              label="Activity Type"
            />
          </div>

          <div class="tw:mt-4">
            <MultiTimeInput
              v-model="form.target_times"
              :error="form.errors.target_times"
              label="Target Times (leave empty for all times)"
            />
          </div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.target_day_patterns"
              chips
              class="tw:block tw:w-full"
              :error="form.errors.target_day_patterns"
              :items="dayPatterns"
              label="Target Frequency Patterns (leave empty for all)"
              multiple
            />
          </div>

          <div v-if="isModifyAction" class="tw:mt-6 tw:p-4 tw:bg-gray-50 tw:rounded-lg">
            <h3 class="tw:text-sm tw:font-medium tw:mb-3">Time Replacements</h3>
            <div class="tw:flex tw:gap-3 tw:items-start tw:mb-3">
              <TimeInput v-model="replacementOriginal" label="Original Time" />
              <div class="tw:pt-4">→</div>
              <TimeInput v-model="replacementNew" label="New Time" />
              <div class="tw:pt-2">
                <SecondaryButton size="small" @click="addReplacementTime"> Add </SecondaryButton>
              </div>
            </div>

            <div v-if="form.replacement_times.length > 0" class="tw:mt-2">
              <div
                v-for="(replacement, index) in form.replacement_times"
                :key="index"
                class="tw:flex tw:justify-between tw:items-center tw:py-1 tw:px-2 tw:bg-white tw:border tw:rounded tw:mb-1"
              >
                <span class="tw:text-sm"
                  >{{ replacement.original }} → {{ replacement.replacement }}</span
                >
                <button
                  class="tw:text-red-500 hover:tw:text-red-700"
                  type="button"
                  @click="removeReplacementTime(index)"
                >
                  &times;
                </button>
              </div>
            </div>
            <p v-if="form.errors.replacement_times" class="tw:text-xs tw:text-red-500 tw:mt-1">
              {{ form.errors.replacement_times }}
            </p>
          </div>

          <div class="tw:mt-4">
            <v-textarea
              v-model="form.reason"
              auto-grow
              class="tw:mt-1"
              counter
              :error-messages="form.errors.reason"
              label="Reason (Optional)"
              rows="2"
              variant="outlined"
            />
          </div>

          <div class="tw:mt-2">
            <v-checkbox
              v-model="form.is_active"
              color="primary"
              hide-details
              label="Active"
            ></v-checkbox>
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
          {{ isEditing ? 'Update Exception' : 'Add Exception' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
