<template>
  <v-autocomplete
    ref="input"
    v-bind="{ ...$attrs }"
    v-model="model"
    v-model:search="query"
    :clearable="clearable"
    :color="color"
    :density="density"
    :error-messages="error"
    hide-details="auto"
    :hint="hint"
    item-text="title"
    item-value="value"
    :items="items"
    :label="label"
    :loading="loading"
    :placeholder="placeholder"
  ></v-autocomplete>
</template>

<script setup>
  import { ref, watch, onMounted } from 'vue';
  import debounce from 'lodash/debounce';
  import http from '@/Plugins/Http';

  const props = defineProps({
    itemsData: { type: Array, default: () => [] },
    dataMapFunc: { type: Function, required: true }, // function to use to map the returned data to text and value
    url: { type: String, required: true },
    clearable: { type: Boolean, default: true },
    noDataText: { type: String, default: 'Nothing Found' },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: 'Start typing to Search' },
    density: { type: String, default: 'default' },
    color: { type: String, default: 'primary' },
  });

  const model = defineModel({ type: [String, Number, Object], required: true });
  const query = ref('');
  const loading = ref(false);
  const items = ref(props.itemsData || []);

  onMounted(() => fetchQueryItems());

  const fetchQueryItems = () => {
    loading.value = true;
    return http
      .get(props.url, {
        params: { search: query.value },
      })
      .then(({ data }) => {
        const results = data?.data?.data || data?.data || [];
        items.value = results?.map((contact) => props.dataMapFunc(contact)) || [];
      })
      .catch(() => console.log('Error fetching data...😭'))
      .finally(() => (loading.value = false));
  };

  watch(
    () => props.itemsData,
    (newValue) => {
      items.value = newValue;
    },
  );

  watch(
    query,
    debounce(() => {
      fetchQueryItems();
    }, 300),
  );
</script>

<script>
  export default {
    inheritAttrs: false,
  };
</script>
