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
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
        host: '192.168.1.87:8080',
        port: 5173,
        },
    },
});
