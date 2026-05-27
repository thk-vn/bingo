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
                'resources/js/page/bingo/register.js',
                'resources/js/page/bingo/detail.js',
                'resources/css/dial.css',
                'resources/js/page/bingo/dial.js',
                'resources/js/app.js',
                'resources/js/page/bingo/bingo-board.js',
                'resources/images/logo-bingo-2026.jpg',
                'resources/css/v2/index.css',
                'resources/images/logo-bingo-2026-removebg-preview.png',
                'resources/images/popup-bingo.gif',
                'resources/images/2026 — Code The Wave.png'
            ],
            refresh: true,
        }),
    ],
});
