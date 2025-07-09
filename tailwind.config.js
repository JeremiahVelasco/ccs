import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/****/***/**/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/views/livewire/**/*.blade.php',
    ],
    safelist: [
        'bg-red-200',
        'bg-green-600',
        'hover:bg-green-700',
        'focus:ring-green-500',
        'bg-green-500',
        'bg-green-800',
        'text-green-600',
        'text-green-700',
        'text-green-800',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
