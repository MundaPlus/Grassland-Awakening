import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app-frontend.css',
                'resources/js/app-frontend.js',

                'resources/sass/app-backend.scss',
                'resources/js/app-backend.js',

                // Game-specific assets
                'resources/css/game/character.css',
                'resources/js/game/character.js',
                'resources/css/game/dashboard.css',
                'resources/css/game/inventory.css',
                'resources/css/game/adventures.css',
                'resources/css/game/adventure-map.css',
                'resources/css/game/skills.css',
                'resources/css/game/crafting.css',
                'resources/js/game/inventory.js',
                'resources/js/game/adventures.js',
                'resources/js/game/adventure-map.js',
                'resources/js/game/skills.js',
                'resources/js/game/village.js',
                'resources/js/game/crafting.js',
            ],
            // refresh: true,
            refresh: [
                'app/View/Components/**',
                'lang/**',
                'resources/lang/**',
                'resources/views/**',
                'resources/routes/**',
                'routes/**',
                'Modules/**/Resources/lang/**',
                'Modules/**/Resources/views/**/*.blade.php',
            ],
        }),
    ],
    resolve: {
        alias: {
            '~coreui': path.resolve(__dirname, 'node_modules/@coreui/coreui'),
        }
    },
});
