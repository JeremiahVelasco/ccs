<div class="space-y-6 w-full">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Group Grading
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Evaluate project criteria with weighted scoring (1-5 scale)
                </p>
            </div>
            <div class="flex flex-col items-center space-x-4">
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Summary Score</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400" id="headerSummaryScore">
                        {{ number_format(floatval($data['documentation_score'] ?? 0) + floatval($data['prototype_score'] ?? 0) + floatval($data['presentation_score'] ?? 0), 1) }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Average Score</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400" id="totalScore">
                                                        @php
$criteriaValues = collect($criteria['detailed'])->keys()->map(fn($key) => floatval($data[$key] ?? 0))->filter(fn($val) => $val > 0);
$avgScore = $criteriaValues->count() > 0 ? $criteriaValues->avg() : 0;
                                @endphp
                        {{ number_format($avgScore, 2) }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Weighted Score</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-white" id="weightedScore">
                        @php
$weightedScore = 0;
foreach ($criteria['detailed'] as $key => $criterion) {
    $weightedScore += floatval($data[$key] ?? 0) * $criterion['weight'];
}
                        @endphp
                        {{ number_format($weightedScore, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form wire:submit="submitGrade">
        <!-- Summary Scoring Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Documentation
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Prototype
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Presentation
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Sum
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Description Row -->
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
                                Documentation [Chapter 4 (15 pts) + Chapter 5(5 pts) + Chapter 6 (5 pts)+ Others (5pts)] + Prototype (40 points) + Presentation (30 pts)
                            </td>
                        </tr>
                        <!-- Score Input Row -->
                        <tr>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center">
                                    <input 
                                        wire:model.live="data.documentation_score" 
                                        type="number" 
                                        min="0" 
                                        max="30" 
                                        step="0.1"
                                        class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center"
                                        placeholder="0-30"
                                    />
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center">
                                    <input 
                                        wire:model.live="data.prototype_score" 
                                        type="number" 
                                        min="0" 
                                        max="40" 
                                        step="0.1"
                                        class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center"
                                        placeholder="0-40"
                                    />
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center">
                                    <input 
                                        wire:model.live="data.presentation_score" 
                                        type="number" 
                                        min="0" 
                                        max="30" 
                                        step="0.1"
                                        class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center"
                                        placeholder="0-30"
                                    />
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="w-20 px-2 py-1 text-sm bg-gray-100 dark:bg-gray-600 rounded-md border border-gray-300 dark:border-gray-600 text-center font-semibold text-gray-900 dark:text-white">
                                    {{ number_format(floatval($data['documentation_score'] ?? 0) + floatval($data['prototype_score'] ?? 0) + floatval($data['presentation_score'] ?? 0), 1) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Grading Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Table Header -->
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Criteria
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Weight
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                5 - Very Acceptable
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                4 - Acceptable
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                3 - Fair
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                2 - Needs Improvement
                            </th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                1 - Poor
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Score
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($criteria['detailed'] as $key => $criterion)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <!-- Criteria Column -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $criterion['name'] }}
                                        </div>
                                        @error($key)
                                            <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Weight Column -->
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100">
                                    ×{{ $criterion['weight'] }}
                                </span>
                            </td>
                            
                            <!-- Description Columns (5-1) -->
                            @for($score = 5; $score >= 1; $score--)
                            <td class="px-4 py-4 text-center">
                                <div class="text-xs text-gray-600 dark:text-gray-400 leading-tight max-w-xs">
                                    {{ $criterion['descriptions'][$score] }}
                                </div>
                            </td>
                            @endfor
                            
                            <!-- Score Column -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <input 
                                        wire:model.live="data.{{ $key }}" 
                                        type="number" 
                                        min="0" 
                                        max="5" 
                                        step="1"
                                        class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center"
                                        placeholder="1-5"
                                    />
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Score Summary -->
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Score Summary</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Weight:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $totalWeight }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Weighted Score:</span>
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                @php
$weightedScore = 0;
foreach ($criteria['detailed'] as $key => $criterion) {
    $weightedScore += floatval($data[$key] ?? 0) * $criterion['weight'];
}
                                @endphp
                                {{ number_format($weightedScore) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Average Score:</span>
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                                        @php
$criteriaValues = collect($criteria['detailed'])->keys()->map(fn($key) => floatval($data[$key] ?? 0))->filter(fn($val) => $val > 0);
$avgScore = $criteriaValues->count() > 0 ? $criteriaValues->avg() : 0;
                        @endphp
                                {{ number_format($avgScore, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Progress -->
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Progress</h3>
                    <div class="space-y-2">
                        @php
$completedCount = collect($criteria['detailed'])->keys()->filter(fn($key) => floatval($data[$key] ?? 0) > 0)->count();
$totalCriteria = count($criteria['detailed']);
$progress = $totalCriteria > 0 ? ($completedCount / $totalCriteria) * 100 : 0;
                        @endphp
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Completed:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $completedCount }}/{{ $totalCriteria }}</span>
                        </div>
                        <div class="mt-2">
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Grade:</span>
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                                @php
$criteriaValues = collect($criteria['detailed'])->keys()->map(fn($key) => floatval($data[$key] ?? 0))->filter(fn($val) => $val > 0);
$avgScore = $criteriaValues->count() > 0 ? $criteriaValues->avg() : 0;
$gradeLetter = '-';
if ($avgScore >= 4.5)
    $gradeLetter = 'A+';
elseif ($avgScore >= 4.0)
    $gradeLetter = 'A';
elseif ($avgScore >= 3.5)
    $gradeLetter = 'B+';
elseif ($avgScore >= 3.0)
    $gradeLetter = 'B';
elseif ($avgScore >= 2.5)
    $gradeLetter = 'C+';
elseif ($avgScore >= 2.0)
    $gradeLetter = 'C';
elseif ($avgScore >= 1.5)
    $gradeLetter = 'D';
elseif ($avgScore >= 1.0)
    $gradeLetter = 'F';
                                @endphp
                                {{ $gradeLetter }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-2">
            <a href="{{ url('/projects') }}" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-white border border-gray-300 rounded-md shadow-sm hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                style="background-color: #ef4444 !important; border-color: #ef4444 !important;"
                onmouseover="this.style.backgroundColor='#f11f1f'"
                onmouseout="this.style.backgroundColor='#ef4444'">
                Cancel
            </a>
            <button type="submit" 
                class="px-4 py-2 text-sm font-medium text-white bg-green-700 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                style="background-color: #15803d !important; border-color: #059669 !important;"
                onmouseover="this.style.backgroundColor='#047857'"
                onmouseout="this.style.backgroundColor='#059669'">
                Save Grade
            </button>
        </div>
    </form>
</div> 