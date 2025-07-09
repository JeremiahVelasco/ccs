<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Project Grading</title>
    
    <!-- Filament Styles -->
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filament/forms/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filament/support/app.css') }}">
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Project Grading
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ url('/') }}" 
                            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </div>

    <!-- Filament Scripts -->
    <script src="{{ asset('js/filament/filament/app.js') }}"></script>
    <script src="{{ asset('js/filament/forms/app.js') }}"></script>
    <script src="{{ asset('js/filament/support/app.js') }}"></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html> 