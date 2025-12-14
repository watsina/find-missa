<script setup>
  import { computed, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import useAuthStore from '@/stores/useAuthStore';

  const props = defineProps({
    holiday: { type: Object, default: null },
  });

  const authStore = useAuthStore();
  const authUser = computed(() => authStore.user);

  const showModal = defineModel({ type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const holidayTypes = [
    { title: 'Fixed Date', value: 'fixed' },
    { title: 'Easter Relative', value: 'easter-relative' },
    { title: 'Computed', value: 'computed' },
  ];

  const liturgicalColors = [
    { title: 'White', value: 'white' },
    { title: 'Red', value: 'red' },
    { title: 'Purple', value: 'purple' },
    { title: 'Green', value: 'green' },
    { title: 'Rose', value: 'rose' },
    { title: 'Gold', value: 'gold' },
  ];

  const months = [
    { title: 'January', value: 1 },
    { title: 'February', value: 2 },
    { title: 'March', value: 3 },
    { title: 'April', value: 4 },
    { title: 'May', value: 5 },
    { title: 'June', value: 6 },
    { title: 'July', value: 7 },
    { title: 'August', value: 8 },
    { title: 'September', value: 9 },
    { title: 'October', value: 10 },
    { title: 'November', value: 11 },
    { title: 'December', value: 12 },
  ];

  const daysOfWeek = [
    { title: 'Sunday', value: 'sunday' },
    { title: 'Monday', value: 'monday' },
    { title: 'Tuesday', value: 'tuesday' },
    { title: 'Wednesday', value: 'wednesday' },
    { title: 'Thursday', value: 'thursday' },
    { title: 'Friday', value: 'friday' },
    { title: 'Saturday', value: 'saturday' },
  ];

  const form = useForm({
    name: props.holiday?.name || '',
    slug: props.holiday?.slug || '',
    type: props.holiday?.type || 'fixed',
    fixed_month: props.holiday?.fixed_month || null,
    fixed_day: props.holiday?.fixed_day || null,
    easter_offset_days: props.holiday?.easter_offset_days || null,
    computation_rule: props.holiday?.computation_rule || null,
    is_holy_day_of_obligation: props.holiday?.is_holy_day_of_obligation || false,
    is_universal: props.holiday?.is_universal ?? true,
    is_moveable: props.holiday?.is_moveable || false,
    liturgical_color: props.holiday?.liturgical_color || 'white',
  });

  // For computed rule fields
  const computedNth = ref(props.holiday?.computation_rule?.nth || 1);
  const computedDay = ref(props.holiday?.computation_rule?.day || 'sunday');
  const computedMonth = ref(props.holiday?.computation_rule?.month || 1);
  const computedLast = ref(props.holiday?.computation_rule?.last || false);

  watch(
    () => props.holiday,
    (newValue) => {
      if (newValue?.id) {
        form.name = newValue?.name || '';
        form.slug = newValue?.slug || '';
        form.type = newValue?.type || 'fixed';
        form.fixed_month = newValue?.fixed_month || null;
        form.fixed_day = newValue?.fixed_day || null;
        form.easter_offset_days = newValue?.easter_offset_days || null;
        form.computation_rule = newValue?.computation_rule || null;
        form.is_holy_day_of_obligation = newValue?.is_holy_day_of_obligation || false;
        form.is_universal = newValue?.is_universal ?? true;
        form.is_moveable = newValue?.is_moveable || false;
        form.liturgical_color = newValue?.liturgical_color || 'white';

        // Update computed rule refs
        if (newValue?.computation_rule) {
          computedNth.value = newValue.computation_rule?.nth || 1;
          computedDay.value = newValue.computation_rule?.day || 'sunday';
          computedMonth.value = newValue.computation_rule?.month || 1;
          computedLast.value = newValue.computation_rule?.last || false;
        }
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
      computedNth.value = 1;
      computedDay.value = 'sunday';
      computedMonth.value = 1;
      computedLast.value = false;
    }
  });

  const isEditing = computed(() => !!props.holiday?.id);
  const canEditUniversal = computed(() => authUser.value?.is_admin);

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
    // Build computation_rule if type is computed
    if (form.type === 'computed') {
      form.computation_rule = {
        nth: computedLast.value ? null : computedNth.value,
        day: computedDay.value,
        month: computedMonth.value,
        last: computedLast.value,
      };
    } else {
      form.computation_rule = null;
    }

    // Clear irrelevant fields based on type
    if (form.type !== 'fixed') {
      form.fixed_month = null;
      form.fixed_day = null;
    }
    if (form.type !== 'easter-relative') {
      form.easter_offset_days = null;
    }

    const url = isEditing.value ? `/api/v1/holidays/${props.holiday?.id}` : `/api/v1/holidays`;
    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Holiday was updated successfully'
          : 'Holiday was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the holiday.'),
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
    <dialog-modal max-width="700" :show="showModal" @close="closeModal">
      <template #title>{{ isEditing ? 'Update Holiday' : 'New Holiday' }}</template>

      <template #content>
        <form>
          <div class="tw:grid tw:grid-cols-2 tw:gap-4">
            <div class="tw:col-span-2">
              <TextInput
                v-model="form.name"
                class="tw:block tw:w-full"
                :error="form.errors.name"
                label="Name*"
                placeholder="Christmas"
                required
              />
            </div>

            <div>
              <TextInput
                v-model="form.slug"
                class="tw:block tw:w-full"
                :error="form.errors.slug"
                label="Slug*"
                placeholder="christmas"
                required
              />
            </div>

            <div>
              <SelectInput
                v-model="form.type"
                class="tw:block tw:w-full"
                :error="form.errors.type"
                :items="holidayTypes"
                label="Type*"
                required
              />
            </div>

            <!-- Fixed Date Fields -->
            <template v-if="form.type === 'fixed'">
              <div>
                <SelectInput
                  v-model="form.fixed_month"
                  class="tw:block tw:w-full"
                  :error="form.errors.fixed_month"
                  :items="months"
                  label="Month*"
                  required
                />
              </div>
              <div>
                <TextInput
                  v-model.number="form.fixed_day"
                  class="tw:block tw:w-full"
                  :error="form.errors.fixed_day"
                  label="Day*"
                  max="31"
                  min="1"
                  placeholder="25"
                  required
                  type="number"
                />
              </div>
            </template>

            <!-- Easter Relative Fields -->
            <template v-if="form.type === 'easter-relative'">
              <div class="tw:col-span-2">
                <TextInput
                  v-model.number="form.easter_offset_days"
                  class="tw:block tw:w-full"
                  :error="form.errors.easter_offset_days"
                  hint="Negative for before Easter (e.g., -46 for Ash Wednesday), positive for after (e.g., 49 for Pentecost)"
                  label="Days from Easter*"
                  persistent-hint
                  placeholder="0"
                  required
                  type="number"
                />
              </div>
            </template>

            <!-- Computed Rule Fields -->
            <template v-if="form.type === 'computed'">
              <div class="tw:col-span-2">
                <v-checkbox
                  v-model="computedLast"
                  color="primary"
                  hide-details="auto"
                  label="Last occurrence in month"
                ></v-checkbox>
              </div>
              <div v-if="!computedLast">
                <TextInput
                  v-model.number="computedNth"
                  class="tw:block tw:w-full"
                  hint="1st, 2nd, 3rd, 4th occurrence"
                  label="Nth occurrence*"
                  max="5"
                  min="1"
                  persistent-hint
                  placeholder="4"
                  required
                  type="number"
                />
              </div>
              <div>
                <SelectInput
                  v-model="computedDay"
                  class="tw:block tw:w-full"
                  :items="daysOfWeek"
                  label="Day of Week*"
                  required
                />
              </div>
              <div>
                <SelectInput
                  v-model="computedMonth"
                  class="tw:block tw:w-full"
                  :items="months"
                  label="Month*"
                  required
                />
              </div>
            </template>

            <div>
              <SelectInput
                v-model="form.liturgical_color"
                class="tw:block tw:w-full"
                :error="form.errors.liturgical_color"
                :items="liturgicalColors"
                label="Liturgical Color"
              />
            </div>

            <div class="tw:col-span-2 tw:flex tw:flex-wrap tw:gap-4">
              <v-checkbox
                v-model="form.is_holy_day_of_obligation"
                color="primary"
                hide-details="auto"
                label="Holy Day of Obligation"
              ></v-checkbox>
              <v-checkbox
                v-model="form.is_moveable"
                color="primary"
                hide-details="auto"
                label="Moveable Feast"
              ></v-checkbox>
              <v-checkbox
                v-model="form.is_universal"
                color="primary"
                :disabled="isEditing && holiday?.is_universal && !canEditUniversal"
                hide-details="auto"
                label="Universal (All Teams)"
              ></v-checkbox>
            </div>
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update Holiday' : 'Add Holiday' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
