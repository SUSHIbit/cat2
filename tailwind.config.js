import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "cat-primary": "#0f172a",
                "cat-secondary": "#334155",
                "cat-accent": "#475569",
                "cat-background": "#f8fafc",
                "cat-muted": "#f1f5f9",
                "cat-border": "#e2e8f0",
            },
        },
    },

    plugins: [forms, typography],
};
