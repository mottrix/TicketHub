import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            /* Blade @vite calls reference these build entrypoints. Shared modules such
               as ticket-conversation.js are bundled through their importing entrypoint. */
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/tickethub-login.css",
                "resources/js/tickethub-login.js",
                "resources/css/tickethub-register.css",
                "resources/js/tickethub-register.js",
                "resources/css/create-ticket.css",
                "resources/css/edit-ticket.css",
                "resources/js/edit-ticket.js",
                "resources/js/create-ticket.js",
            ],
            /* Reload during development when Laravel templates and related files change. */
            refresh: true,
            fonts: [
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: "127.0.0.1",
        hmr: {
            host: "127.0.0.1",
        },
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});

