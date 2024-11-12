<script setup>
  import AppLayout from '@/layouts/AppLayout.vue';
  import NavLink from '@/components/NavLink.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import ImportDataModal from './ImportDataModal.vue';
  import { ref, onMounted, computed } from 'vue';
  import { http } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';

  const store = useAuthStore();

  const user = computed(() => store?.user);

  const isFetching = ref(false);
  const showUploadModal = ref(false);

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

    <import-data-modal
      v-if="user.is_admin"
      v-model:show-modal="showUploadModal"
    ></import-data-modal>

    <v-container>
      <v-row class="py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <div class="p-6 text-gray-900 dark:text-gray-100">Welcome to your dashboard</div>
          <PrimaryButton v-if="user.is_admin" @click="showUploadModal = true">
            Upload data
          </PrimaryButton>
        </v-col>
        <template v-if="isFetching">
          <v-col v-for="i in 6" :key="i" cols="12" md="4">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </template>
        <v-col v-for="card in cards" v-else :key="card.name" cols="12" md="4">
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
