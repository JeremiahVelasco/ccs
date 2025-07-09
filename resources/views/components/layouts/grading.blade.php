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
                    <div class="flex gap-4 items-center">
                        <button 
                            id="groupGradingBtn"
                            onclick="showGroupGrading()"
                            class="grading-btn active text-xl font-semibold text-white transition-all duration-300 ease-in-out"
                            style="background-color: #059669; border-radius: 5px; padding: 5px 10px; border: none; cursor: pointer;"
                            onmouseover="if(this.classList.contains('active')) this.style.backgroundColor='#047857'"
                            onmouseout="if(this.classList.contains('active')) this.style.backgroundColor='#059669'"
                            >
                            Group Grading
                        </button>
                        <button 
                            id="individualGradingBtn"
                            onclick="showIndividualGrading()"
                            class="grading-btn text-xl font-semibold transition-all duration-300 ease-in-out"
                            style="background-color: #6b7280; border-radius: 5px; padding: 5px 10px; color: white; border: none; cursor: pointer;"
                            onmouseover="if(this.classList.contains('active')) this.style.backgroundColor='#047857'; else this.style.backgroundColor='#4b5563'"
                            onmouseout="if(this.classList.contains('active')) this.style.backgroundColor='#059669'; else this.style.backgroundColor='#6b7280'"
                            >
                            Individual Grading
                        </button>
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
            <!-- Group Grading Component -->
            <div id="groupGradingComponent" class="grading-component active">
                @livewire('grading-component')
            </div>

            <!-- Individual Grading Component -->
            <div id="individualGradingComponent" class="grading-component" style="display: none;">
                @livewire('individual-grading-component')
            </div>

            <!-- Fallback for custom slot content -->
            <div id="customContent" style="display: none;">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Filament Scripts -->
    <script src="{{ asset('js/filament/filament/app.js') }}"></script>
    <script src="{{ asset('js/filament/forms/app.js') }}"></script>
    <script src="{{ asset('js/filament/support/app.js') }}"></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Grading Toggle Script -->
    <script>
        function showGroupGrading() {
            // Hide all components
            document.querySelectorAll('.grading-component').forEach(component => {
                component.style.display = 'none';
                component.classList.remove('active');
            });
            
            // Show group grading component
            const groupComponent = document.getElementById('groupGradingComponent');
            groupComponent.style.display = 'block';
            groupComponent.classList.add('active');
            
            // Update button states
            updateButtonStates('group');
        }

        function showIndividualGrading() {
            // Hide all components
            document.querySelectorAll('.grading-component').forEach(component => {
                component.style.display = 'none';
                component.classList.remove('active');
            });
            
            // Show individual grading component
            const individualComponent = document.getElementById('individualGradingComponent');
            individualComponent.style.display = 'block';
            individualComponent.classList.add('active');
            
            // Update button states
            updateButtonStates('individual');
        }

        function updateButtonStates(activeType) {
            const groupBtn = document.getElementById('groupGradingBtn');
            const individualBtn = document.getElementById('individualGradingBtn');
            
            // Reset both buttons
            groupBtn.classList.remove('active');
            individualBtn.classList.remove('active');
            groupBtn.style.backgroundColor = '#6b7280';
            individualBtn.style.backgroundColor = '#6b7280';
            
            // Set active button
            if (activeType === 'group') {
                groupBtn.classList.add('active');
                groupBtn.style.backgroundColor = '#059669';
            } else {
                individualBtn.classList.add('active');
                individualBtn.style.backgroundColor = '#059669';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Default to group grading
            showGroupGrading();
        });
    </script>

    <style>
        .grading-component {
            transition: opacity 0.3s ease-in-out;
        }
        
        .grading-component.active {
            opacity: 1;
        }
        
        .grading-btn {
            transition: all 0.3s ease-in-out;
        }
        
        .grading-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .grading-btn:active {
            transform: translateY(0);
        }
    </style>
</body>
</html> 