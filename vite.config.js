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
                'resources/css/register.css',
                'resources/css/dashboard.css',
                'resources/css/admin/dashboard.css',
                'resources/js/admin/dashboard.js',
                'resources/css/admin/styles.css',
                'resources/css/settings.css',
                'resources/js/dashboard.js',
                'resources/css/links.css',
                'resources/js/links.js',
                'resources/css/admin/domains/index.css',
                'resources/css/admin/links/index.css',
                'resources/js/admin/links/index.js',
                'resources/js/admin/domains/index.js',
                'resources/css/admin/users/index.css',
                'resources/js/admin/users/index.js',
                'resources/css/admin/users/show.css',
                'resources/js/admin/users/show.js',
                'resources/css/admin/settings.css',
                'resources/js/admin/settings/index.js',
                'resources/js/admin/settings/maintenance.js',
                'resources/js/admin/search.js',
                'resources/css/admin/burger.css',
            ],
            refresh: true,
        }),
    ],
});
