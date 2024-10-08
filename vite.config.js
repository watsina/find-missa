import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import Vuetify, { transformAssetUrls } from 'vite-plugin-vuetify';

export default defineConfig({
    resolve: {
        alias: {
            '@': '/ui/',
        },
        extensions: ['.gql', '.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    },
    plugins: [
        laravel({
            input: 'ui/app.js',
            refresh: true,
        }),
        vue({
            template: { transformAssetUrls },
        }),
        Vuetify({
            autoImport: true,
            styles: {
                configFile: 'ui/styles/settings.scss',
            },
        }),
    ],
});
