import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // carta.js: hoja ligera de la carta digital pública (RNF-03), separada del panel.
            input: ['resources/scss/app.scss', 'resources/js/app.js', 'resources/js/carta.js'],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            // Bootstrap 5.3 aún usa funciones de Sass que están en desuso.
            scss: { quietDeps: true, silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'] },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
