import { aliases, mdi } from 'vuetify/iconsets/mdi-svg';
import colors from '@/utils/colors';
import { md3 } from 'vuetify/blueprints';
import { VTimePicker } from 'vuetify/labs/VTimePicker';
import { VDateInput } from 'vuetify/labs/VDateInput';

// Composables
import { createVuetify } from 'vuetify';

const dataTableOptions = {
  itemsPerPage: 15,
  disableSort: true,
  itemsPerPageOptions: [
    { title: '15', value: 15 },
    { title: '25', value: 25 },
    { title: '50', value: 50 },
  ],
};

const theme = {
  dark: false,
  colors,
};

export default createVuetify({
  blueprint: md3,
  theme: {
    defaultTheme: 'theme',
    options: {
      customProperties: true,
    },
    themes: {
      theme,
    },
  },
  components: {
    VTimePicker,
    VDateInput,
  },
  defaults: {
    VCol: {
      cols: 12,
    },
    VAlert: {},
    VTextField: {
      variant: 'outlined',
      density: 'compact',
      color: 'primary',
      hideDetails: 'auto',
    },

    VTextarea: {
      variant: 'outlined',
      density: 'compact',
      color: 'primary',
      autoGrow: true,
    },
    VAutocomplete: {
      variant: 'outlined',
      density: 'compact',
      color: 'primary',
      itemValue: 'id',
    },
    VCombobox: {
      variant: 'outlined',
      density: 'compact',
      color: 'primary',
    },
    VBtn: {
      color: 'primary',
    },
    VCard: {
      // rounded: '3px',
    },
    VBtnToggle: {
      density: 'compact',
      color: 'primary',
    },

    VDataTable: dataTableOptions,
    VDataTableServer: dataTableOptions,
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
});
