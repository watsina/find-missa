// exports
export { default as http } from './http';
export { default as useForm } from './form';
export { default as notify } from './notify';

// Plugins
import vuetify from './vuetify';
// import posthogPlugin from './posthog';
import pinia from '../stores';
import router from './router';
import head from './unhead';

export function registerPlugins(app) {
  app.use(vuetify).use(router).use(pinia).use(head); //.use(posthogPlugin);
}
