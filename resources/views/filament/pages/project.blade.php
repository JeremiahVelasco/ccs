<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Project Management</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $this->hasProject ? 'View and manage your group project' : 'Create a new project for your group' }}
            </p>
        </div>

        <!-- No Group Warning -->
        @if (!$this->group)
            <div class="p-4 border rounded-lg bg-yellow-50 border-yellow-200 dark:bg-yellow-900/50 dark:border-yellow-800">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No Group Found</h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>You are not assigned to any group yet. Please join or create a group before creating a project.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Existing Project Display -->
        @if ($this->hasProject)
            <!-- Edit Project Form -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Project Details</h3>
                <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
                    <form wire:submit.prevent="updateProject">
                        {{ $this->form }}

                        <div class="mt-6 flex justify-end">
                            <x-filament::button type="submit">
                                Update Project
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="flex gap-4 w-full">
                <!-- Progress -->
                <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800 w-full">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Overall Progress</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Calculated by the number of tasks completed</p>
                    <p class="text-2xl font-bold">
                        {{ round($this->project->progressAttribute()) }}%
                    </p>
                </div>

                <!-- Completion Prediction -->
                <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800 w-full">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">ML Completion Prediction</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Calculated by the number of tasks approved, adherance to deadline and team collaboration</p>
                    <p class="text-2xl font-bold">
                        {{ $this->project->completion_probability * 100 }}%
                    </p>
                    <div class="flex justify-between items-center">
                        @if ($this->project->last_prediction_at)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Last updated {{ $this->project->last_prediction_at->diffForHumans()}}
                            </p>
                        @endif
                    </div>
                    <div class="flex justify-end">
                        <x-filament::button wire:click="refreshCompletionProbability">
                            Refresh
                        </x-filament::button>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex justify-between items-start">
                    <div class="flex items-center">
                        @if($this->project->logo)
                            <div class="mr-4 flex-shrink-0">
                                <img src="{{ Storage::url($this->project->logo) }}" alt="{{ $this->project->title }} Logo" class="h-16 w-16 object-cover rounded-full">
                            </div>
                        @endif
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $this->project->title }}</h2>
                            <div class="mt-1 flex items-center">
                                <span class="inline-flex items-center py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $this->project->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Created {{ $this->project->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                    <p class="whitespace-pre-line">{{ $this->project->description }}</p>
                </div>

                <!-- Project Files Section -->
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Project Files</h3>
                        <a href="{{ route('filament.admin.resources.files.create') }}" class="inline-flex items-center px-3 py-1.5 text-md font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Upload File
                        </a>
                    </div>

                    @if($this->project->files->count() > 0)
                        <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->project->files as $file)
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $file->title }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Uploaded {{ $file->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded text-primary-700 bg-primary-50 hover:bg-primary-100 dark:text-primary-400 dark:bg-primary-900/50 dark:hover:bg-primary-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="mt-3 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-md text-sm text-gray-600 dark:text-gray-300">
                            No files uploaded yet.
                        </div>
                    @endif
                </div>

                <!-- Project Awards Section -->
                @if($this->project->awards && count($this->project->awards) > 0)
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Awards</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($this->project->awards as $award)
                                <div class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/70 dark:text-yellow-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                    {{ $award }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Final Grade Section -->
                @if($this->project->final_grade)
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Final Grade</h3>
                        <div class="mt-3">
                            <div class="inline-flex items-center px-4 py-2 rounded-md text-lg font-bold bg-green-100 text-green-800 dark:bg-green-900/70 dark:text-green-300">
                                {{ $this->project->final_grade }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Create Project Form -->
            @if($this->group)
                {{ $this->form }}
                <div class="mt-6 flex justify-end">
                    <x-filament::button type="submit" wire:click="createProject">
                        Create Project
                    </x-filament::button>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>