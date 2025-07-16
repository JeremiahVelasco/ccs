<div>
    <div class="space-y-6">
        <!-- Project Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Individual Grading</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Project: <span class="font-medium">{{ $project->title }}</span>
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Group: <span class="font-medium">{{ $project->group->name }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Weight</p>
                    <p class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $totalWeight }}</p>
                </div>
            </div>
        </div>

    @if($groupMembers->isEmpty())
        <div class="bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No Group Members</h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>This project's group has no members to evaluate.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
                <!-- Individual Grading Table -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <!-- Criteria Column -->
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Criteria & Weight
                                    </th>

                                    <!-- Score Description Columns -->
                                    @foreach([5, 4, 3, 2, 1] as $score)
                                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider bg-gray-100 dark:bg-gray-600">
                                            {{ $score }}
                                        </th>
                                    @endforeach

                                    <!-- Member Columns -->
                                    @foreach($groupMembers as $member)
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <div class="flex flex-col items-center">
                                                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold mb-1">
                                                    {{ substr($member->name, 0, 1) }}
                                                </div>
                                                <span class="text-xs">{{ \Illuminate\Support\Str::limit($member->name, 12) }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($criteria as $criterionKey => $criterion)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <!-- Criteria & Weight -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $criterion['name'] }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Weight: {{ $criterion['weight'] }}
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Score Descriptions -->
                                        @foreach([5, 4, 3, 2, 1] as $score)
                                            <td class="px-3 py-4 text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-750">
                                                <div class="max-w-32">
                                                    {{ \Illuminate\Support\Str::limit($criterion['descriptions'][$score], 50) }}
                                                </div>
                                            </td>
                                        @endforeach

                                        <!-- Member Score Inputs -->
                                        @foreach($groupMembers as $member)
                                            <td class="px-4 py-4 text-center">
                                                <div class="flex flex-col items-center space-y-2">
                                                    <select 
                                                        wire:change="updateScore({{ $member->id }}, '{{ $criterionKey }}', $event.target.value)"
                                                        class="w-16 text-center border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:text-white text-sm"
                                                    >
                                                        <option value="0" {{ ($scores[$member->id][$criterionKey] ?? 0) == 0 ? 'selected' : '' }}>-</option>
                                                        @foreach([1, 2, 3, 4, 5] as $score)
                                                            <option value="{{ $score }}" {{ ($scores[$member->id][$criterionKey] ?? 0) == $score ? 'selected' : '' }}>
                                                                {{ $score }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Member Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    @foreach($groupMembers as $member)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</h4>
                                </div>
                            </div>

                            @if(isset($scores[$member->id]) && !empty(array_filter($scores[$member->id])))
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Average Score:</span>
                                        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                                            {{ $this->calculateMemberAverage($member->id) }}/5
                                        </span>
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Completed: {{ count(array_filter($scores[$member->id], fn($score) => $score > 0)) }}/{{ count($criteria) }} criteria
                                    </div>

                                    <!-- Progress Bar -->
                                    @php
                    $completed = count(array_filter($scores[$member->id], fn($score) => $score > 0));
                    $total = count($criteria);
                    $percentage = $total > 0 ? ($completed / $total) * 100 : 0;
                                    @endphp
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                        <div class="bg-primary-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">
                                    No scores assigned
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4 gap-x-2">
                    <a href="{{ url('/projects') }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </a>
                    <button 
                        wire:click="submitIndividualGrades"
                        type="button"
        class="px-4 py-2 text-sm font-medium text-white bg-green-700 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        style="background-color: #15803d !important; border-color: #059669 !important;"
                        onmouseover="this.style.backgroundColor='#047857'"
                        onmouseout="this.style.backgroundColor='#059669'">
                        Submit Individual Grades
                    </button>
                </div>
    @endif
    </div>

    <!-- Criteria Descriptions Modal (Optional Enhancement) -->
    <div x-data="{ showDescriptions: false }" class="fixed bottom-4 right-4">
        <button 
            @click="showDescriptions = true"
            class="bg-primary-600 hover:bg-primary-700 text-white rounded-full p-3 shadow-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </button>
        
        <!-- Modal -->
        <div x-show="showDescriptions" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @click.away="showDescriptions = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Grading Criteria Descriptions</h3>
                        <button @click="showDescriptions = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        @foreach($criteria as $criterionKey => $criterion)
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-2">
                                    {{ $criterion['name'] }} (Weight: {{ $criterion['weight'] }})
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-2 text-xs">
                                    @foreach([5, 4, 3, 2, 1] as $score)
                                        <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                            <div class="font-medium text-primary-600 dark:text-primary-400">{{ $score }}</div>
                                            <div class="text-gray-600 dark:text-gray-300">{{ $criterion['descriptions'][$score] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div> 