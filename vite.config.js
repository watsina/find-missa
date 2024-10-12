import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import Vuetify from 'vite-plugin-vuetify';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./ui', import.meta.url)),
        },
        extensions: ['.gql', '.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    },
    plugins: [
        laravel({
            input: 'ui/app.js',
            refresh: true,
        }),
        vue({
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
});
