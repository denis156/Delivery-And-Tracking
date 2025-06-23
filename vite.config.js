import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';

const host = 'delivery-and-tracking.test';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // ← WAJIB: CSS entry point
                'resources/js/app.js',   // ← WAJIB: JS entry point
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host,
        hmr: { host },
        https: {
            key: fs.readFileSync(`/Users/denisdjadianardika/Library/Application Support/Herd/config/valet/Certificates/${host}.key`),
            cert: fs.readFileSync(`/Users/denisdjadianardika/Library/Application Support/Herd/config/valet/Certificates/${host}.crt`),
        }
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['axios'],
                    mapping: ['leaflet', 'leaflet-routing-machine'],
                }
            }
        },
        chunkSizeWarningLimit: 800, // Increase to 800kB untuk leaflet

        // Gunakan esbuild (recommended) - cepat & cukup bagus
        minify: 'esbuild',
        esbuild: {
            drop: ['console', 'debugger'],
        },

        sourcemap: false,
    }
});
