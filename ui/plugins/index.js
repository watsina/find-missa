// exports
export { default as http } from './http';
import { default as useForm } from './form';

// Plugins
import vuetify from './vuetify';
import pinia from '../store';
import router from '../router';

export function registerPlugins(app) {
  app.use(vuetify).use(router).use(pinia);
}
