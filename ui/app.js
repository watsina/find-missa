import './bootstrap';
import './styles/app.css';

import { registerPlugins } from '@/plugins/index.js';
import App from './App.vue';

import { createApp } from 'vue';

const app = createApp(App);

registerPlugins(app);

app.mount('#app');
