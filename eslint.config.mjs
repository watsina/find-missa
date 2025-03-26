import { defineConfig } from 'eslint/config';
import vue from 'eslint-plugin-vue';
import unusedImports from 'eslint-plugin-unused-imports';
import globals from 'globals';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import js from '@eslint/js';
import { FlatCompat } from '@eslint/eslintrc';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
  baseDirectory: __dirname,
  recommendedConfig: js.configs.recommended,
  allConfig: js.configs.all,
});

export default defineConfig([
  {
    extends: compat.extends(
      'eslint:recommended',
      'plugin:vue/vue3-essential',
      'plugin:prettier/recommended',
      'plugin:vuetify/base',
    ),

    plugins: {
      vue,
      'unused-imports': unusedImports,
    },

    languageOptions: {
      globals: {
        ...globals.node,
      },
    },

    rules: {
      'no-unused-vars': 'warn',
      'vue/no-unused-vars': 'warn',
      'unused-imports/no-unused-imports': 'warn',
      'vue/max-attributes-per-line': 'off',
      'vue/multi-word-component-names': 'off',
      'no-undef': 'warn',

      'vue/attributes-order': [
        'warn',
        {
          alphabetical: true,
        },
      ],

      'vue/valid-v-slot': [
        'warn',
        {
          allowModifiers: true,
        },
      ],
    },
  },
]);
