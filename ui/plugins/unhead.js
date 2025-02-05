// https://unhead.unjs.io/setup/vue/installation
import { InferSeoMetaPlugin } from '@unhead/addons';
import { createHead } from '@unhead/vue';

export default createHead({
  plugins: [
    InferSeoMetaPlugin({
      ogTitle: (title) => title?.replace('- Find Missa', ''),
    }),
  ],
});
