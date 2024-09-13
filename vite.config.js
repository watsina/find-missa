import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import vuetify from 'vite-plugin-vuetify';

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
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({
            autoImport: true,
            styles: {
                configFile: 'ui/styles/settings.scss',
            },
        }),
    ],
});
