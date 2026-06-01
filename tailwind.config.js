import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                rail: '#111827',
                sidebar: { bg: '#f9fafb', border: '#e5e7eb' },
                primary: { DEFAULT: '#10b981', hover: '#059669', light: '#d1fae5', dark: '#065f46' },
                online: '#10b981',
                offline: '#9ca3af',
                busy: '#ef4444',
                away: '#f59e0b',
                guest: '#f59e0b',
                chat: { bg: '#ffffff', input: '#f3f4f6' },
            },
        },
    },
    plugins: [],
};
