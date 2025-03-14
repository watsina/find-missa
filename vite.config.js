import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import Vuetify from 'vite-plugin-vuetify';
import { fileURLToPath, URL } from 'node:url';

import fs from 'fs';
import { homedir } from 'os';
import { resolve } from 'path';

function detectServerConfig(mode) {
  // Load current .env-file
  const env = loadEnv(mode, process.cwd(), '');

  // we skip if no url
  if (!env.APP_URL) {
    return {};
  }

  // Set the host based on APP_URL
  let host = new URL(env.APP_URL).host;

  let keyPath = resolve(homedir(), `.config/valet/Certificates/${host}.key`);
  let certificatePath = resolve(homedir(), `.config/valet/Certificates/${host}.crt`);

  if (!fs.existsSync(keyPath)) {
    return {};
  }

  if (!fs.existsSync(certificatePath)) {
    return {};
  }

  return {
    hmr: { host },
    host,
    https: {
      key: fs.readFileSync(keyPath),
      cert: fs.readFileSync(certificatePath),
    },
  };
}

export default defineConfig(({ mode }) => ({
  server: detectServerConfig(mode),
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
    minify: true,
    sourcemap: false,
    rollupOptions: {
      output: {},
    },
  },
  plugins: [
    laravel({
      input: ['ui/app.js', 'ui/styles/app.css'],
      refresh: true,
    }),
    vue({
      autoImport: { labs: true },
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    Vuetify({
      autoImport: true,
      styles: {
        configFile: 'ui/styles/settings.scss',
      },
    }),
  ],
}));
