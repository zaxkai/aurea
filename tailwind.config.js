import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: '#0B1224',
                aurea: '#22D3D9',
                bg: '#F3F5F8',
                mood: {
                    energetic: '#D6E64C',
                    calm: '#F072C0',
                    neutral: '#39D98A',
                    stressed: '#F43F72',
                    exhausted: '#33C6D9',
                },
            },
        },
    },

    plugins: [forms],
};