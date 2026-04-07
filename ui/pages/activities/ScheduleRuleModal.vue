<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import { http } from '@/plugins';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TextInput from '@/components/TextInput.vue';
  import MultiTimeInput from '@/components/MultiTimeInput.vue';

  const props = defineProps({
    rule: { type: Object, default: null },
    churchId: { type: [String, Number], default: null },
    dioceseId: { type: [String, Number], default: null },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const holidays = ref([]);
  const loadingHolidays = ref(false);

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

  const conditionTypes = [
    { title: 'Public/Religious Holiday', value: 'liturgical_day' },
    { title: 'Specific Time Range', value: 'specific_time' },
    { title: 'Date Range', value: 'date_range' },
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
    name: props.rule?.name || '',
    description: props.rule?.description || '',
    condition_type: props.rule?.condition_type || 'liturgical_day',
    condition_holidays: props.rule?.condition_holidays || [],
    action: props.rule?.action || 'exclude',
    activity_type: props.rule?.activity_type || null,
    target_times: props.rule?.target_times || [],
    target_day_patterns: props.rule?.target_day_patterns || [],
    priority: props.rule?.priority || 0,
    is_active: props.rule?.is_active ?? true,
  });

  const isLiturgicalCondition = computed(() => form.condition_type === 'liturgical_day');

  function fetchHolidays() {
    loadingHolidays.value = true;
    http
      .get('/api/v1/holidays', {
        params: { itemsPerPage: 100 },
      })
      .then(({ data }) => {
        holidays.value = data.data.map((h) => ({
          title: h.name,
          value: h.slug || h.id,
        }));
      })
      .catch((error) => {
        console.error('Failed to fetch holidays:', error);
      })
      .finally(() => {
        loadingHolidays.value = false;
      });
  }

  onMounted(() => {
    fetchHolidays();
  });

  watch(
    () => props.rule,
    (newValue) => {
      if (newValue?.id) {
        form.scope_type = newValue.scope_type;
        form.scope_id = newValue.scope_id;
        form.name = newValue.name || '';
        form.description = newValue.description || '';
        form.condition_type = newValue.condition_type || 'liturgical_day';
        form.condition_holidays = newValue.condition_holidays || [];
        form.action = newValue.action || 'exclude';
        form.activity_type = newValue.activity_type || null;
        form.target_times = newValue.target_times || [];
        form.target_day_patterns = newValue.target_day_patterns || [];
        form.priority = newValue.priority || 0;
        form.is_active = newValue.is_active;
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

  const isEditing = computed(() => !!props.rule?.id);

  const submit = () => {
    if (!form.scope_id) {
      return notify.error('Scope ID missing');
    }

    const url = isEditing.value
      ? `/api/v1/schedule-rules/${props.rule?.id}`
      : `/api/v1/schedule-rules`;

    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Rule was updated successfully'
          : 'Rule was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the rule.'),
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
      <template #title> {{ isEditing ? 'Update Rule' : 'New Rule' }} </template>

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
              placeholder="e.g. No mass on Good Friday"
            />
          </div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.condition_type"
              class="tw:block tw:w-full"
              :error="form.errors.condition_type"
              :items="conditionTypes"
              label="Condition Type*"
              required
            />
          </div>

          <div v-if="isLiturgicalCondition" class="tw:mt-4">
            <SelectInput
              v-model="form.condition_holidays"
              chips
              class="tw:block tw:w-full"
              :error="form.errors.condition_holidays"
              :items="holidays"
              label="Holidays*"
              :loading="loadingHolidays"
              multiple
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

          <div class="tw:mt-4">
            <TextInput
              v-model="form.priority"
              class="tw:block tw:w-full tw:mt-1"
              density="default"
              :error="form.errors.priority"
              label="Priority (0-100)"
              type="number"
            />
          </div>

          <div class="tw:mt-4">
            <v-textarea
              v-model="form.description"
              auto-grow
              class="tw:mt-1"
              counter
              :error-messages="form.errors.description"
              label="Description (Optional)"
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
          {{ isEditing ? 'Update Rule' : 'Add Rule' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
