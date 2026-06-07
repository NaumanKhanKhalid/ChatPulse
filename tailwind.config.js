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
                sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
            },
            colors: {
                primary: { DEFAULT: '#10b981', hover: '#059669', light: '#d1fae5', dark: '#065f46' },
                ink: { 900: '#0c1411', 700: '#33403b', 500: '#5d6b65', 400: '#8a958f' },
                line: '#e6e9e7',
                online: '#10b981',
                busy: '#ef4444',
                away: '#f59e0b',
                guest: '#f59e0b',
                rail: '#111827',
            },
            boxShadow: {
                form: '0 1px 2px rgba(12,20,17,0.04), 0 24px 48px -24px rgba(12,20,17,0.18)',
                glass: '0 20px 60px -20px rgba(0,0,0,0.45)',
                btn: '0 8px 20px -8px rgba(16,185,129,0.7)',
            },
        },
    },
    plugins: [],
};
