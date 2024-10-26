<script setup>
  import AppLayout from '@/layouts/AppLayout.vue';
  import NavLink from '@/components/NavLink.vue';
  import { ref, onMounted, computed } from 'vue';
  import { http } from '@/plugins';

  const isFetching = ref(false);

  const stats = ref([]);

  onMounted(() => {
    isFetching.value = true;
    http
      .get('/api/v1/dashboard')
      .then(({ data }) => {
        stats.value = data?.stats;
      })
      .finally(() => (isFetching.value = false));
  });

  const cards = computed(() =>
    Object.keys(stats.value)?.map((key) => {
      return {
        name: `Number of ${key}`,
        routeName: `${key}.index`,
        // icon: UserGroupIcon,
        count: stats.value[key],
      };
    }),
  );
</script>

<template>
  <AppLayout>
    <template #head>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Dashboard
      </h2>
    </template>

    <v-container>
      <v-row class="py-12">
        <v-col cols="12">
          <div class="p-6 text-gray-900 dark:text-gray-100">Welcome to your dashboard</div>
        </v-col>
        <v-col v-for="card in cards" :key="card.name" cols="12" md="4">
          <v-card rounded="lg">
            <div class="tw-flex tw-items-center tw-p-5">
              <div class="tw-flex-shrink-0">
                <!-- <component -->
                <!--   :is="card.icon" -->
                <!--   aria-hidden="true" -->
                <!--   class="tw-w-6 tw-h-6 tw-text-gray-400" -->
                <!-- /> -->
              </div>
              <div class="tw-flex-1 tw-w-0 tw-ml-5">
                <dl>
                  <dt class="tw-text-sm tw-font-medium tw-text-gray-500 tw-truncate">
                    {{ card.name }}
                  </dt>
                  <dd>
                    <div class="tw-text-lg tw-font-medium tw-text-gray-900">
                      {{ card.count }}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
            <v-card-actions class="tw-px-5 tw-py-2 tw-bg-gray-50">
              <div class="tw-text-sm">
                <NavLink
                  class="tw-font-medium tw-text-indigo-700 hover:tw-text-indigo-900"
                  :href="{ name: card.routeName }"
                >
                  View all
                </NavLink>
              </div>
            </v-card-actions>
          </v-card>
        </v-col>
        <v-col cols="12"></v-col>
        <v-col cols="12"></v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>
