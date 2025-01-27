import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/styles.css',
                'resources/js/home.js',
                'resources/css/home.css',
                'resources/css/login.css',
                'resources/css/dashboard.css',
                'resources/css/settings.css',
                'resources/js/dashboard.js',
                'resources/css/links.css',
                'resources/js/links.js',
            ],
            refresh: true,
        }),
    ],
});
