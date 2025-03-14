import { notify, useDayjs } from '@/utils';
import { registerPlugins, useForm } from '@/plugins';
import App from './App.vue';

import { createApp } from 'vue';

const app = createApp(App);

import '@/styles/vuetify.app.scss';

registerPlugins(app);

// app.config.globalProperties.$date = useDayjs;
// app.config.globalProperties.$useForm = useForm;
app.config.globalProperties.$notify = notify;

app.mount('#app');
