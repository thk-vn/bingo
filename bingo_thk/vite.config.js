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
                'resources/css/dial.css',
                'resources/js/page/bingo/dial.js',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
