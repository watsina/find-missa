import { notify, useDayjs } from '@/utils';
import { registerPlugins } from '@/plugins';
import App from './App.vue';

import '@/styles/vuetify.app.scss';

import { createApp } from 'vue';

const app = createApp(App);

registerPlugins(app);

app.config.globalProperties.$date = useDayjs;
app.config.globalProperties.$notify = notify;

app.mount('#app');
