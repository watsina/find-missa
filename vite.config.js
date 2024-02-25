import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify';
import ViteFonts from 'unplugin-fonts/vite';

export default defineConfig({
    resolve: {
        alias: {
            '@': '/ui/',
            // ziggy: path.resolve('vendor/tightenco/ziggy/dist/vue.m'),
        },
        extensions: ['.gql', '.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
        optimizeDeps: {
            include: ['vue-google-maps-community-fork', 'fast-deep-equal'],
        },
    },
    plugins: [
        laravel({
            input: 'ui/app.js',
            ssr: 'ui/ssr.js',
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
        ViteFonts({
            google: {
                families: [
                    {
                        name: 'Roboto',
                        styles: 'wght@100;300;400;500;700;900',
                    },
                ],
            },
        }),
    ],
});
