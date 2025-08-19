<script setup>
  import AppUserLayout from '@/layouts/AppUserLayout.vue';
  import { useFaqs } from '@/composables/useFaqs';
  import { mdiMinus, mdiPlus } from '@mdi/js';
  import { ref } from 'vue';

  const { faqs } = useFaqs();

  const panel = ref(0);
</script>

<template>
  <AppUserLayout>
    <v-container>
      <v-row class="tw:py-12">
        <v-col class="tw:text-center" cols="12">
          <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">
            Frequently Asked Questions
          </h2>
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">
            Here are some common questions and answers to help you navigate our platform and
            services.
          </p>
        </v-col>
        <v-col class="tw:mt-4" cols="12" md="8" offset-md="2">
          <v-expansion-panels v-model="panel" variant="popout">
            <v-expansion-panel
              v-for="(faq, index) in faqs"
              :key="index"
              :collapse-icon="mdiMinus"
              :color="index === panel ? 'grey-lighten-2' : 'default'"
              :expand-icon="mdiPlus"
            >
              <v-expansion-panel-title class="tw:font-bold">
                {{ faq.question }}
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <p v-html="faq.answer"></p>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </v-col>
      </v-row>
    </v-container>
  </AppUserLayout>
</template>
