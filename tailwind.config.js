import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
                serif: ['Cormorant Garamond', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Coco Craft brand palette, sourced from the logo (public/images/cococraft-logo.svg):
                // chocolate brown, warm orange, lime green accent, and cream.
                godiva: {
                    charcoal: '#4B2E1E',
                    cream: '#F5EBDD',
                    gold: '#E89A50',
                    'gold-dark': '#C97830',
                    pink: '#FBEBD9',
                    green: '#B5C934',
                    prefooter: '#3A2517',
                }
            }
        },
    },

    plugins: [forms],
};
