import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'public/css/general.css',
                'public/css/home.css',
                'public/css/dashboard.css',
                'public/css/scan.css',
                'public/css/history.css',
                'public/css/result.css',
                'public/css/settings.css',
                'public/css/login-register.css',
                'resources/js/app.js',
                'resources/js/auth.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
