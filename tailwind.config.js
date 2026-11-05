import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // zelena prema logotipu agro-log.app: od svijetle (primjesa limete) do tamnozelene #033B2C
                green: {
                    50: '#f3f8ed',
                    100: '#e3efd3',
                    200: '#c7e0a8',
                    300: '#a3cc76',
                    400: '#7fb44c',
                    500: '#4f9a3a',
                    600: '#2b7d3b',
                    700: '#19653a',
                    800: '#0d4f34',
                    900: '#064531',
                    950: '#033b2c',
                },
                // boje logotipa
                brand: {
                    tamna: '#033b2c',
                    limeta: '#86bc25',
                    'limeta-svijetla': '#9ccf3e',
                },
            },
        },
    },

    plugins: [forms],
};
