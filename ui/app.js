import './styles/app.css';
import date from './utils/date';
import http from '@/plugins/http';
import form from './plugins/form';

import { registerPlugins } from '@/plugins/index.js';
import App from './App.vue';

import { createApp } from 'vue';

const app = createApp(App);

registerPlugins(app);

app.config.globalProperties.$date = date;
app.config.globalProperties.$http = http;
app.config.globalProperties.$form = form;

app.mount('#app');
