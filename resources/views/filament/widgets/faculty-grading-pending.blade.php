<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Projects Pending Grading ({{ $totalPending }})
        </x-slot>

        @if($pendingProjects->count() > 0)
            <div class="space-y-4">
                @foreach($pendingProjects as $project)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    @if($project->logo)
                                        <img src="{{ Storage::url($project->logo) }}" alt="{{ $project->title }}" class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <x-heroicon-o-folder class="w-5 h-5 text-gray-500" />
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $project->title }}</h4>
                                        <p class="text-sm text-gray-600">{{ $project->group->name ?? 'No Group' }}</p>
                                    </div>
                                </div>
                                
                                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                    <span>Leader: {{ $project->group->leader->name ?? 'Not assigned' }}</span>
                                    <span>•</span>
                                    <span>Progress: {{ $project->progress ?? 0 }}%</span>
                                    <span>•</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($project->status === 'Done') bg-green-100 text-green-800
                                        @elseif($project->status === 'For Review') bg-yellow-100 text-yellow-800
                                        @elseif($project->status === 'In Progress') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $project->status }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="{{ route('filament.admin.pages.project', ['project' => $project->id]) }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-heroicon-o-eye class="w-4 h-4 mr-1" />
                                    View
                                </a>
                                <a href="{{ route('filament.admin.pages.project', ['project' => $project->id]) }}?tab=grading" 
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-heroicon-o-star class="w-4 h-4 mr-1" />
                                    Grade
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                @if($totalPending > 5)
                    <div class="text-center pt-4 border-t">
                        <p class="text-sm text-gray-500">
                            Showing 5 of {{ $totalPending }} pending projects.
                            <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">View all</a>
                        </p>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-6">
                <x-heroicon-o-check-circle class="mx-auto h-12 w-12 text-green-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">All Caught Up!</h3>
                <p class="mt-1 text-sm text-gray-500">You have no projects pending grading at the moment.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget> 