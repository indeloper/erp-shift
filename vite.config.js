import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/css/custom/projects.scss',
                'resources/assets/js/layout/layout.js',
                'resources/assets/js/objects/init-datagrid.js',
                'resources/assets/js/pages/notifications/notifications.js',
                'resources/assets/js/plugins/pusher_and_vue.js',
                'resources/assets/js/projects/init-datagrid.js',
                'resources/assets/js/telegram/auth-web-apps.js',
            ],
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
    ],
});
