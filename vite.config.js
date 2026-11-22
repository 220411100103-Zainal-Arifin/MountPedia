import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/layout.css",
                "resources/js/layout.js",
                "resources/css/home.css",
                "resources/js/home.js",
                "resources/css/gunung.css",
                "resources/js/gunung.js",
                "resources/css/perlengkapan.css",
                "resources/js/perlengkapan.js",
                "resources/css/sewa.css",
                "resources/css/app.css",
                "resources/js/mountain-detail.js",
                "resources/css/sewa-detail.css",
                "resources/js/sewa-detail.js",
                "resources/css/admin.css",
                "resources/js/admin.js",
                "resources/css/gear-detail.css",
                "resources/js/gear-detail.js",
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
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
