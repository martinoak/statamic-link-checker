import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';

export default defineConfig({
    server: {
        strictPort: true, // Zajistí, že se Vite nesnaží přepnout port
        cors: true  // Prevence chyby "blocked by CORS policy"
    },
    publicDir: false,
    plugins: [
        laravel({
            input: [
                'resources/js/cp.js',
                'resources/css/cp.css'
            ],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
});
