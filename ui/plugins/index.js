// exports
export { default as http } from './http';
export { default as useForm } from './form';

// Plugins
import vuetify from './vuetify';
// import posthogPlugin from './posthog';
import pinia from '../stores';
import router from './router';

export function registerPlugins(app) {
  app.use(vuetify).use(router).use(pinia); //.use(posthogPlugin);
}
