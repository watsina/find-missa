import './styles/app.scss';
import { date, notify } from '@/utils';
import { registerPlugins, useForm } from '@/plugins';
import App from './App.vue';

import { createApp } from 'vue';

const app = createApp(App);

registerPlugins(app);

app.config.globalProperties.$date = date;
app.config.globalProperties.$useForm = useForm;
app.config.globalProperties.$notify = notify;

app.mount('#app');
