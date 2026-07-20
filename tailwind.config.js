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
                script: ['"Dancing Script"', 'cursive'],
                menu: ['Oswald', ...defaultTheme.fontFamily.sans],
                // Figma "CocoCraft" home spec fonts
                heading: ['Oswald', ...defaultTheme.fontFamily.sans],
                corinthia: ['Corinthia', 'cursive'],
                body: ['Parkinsans', 'Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Coco Craft brand palette, per Figma spec.
                godiva: {
                    charcoal: '#4B2E1E',
                    cream: '#F5EBDD',
                    gold: '#E89A50',
                    'gold-dark': '#C97830',
                    pink: '#FBEBD9',
                    green: '#B5C934',
                    prefooter: '#3A2517',
                },
                // Menu-specific spec colors (Figma Dev Mode): default + active/hover nav text.
                menu: {
                    text: '#484747',
                    active: '#F69521',
                },
                // CocoCraft home page — exact Figma Dev Mode hexes.
                cocov: {
                    gold: '#F69521',
                    text: '#484747',
                    muted: '#B3AEAC',
                    card: '#F6F2EC',
                    offer: '#F4EADF',
                    line: '#F2E4D6',
                    brown: '#6B2410',
                    'brown-dark': '#430F00',
                }
            }
        },
    },

    plugins: [forms],
};
