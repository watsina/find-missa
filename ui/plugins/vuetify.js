// Styles
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg';
import colors from '@/utils/colors';
import { VTimePicker } from 'vuetify/labs/VTimePicker';

// Composables
import { createVuetify } from 'vuetify';

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  components: {
    VTimePicker,
  },
  icons: {
    defaultSet: 'mdi',
    aliases: {
      ...aliases,
    },
    sets: {
      mdi,
    },
  },
  theme: {
    themes: {
      dark: { colors },
      light: { colors },
    },
  },
});
