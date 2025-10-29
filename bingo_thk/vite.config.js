import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/index.css',
                'resources/css/login.css',
                'resources/css/animation.css',
                'resources/js/app.js',
                'resources/js/page/bingo/register.js',
                'resources/js/page/bingo/detail.js',
            ],
            refresh: true,
        }),
    ],
});
