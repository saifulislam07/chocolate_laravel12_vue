import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
                godiva: {
                    charcoal: '#1C1C1C',
                    cream: '#F3EFE9',
                    gold: '#B99D4B',
                    'gold-dark': '#8C733E',
                    pink: '#FBE0E3',
                    prefooter: '#333333',
                }
            }
        },
    },

    plugins: [forms],
};
