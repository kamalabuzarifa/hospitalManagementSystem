import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        'bg-green-600',
        'hover:bg-green-700',
        'bg-blue-600',
        'hover:bg-blue-700',
        'bg-gray-600',
        'hover:bg-gray-700',
        'bg-yellow-600',
        'hover:bg-yellow-700',
    ],

    theme: {
        extend: {
            colors: {
                primary: '#2b66f1',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
