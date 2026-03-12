// https://unhead.unjs.io/setup/vue/installation
import { InferSeoMetaPlugin } from '@unhead/addons';
import { createHead } from '@unhead/vue/client';

const head = createHead();
head.use(InferSeoMetaPlugin());

export default head;
