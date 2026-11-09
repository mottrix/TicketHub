import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
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
            refresh: true,
            fonts: [
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
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

