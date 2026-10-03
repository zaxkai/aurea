import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    safelist: [
        'bg-mood-energetic',
        'bg-mood-calm',
        'bg-mood-neutral',
        'bg-mood-stressed',
        'bg-mood-exhausted',
        'border-mood-energetic',
        'border-mood-calm',
        'border-mood-neutral',
        'border-mood-stressed',
        'border-mood-exhausted',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: '#000F2E',
                aurea: '#2EE0E0',
                bg: '#F2F3F8',
                mood: {
                    energetic: '#D6E64C', // lime
                    calm: '#F072C0',      // light pink/magenta
                    neutral: '#39D98A',   // green
                    stressed: '#F43F72',  // hot pink
                    exhausted: '#33C6D9', // cyan
                },
            },
        },
    },

    plugins: [forms],
};