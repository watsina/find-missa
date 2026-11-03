import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify';
import { fileURLToPath, URL } from 'node:url';

import path from 'node:path';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd());

  return {
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./ui', import.meta.url)),
      },
      extensions: ['.gql', '.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    },
    css: {
      preprocessorOptions: {
        scss: {
          api: 'modern-compiler',
        },
      },
    },
    build: {
      target: 'esnext',
      minify: 'esbuild',
      rollupOptions: {},
    },
    plugins: [
      laravel({
        input: ['ui/app.js', 'ui/styles/app.css'],
        refresh: true,
      }),
      vue({
        template: {
          transformAssetUrls,
        },
      }),
      vuetify({
        autoImport: { labs: true },
        styles: {
          configFile: 'ui/styles/settings.scss',
        },
      }),
      tailwindcss(),
    ],
  };
});
