// Plugins
import vuetify from './vuetify';
import pinia from '../store';
import router from '../router';
// import VueGoogleMaps from '@fawmi/vue-google-maps';

export function registerPlugins(app) {
  app.use(vuetify).use(router).use(pinia);
  // .use(VueGoogleMaps, {
  //   load: {
  //     key: import.meta.env.VITE_GOOGLE_API_KEY,
  //   },
  // });
}
