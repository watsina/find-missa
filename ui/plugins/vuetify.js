import { aliases, mdi } from 'vuetify/iconsets/mdi-svg';
import colors from '@/utils/colors';
import { md3 } from 'vuetify/blueprints';

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
  defaults: {
    VCol: {
      cols: 12,
    },
    VAlert: {},
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      hideDetails: 'auto',
    },

    VTextarea: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      autoGrow: true,
      rows: 2,
    },
    VAutocomplete: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      itemValue: 'id',
    },
    VCombobox: {
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
    },
    VBtn: {
      rounded: 'lg',
      color: 'default',
    },
    VCard: {
      rounded: 'md',
    },
    VBtnToggle: {
      density: 'comfortable',
    },
    VDateInput: {
      density: 'comfortable',
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
