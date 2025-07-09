<!-- DEPRECATED: This file has been replaced by grading-component-optimized.blade.php -->
<!-- Please update your references to use the optimized version -->
<div class="space-y-6 w-full">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Project Grading Component (DEPRECATED)
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Evaluate project criteria with weighted scoring (1-5 scale)
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Summary Score</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400" id="headerSummaryScore">
                        0.0
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Average Score</div>
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400" id="totalScore">
                        0.00/5.0
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Weighted Score</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-white" id="weightedScore">
                        0.0
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                            <input 
                                type="number" 
                                name="documentation_score"
                                min="0" 
                                max="30" 
                                step="0.1"
                                class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center summary-input"
                                placeholder="0-30"
                            />
                        </td>
                        <td class="px-6 py-4 text-center">
                            <input 
                                type="number" 
                                name="prototype_score"
                                min="0" 
                                max="40" 
                                step="0.1"
                                class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center summary-input"
                                placeholder="0-40"
                            />
                        </td>
                        <td class="px-6 py-4 text-center">
                            <input 
                                type="number" 
                                name="presentation_score"
                                min="0" 
                                max="30" 
                                step="0.1"
                                class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-center summary-input"
                                placeholder="0-30"
                            />
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="w-20 px-2 py-1 text-sm bg-gray-100 dark:bg-gray-600 rounded-md border border-gray-300 dark:border-gray-600 text-center font-semibold text-gray-900 dark:text-white" id="summaryTotal">
                                0
                            </div>
                            <input type="hidden" name="total_summary_score" id="hiddenSummaryTotal" value="0" />
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
                    @php
$criteria = [
    'presentation_of_results' => [
        'name' => 'Presentation of Results',
        'weight' => 2,
        'descriptions' => [
            5 => 'Extremely clear, well-organized, and professionally presented results with excellent visual aids',
            4 => 'Clear and well-organized presentation with good visual aids and structure',
            3 => 'Adequately presented results with basic organization and visual support',
            2 => 'Poorly organized presentation with minimal visual aids and unclear structure',
            1 => 'Very poor presentation with no organization and no visual aids'
        ]
    ],
    'summary_of_findings' => [
        'name' => 'Summary of Findings',
        'weight' => 1,
        'descriptions' => [
            5 => 'Comprehensive and insightful summary that captures all key findings effectively',
            4 => 'Good summary that covers most important findings with clear explanations',
            3 => 'Adequate summary covering basic findings with some clarity',
            2 => 'Limited summary with unclear explanations and missing key findings',
            1 => 'Very poor summary with no clear findings or explanations'
        ]
    ],
    'conclusion' => [
        'name' => 'Conclusion',
        'weight' => 1,
        'descriptions' => [
            5 => 'Strong, well-supported conclusions that directly address the research objectives',
            4 => 'Good conclusions with adequate support and clear connections to objectives',
            3 => 'Basic conclusions with some support and relevance to objectives',
            2 => 'Weak conclusions with limited support and unclear connections',
            1 => 'Very poor conclusions with no support or relevance'
        ]
    ],
    'recommendation' => [
        'name' => 'Recommendation',
        'weight' => 1,
        'descriptions' => [
            5 => 'Excellent, actionable recommendations based on findings with clear implementation steps',
            4 => 'Good recommendations with practical value and clear basis in findings',
            3 => 'Adequate recommendations with some practical value and connection to findings',
            2 => 'Limited recommendations with unclear practical value',
            1 => 'Very poor recommendations with no practical value or basis'
        ]
    ],
    'content' => [
        'name' => 'Content',
        'weight' => 1,
        'descriptions' => [
            5 => 'Excellent content depth, accuracy, and relevance with comprehensive coverage',
            4 => 'Good content with adequate depth and accuracy',
            3 => 'Satisfactory content with basic coverage and accuracy',
            2 => 'Limited content with minimal depth and some inaccuracies',
            1 => 'Very poor content with no depth and significant inaccuracies'
        ]
    ],
    'project_output' => [
        'name' => 'Project Output',
        'weight' => 4,
        'descriptions' => [
            5 => 'Outstanding project output that exceeds expectations with innovative solutions',
            4 => 'Excellent project output meeting all requirements with quality execution',
            3 => 'Good project output meeting basic requirements with adequate execution',
            2 => 'Limited project output with minimal requirements met',
            1 => 'Very poor project output with requirements not met'
        ]
    ],
    'relevance_to_specialization' => [
        'name' => 'Relevance to Specialization',
        'weight' => 2,
        'descriptions' => [
            5 => 'Highly relevant to specialization with deep integration of specialized knowledge',
            4 => 'Good relevance with adequate integration of specialized concepts',
            3 => 'Adequate relevance with basic integration of specialization',
            2 => 'Limited relevance with minimal connection to specialization',
            1 => 'Very poor relevance with no connection to specialization'
        ]
    ],
    'project_demonstration' => [
        'name' => 'Project Demonstration',
        'weight' => 2,
        'descriptions' => [
            5 => 'Excellent demonstration with clear explanation and engaging presentation',
            4 => 'Good demonstration with adequate explanation and presentation',
            3 => 'Satisfactory demonstration with basic explanation',
            2 => 'Limited demonstration with unclear explanation',
            1 => 'Very poor demonstration with no clear explanation'
        ]
    ],
    'consistency' => [
        'name' => 'Consistency',
        'weight' => 3,
        'descriptions' => [
            5 => 'Excellent consistency in methodology, presentation, and quality throughout',
            4 => 'Good consistency with minor variations in quality',
            3 => 'Adequate consistency with some variations',
            2 => 'Limited consistency with significant variations',
            1 => 'Very poor consistency with major variations'
        ]
    ],
    'materials' => [
        'name' => 'Materials',
        'weight' => 1,
        'descriptions' => [
            5 => 'Excellent use of materials with high quality and appropriate selection',
            4 => 'Good use of materials with adequate quality and selection',
            3 => 'Satisfactory use of materials with basic quality',
            2 => 'Limited use of materials with poor quality',
            1 => 'Very poor use of materials with inappropriate selection'
        ]
    ],
    'manner_of_presentation' => [
        'name' => 'Manner of Presentation',
        'weight' => 1,
        'descriptions' => [
            5 => 'Outstanding presentation skills with excellent communication and confidence',
            4 => 'Good presentation skills with clear communication',
            3 => 'Adequate presentation skills with basic communication',
            2 => 'Limited presentation skills with unclear communication',
            1 => 'Very poor presentation skills with no clear communication'
        ]
    ],
    'presentation_of_project_overview' => [
        'name' => 'Presentation of Project Overview',
        'weight' => 1,
        'descriptions' => [
            5 => 'Excellent overview that clearly explains the project scope and objectives',
            4 => 'Good overview with adequate explanation of scope and objectives',
            3 => 'Satisfactory overview with basic explanation',
            2 => 'Limited overview with unclear explanation',
            1 => 'Very poor overview with no clear explanation'
        ]
    ]
];
                    @endphp

                    @foreach($criteria as $key => $criterion)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        <!-- Criteria Column -->
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $criterion['name'] }}
                                    </div>
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
                                    type="number" 
                                    name="criteria[{{ $key }}]"
                                    data-weight="{{ $criterion['weight'] }}"
                                    min="1" 
                                    max="5" 
                                    step="1"
                                    class="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white criteria-input"
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
                        <span class="text-sm font-medium text-gray-900 dark:text-white">20</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Weighted Score:</span>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400" id="summaryWeightedScore">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Average Score:</span>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400" id="averageScore">0.00</span>
                    </div>
                </div>
            </div>

            <!-- Progress -->
            <div class="space-y-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Progress</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Completed:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white" id="completedCount">0/12</span>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" id="progressBar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Grade:</span>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400" id="letterGrade">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Debug button for testing -->
    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg border border-yellow-200 dark:border-yellow-700">
        <button type="button" id="debugCopyBtn" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
            🐛 DEBUG: Copy Values to Hidden Fields
        </button>
        <button type="button" id="debugStructureBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 ml-2">
            🔍 DEBUG: Show Form Structure
        </button>
        <button type="button" id="debugJSONBtn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 ml-2">
            ✅ TEST: Generate JSON
        </button>
        <button type="button" onclick="window.testBasicDebug()" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 ml-2">
            🧪 BASIC: Test Script Loading
        </button>
        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-2">
            Click these buttons to debug the form structure and copy operation. Check the console for detailed logs.
        </p>
    </div>
    
    <!-- Note: Hidden form fields are handled by the ProjectResource form -->
</div>

<script>
console.log('🚀 Grading component script starting...');

// Make functions available globally first
window.gradingComponent = {};

// Define basic debug functions immediately as fallback
window.testBasicDebug = function() {
    console.log('🧪 Basic debug function working!');
    
    // Check if hidden field exists
    const hiddenField = document.querySelector('input[name="grading_data"]');
    console.log('Hidden field found:', hiddenField ? 'YES' : 'NO');
    
    if (hiddenField) {
        console.log('Hidden field details:', {
            name: hiddenField.name,
            type: hiddenField.type,
            value: hiddenField.value,
            form: hiddenField.form ? 'YES' : 'NO'
        });
        
        // Try to set a test value
        hiddenField.value = '{"test": "value"}';
        console.log('Set test value, new value:', hiddenField.value);
    }
    
    alert(`Basic debug working! Hidden field: ${hiddenField ? 'FOUND' : 'NOT FOUND'}. Check console for details.`);
    
    // Try to call the main functions if they exist
    if (typeof window.copyToHiddenFields === 'function') {
        console.log('✅ copyToHiddenFields is available');
        window.copyToHiddenFields();
    } else {
        console.log('❌ copyToHiddenFields is not available yet');
    }
};

// Fallback functions that will be overridden by the main ones
window.copyToHiddenFields = function() {
    console.log('⚠️ Using fallback copyToHiddenFields - main function not loaded yet');
    alert('Fallback function called - check console for details');
};

window.debugFormStructure = function() {
    console.log('⚠️ Using fallback debugFormStructure - main function not loaded yet');
    alert('Fallback function called - check console for details');
};

window.testJSONGeneration = function() {
    console.log('⚠️ Using fallback testJSONGeneration - main function not loaded yet');
    alert('Fallback function called - check console for details');
};

(function() {
    'use strict';
    
    console.log('📝 Inside IIFE - Loading grading component script...');
    
    // Define calculation functions
    function updateSummaryScores() {
        try {
            console.log('updateSummaryScores called');
            
            // Get input elements
            const docInput = document.querySelector('input[name="documentation_score"]');
            const protoInput = document.querySelector('input[name="prototype_score"]');
            const presInput = document.querySelector('input[name="presentation_score"]');
            
            console.log('Found inputs:', {
                docInput: docInput ? 'found' : 'not found',
                protoInput: protoInput ? 'found' : 'not found',
                presInput: presInput ? 'found' : 'not found'
            });
            
            // Get values with fallback to 0
            const documentationScore = docInput ? (parseFloat(docInput.value) || 0) : 0;
            const prototypeScore = protoInput ? (parseFloat(protoInput.value) || 0) : 0;
            const presentationScore = presInput ? (parseFloat(presInput.value) || 0) : 0;
            
            console.log('Parsed values:', {documentationScore, prototypeScore, presentationScore});
            
            // Calculate total
            const summaryTotal = documentationScore + prototypeScore + presentationScore;
            console.log('Calculated summaryTotal:', summaryTotal);
            
            // Update summary total display
            const summaryTotalElement = document.getElementById('summaryTotal');
            const hiddenSummaryTotal = document.getElementById('hiddenSummaryTotal');
            const headerSummaryScore = document.getElementById('headerSummaryScore');
            
            console.log('Found display elements:', {
                summaryTotalElement: summaryTotalElement ? 'found' : 'not found',
                hiddenSummaryTotal: hiddenSummaryTotal ? 'found' : 'not found',
                headerSummaryScore: headerSummaryScore ? 'found' : 'not found'
            });
            
            if (summaryTotalElement) {
                summaryTotalElement.textContent = summaryTotal.toFixed(1);
                console.log('Updated summaryTotalElement to:', summaryTotal.toFixed(1));
            }
            if (hiddenSummaryTotal) {
                hiddenSummaryTotal.value = summaryTotal;
                console.log('Updated hiddenSummaryTotal to:', summaryTotal);
            }
            
            // Note: Real-time form updates will be handled by copyToHiddenFields() on form submission
            if (headerSummaryScore) {
                headerSummaryScore.textContent = summaryTotal.toFixed(1);
                console.log('Updated headerSummaryScore to:', summaryTotal.toFixed(1));
            }
            
            console.log('Summary Updated:', {documentationScore, prototypeScore, presentationScore, summaryTotal});
            
        } catch (error) {
            console.error('Error in updateSummaryScores:', error);
        }
    }

    function updateScores() {
        try {
            console.log('updateScores called');
            
            const inputs = document.querySelectorAll('.criteria-input');
            console.log('Found criteria inputs:', inputs.length);
            
            let totalWeightedScore = 0;
            let completedCount = 0;
            let totalScore = 0;
            
            inputs.forEach((input, index) => {
                const value = parseInt(input.value) || 0;
                const weight = parseInt(input.dataset.weight) || 1;
                console.log(`Input ${index}:`, {name: input.name, value, weight});
                
                if (value > 0) {
                    completedCount++;
                    totalScore += value;
                    totalWeightedScore += value * weight;
                }
            });
            
            console.log('Calculation results:', {totalWeightedScore, completedCount, totalScore});
            
            const averageScore = completedCount > 0 ? (totalScore / completedCount).toFixed(2) : '0.00';
            const progress = (completedCount / 12) * 100;
            
            console.log('Derived values:', {averageScore, progress});
            
            // Update displays with null checks
            const weightedScore = document.getElementById('weightedScore');
            const summaryWeightedScore = document.getElementById('summaryWeightedScore');
            const totalScoreElement = document.getElementById('totalScore');
            const averageScoreElement = document.getElementById('averageScore');
            const completedCountElement = document.getElementById('completedCount');
            const progressBar = document.getElementById('progressBar');
            const letterGradeElement = document.getElementById('letterGrade');
            
            console.log('Found display elements:', {
                weightedScore: weightedScore ? 'found' : 'not found',
                summaryWeightedScore: summaryWeightedScore ? 'found' : 'not found',
                totalScoreElement: totalScoreElement ? 'found' : 'not found',
                averageScoreElement: averageScoreElement ? 'found' : 'not found',
                completedCountElement: completedCountElement ? 'found' : 'not found',
                progressBar: progressBar ? 'found' : 'not found',
                letterGradeElement: letterGradeElement ? 'found' : 'not found'
            });
            
            if (weightedScore) weightedScore.textContent = totalWeightedScore.toFixed(1);
            if (summaryWeightedScore) summaryWeightedScore.textContent = totalWeightedScore;
            if (totalScoreElement) totalScoreElement.textContent = averageScore + '/5.0';
            if (averageScoreElement) averageScoreElement.textContent = averageScore;
            if (completedCountElement) completedCountElement.textContent = `${completedCount}/12`;
            if (progressBar) progressBar.style.width = `${progress}%`;
            
            // Store total weighted score for form submission
            window.currentTotalWeightedScore = totalWeightedScore;
            
            // Note: Form fields will be updated by copyToHiddenFields() on form submission
            
            // Calculate letter grade
            const avgScore = parseFloat(averageScore);
            let letterGrade = '-';
            if (avgScore >= 4.5) letterGrade = 'A+';
            else if (avgScore >= 4.0) letterGrade = 'A';
            else if (avgScore >= 3.5) letterGrade = 'B+';
            else if (avgScore >= 3.0) letterGrade = 'B';
            else if (avgScore >= 2.5) letterGrade = 'C+';
            else if (avgScore >= 2.0) letterGrade = 'C';
            else if (avgScore >= 1.5) letterGrade = 'D';
            else if (avgScore >= 1.0) letterGrade = 'F';
            
            if (letterGradeElement) letterGradeElement.textContent = letterGrade;
            
            console.log('Criteria scores updated:', {totalWeightedScore, averageScore, completedCount});
            
        } catch (error) {
            console.error('Error in updateScores:', error);
        }
    }
    
    // Function to copy values from visual inputs to JSON hidden field
    function copyToHiddenFields() {
        try {
            console.log('=== COPYING VALUES TO JSON HIDDEN FIELD ===');
            
            // Find the single hidden field
            const hiddenField = document.querySelector('input[name="grading_data"][type="hidden"]');
            console.log('Found grading_data hidden field:', hiddenField ? 'YES' : 'NO');
            
            if (!hiddenField) {
                console.error('❌ Hidden grading_data field not found!');
                return;
            }
            
            // Collect all visual data
            const gradingData = {};
            
            // Copy summary scores
            const docInput = document.querySelector('input[name="documentation_score"]:not([type="hidden"])');
            const protoInput = document.querySelector('input[name="prototype_score"]:not([type="hidden"])');
            const presInput = document.querySelector('input[name="presentation_score"]:not([type="hidden"])');
            
            console.log('Visual inputs found:', {
                docInput: docInput ? `value: ${docInput.value}` : 'NOT FOUND',
                protoInput: protoInput ? `value: ${protoInput.value}` : 'NOT FOUND',
                presInput: presInput ? `value: ${presInput.value}` : 'NOT FOUND'
            });
            
            gradingData.documentation_score = docInput ? (parseFloat(docInput.value) || 0) : 0;
            gradingData.prototype_score = protoInput ? (parseFloat(protoInput.value) || 0) : 0;
            gradingData.presentation_score = presInput ? (parseFloat(presInput.value) || 0) : 0;
            gradingData.total_summary_score = gradingData.documentation_score + gradingData.prototype_score + gradingData.presentation_score;
            
            console.log('✓ Summary scores collected:', {
                documentation_score: gradingData.documentation_score,
                prototype_score: gradingData.prototype_score,
                presentation_score: gradingData.presentation_score,
                total_summary_score: gradingData.total_summary_score
            });
            
            // Copy criteria scores
            const criteriaInputs = document.querySelectorAll('.criteria-input');
            console.log('Found criteria inputs:', criteriaInputs.length);
            
            criteriaInputs.forEach((input, index) => {
                const criteriaName = input.name.replace('criteria[', '').replace(']', '');
                const value = parseInt(input.value) || 0;
                gradingData[criteriaName] = value;
                console.log(`✓ Criteria ${index}: ${criteriaName} = ${value}`);
            });
            
            // Add total score from global variable
            if (window.currentTotalWeightedScore !== undefined) {
                gradingData.total_score = window.currentTotalWeightedScore;
                console.log('✓ Total weighted score:', window.currentTotalWeightedScore);
            } else {
                gradingData.total_score = 0;
                console.log('⚠️ currentTotalWeightedScore not set, using 0');
            }
            
            // Convert to JSON and store
            const jsonData = JSON.stringify(gradingData);
            hiddenField.value = jsonData;
            
            console.log('=== FINAL GRADING DATA JSON ===');
            console.log(jsonData);
            console.log('=== COPY OPERATION COMPLETE ===');
            
        } catch (error) {
            console.error('Error copying values to JSON hidden field:', error);
        }
    }
    
    // Use event delegation for better modal compatibility
    function setupEventListeners() {
        console.log('Setting up event delegation...');
        
        // Event delegation for summary inputs
        document.addEventListener('input', function(e) {
            console.log('Input event detected on:', e.target.name, 'Classes:', e.target.classList.toString());
            if (e.target.classList.contains('summary-input')) {
                console.log('Summary input detected, calling updateSummaryScores');
                updateSummaryScores();
                // Also immediately update the hidden field
                setTimeout(copyToHiddenFields, 100);
            }
        });
        
        document.addEventListener('change', function(e) {
            console.log('Change event detected on:', e.target.name, 'Classes:', e.target.classList.toString());
            if (e.target.classList.contains('summary-input')) {
                console.log('Summary input detected, calling updateSummaryScores');
                updateSummaryScores();
                // Also immediately update the hidden field
                setTimeout(copyToHiddenFields, 100);
            }
        });
        
        // Event delegation for criteria inputs
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('criteria-input')) {
                console.log('Criteria input detected, calling updateScores');
                updateScores();
                // Also immediately update the hidden field
                setTimeout(copyToHiddenFields, 100);
            }
        });
        
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('criteria-input')) {
                console.log('Criteria input detected, calling updateScores');
                updateScores();
                // Also immediately update the hidden field
                setTimeout(copyToHiddenFields, 100);
            }
        });
        
        // Add form submission handler
        document.addEventListener('submit', function(e) {
            // Check if this is a form submission that includes our grading data
            if (e.target.querySelector('input[name="grading_data"][type="hidden"]')) {
                console.log('Form submission detected, copying values...');
                copyToHiddenFields();
            }
        });
        
        console.log('Event delegation setup complete');
    }
    
    // Initialize with multiple strategies
    function initialize() {
        console.log('Initializing grading component...');
        
        // Setup event delegation immediately
        setupEventListeners();
        
        // Initial calculations
        function runInitialCalculations() {
            console.log('Running initial calculations...');
            
            // Check if elements exist
            const summaryInputs = document.querySelectorAll('.summary-input');
            const criteriaInputs = document.querySelectorAll('.criteria-input');
            console.log('Found summary inputs:', summaryInputs.length);
            console.log('Found criteria inputs:', criteriaInputs.length);
            
            // List all found elements
            summaryInputs.forEach((input, index) => {
                console.log(`Summary input ${index}:`, input.name, input.value);
            });
            
            criteriaInputs.forEach((input, index) => {
                console.log(`Criteria input ${index}:`, input.name, input.value);
            });
            
            updateSummaryScores();
            updateScores();
            console.log('Initial calculations complete');
        }
        
        // Try multiple initialization strategies
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runInitialCalculations);
        } else {
            runInitialCalculations();
        }
        
        // Also try with timeouts for modal contexts
        setTimeout(runInitialCalculations, 100);
        setTimeout(runInitialCalculations, 500);
        setTimeout(runInitialCalculations, 1000);
    }
    
    // Debug form structure
    function debugFormStructure() {
        console.log('=== FORM STRUCTURE DEBUG ===');
        
        // Find all forms
        const forms = document.querySelectorAll('form');
        console.log('Found forms:', forms.length);
        
        forms.forEach((form, index) => {
            console.log(`Form ${index}:`, form);
            
            // Find all inputs in this form
            const inputs = form.querySelectorAll('input, textarea, select');
            console.log(`  Inputs in form ${index}:`, inputs.length);
            
            inputs.forEach((input, inputIndex) => {
                console.log(`    Input ${inputIndex}: ${input.type} - ${input.name} = "${input.value}"`);
            });
        });
        
        // Find the grading_data hidden field
        const gradingDataField = document.querySelector('input[name="grading_data"][type="hidden"]');
        console.log('=== GRADING DATA FIELD CHECK ===');
        console.log('grading_data field found:', gradingDataField ? 'YES' : 'NO');
        if (gradingDataField) {
            console.log('Current grading_data value:', gradingDataField.value);
        }
        
        // Find remarks field
        const remarksField = document.querySelector('textarea[name="remarks"]');
        console.log('remarks field found:', remarksField ? 'YES' : 'NO');
        if (remarksField) {
            console.log('Current remarks value:', remarksField.value);
        }
        
        console.log('=== VISUAL INPUTS CHECK ===');
        const summaryInputs = document.querySelectorAll('.summary-input');
        const criteriaInputs = document.querySelectorAll('.criteria-input');
        
        console.log('Summary inputs:', summaryInputs.length);
        summaryInputs.forEach((input, index) => {
            console.log(`  ${index}: ${input.name} = "${input.value}"`);
        });
        
        console.log('Criteria inputs:', criteriaInputs.length);
        criteriaInputs.forEach((input, index) => {
            console.log(`  ${index}: ${input.name} = "${input.value}"`);
        });
    }
    
    // Form submission handler - multiple approaches
    function setupFormInterception() {
        console.log('Setting up multiple form submission handlers...');
        
        // Approach 1: Standard form submit listener
        document.addEventListener('submit', function(e) {
            console.log('📝 Form submission detected via document listener');
            copyToHiddenFields();
        });
        
        // Approach 2: Look for Filament form submit buttons
        function setupFilamentButtonListener() {
            const submitButtons = document.querySelectorAll('button[type="submit"], button[wire\\:click*="mountedActionsData"]');
            console.log('Found potential submit buttons:', submitButtons.length);
            
            submitButtons.forEach((btn, index) => {
                console.log(`Adding listener to button ${index}:`, btn);
                btn.addEventListener('click', function(e) {
                    console.log('📝 Submit button clicked:', btn);
                    setTimeout(() => {
                        copyToHiddenFields();
                    }, 100);
                });
            });
        }
        
        // Approach 3: Periodic checking before submission
        let lastCheck = 0;
        function periodicCheck() {
            const now = Date.now();
            if (now - lastCheck > 2000) { // Check every 2 seconds
                const gradingField = document.querySelector('input[name="grading_data"]');
                if (gradingField && (!gradingField.value || gradingField.value === '')) {
                    console.log('🔄 Periodic check: updating grading data');
                    copyToHiddenFields();
                }
                lastCheck = now;
            }
        }
        
        // Setup all approaches
        setupFilamentButtonListener();
        setInterval(periodicCheck, 2000);
        
        // Also setup listeners with delays
        setTimeout(setupFilamentButtonListener, 1000);
        setTimeout(setupFilamentButtonListener, 3000);
        
        // Approach 4: Mutation Observer as final safety net
        function setupMutationObserver() {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    // Check if form is being submitted (look for loading states, etc.)
                    if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                        console.log('🔍 Form element disabled (likely submitting)');
                        copyToHiddenFields();
                    }
                });
            });
            
            // Observe the document for attribute changes
            observer.observe(document, {
                attributes: true,
                attributeFilter: ['disabled', 'aria-busy'],
                subtree: true
            });
            
            console.log('👁️ MutationObserver setup complete');
        }
        
        setTimeout(setupMutationObserver, 500);
    }
    
    // Test function to generate JSON data
    function testJSONGeneration() {
        console.log('=== TESTING JSON GENERATION ===');
        
        // First, copy the values
        copyToHiddenFields();
        
        // Then check what's in the hidden field
        const hiddenField = document.querySelector('input[name="grading_data"][type="hidden"]');
        if (hiddenField) {
            console.log('Hidden field value:', hiddenField.value);
            
            // Try to parse it back
            try {
                const parsed = JSON.parse(hiddenField.value);
                console.log('Parsed JSON:', parsed);
                
                // Show summary
                console.log('Summary:', {
                    documentation_score: parsed.documentation_score,
                    prototype_score: parsed.prototype_score,
                    presentation_score: parsed.presentation_score,
                    total_summary_score: parsed.total_summary_score
                });
                
                // Count filled criteria
                const criteriaCount = Object.keys(parsed).filter(key => 
                    key.startsWith('presentation_') || 
                    key.startsWith('summary_') || 
                    key.includes('conclusion') || 
                    key.includes('recommendation') || 
                    key.includes('content') || 
                    key.includes('project_') || 
                    key.includes('relevance_') || 
                    key.includes('consistency') || 
                    key.includes('materials') || 
                    key.includes('manner_')
                ).length;
                
                console.log(`Found ${criteriaCount} criteria fields`);
                
                alert(`JSON generated successfully! Check console for details.\n\nSummary Total: ${parsed.total_summary_score}\nCriteria Count: ${criteriaCount}\nTotal Score: ${parsed.total_score}`);
                
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                alert('Failed to parse JSON. Check console for details.');
            }
        } else {
            console.error('Hidden field not found!');
            alert('Hidden field not found!');
        }
    }
    
    // Try Livewire-specific approach
    function setupLivewireInterception() {
        console.log('Setting up Livewire interception...');
        
        // Listen for Livewire requests
        document.addEventListener('livewire:init', function () {
            console.log('Livewire initialized');
            
            Livewire.hook('request', ({ component, commit, respond, succeed, fail }) => {
                console.log('Livewire request intercepted');
                
                // Before sending request, copy our values
                copyToHiddenFields();
                
                succeed(({ status, response }) => {
                    console.log('Livewire request succeeded');
                });
            });
        });
        
        // Also try to listen for any wire:click events
        document.addEventListener('click', function(e) {
            if (e.target.hasAttribute('wire:click') || e.target.closest('[wire:click]')) {
                console.log('Livewire click detected, copying values...');
                setTimeout(() => copyToHiddenFields(), 100);
            }
        });
    }
    
    // Make functions available globally for debugging
    console.log('📤 Exposing functions to window (overriding fallbacks)...');
    window.copyToHiddenFields = copyToHiddenFields;
    window.debugFormStructure = debugFormStructure;
    window.testJSONGeneration = testJSONGeneration;
    
    console.log('🔄 Overrode fallback functions with real implementations');
    
    // Also store in the namespace
    window.gradingComponent.copyToHiddenFields = copyToHiddenFields;
    window.gradingComponent.debugFormStructure = debugFormStructure;
    window.gradingComponent.testJSONGeneration = testJSONGeneration;
    window.gradingComponent.updateSummaryScores = updateSummaryScores;
    window.gradingComponent.updateScores = updateScores;
    
    console.log('✅ Functions exposed to window:', {
        copyToHiddenFields: typeof window.copyToHiddenFields,
        debugFormStructure: typeof window.debugFormStructure,
        testJSONGeneration: typeof window.testJSONGeneration
    });
    
    // Setup debug button event listeners
    function setupDebugButtons() {
        console.log('🔘 Setting up debug button event listeners...');
        
        // Use timeouts to ensure buttons exist
        setTimeout(() => {
            const copyBtn = document.getElementById('debugCopyBtn');
            const structureBtn = document.getElementById('debugStructureBtn');
            const jsonBtn = document.getElementById('debugJSONBtn');
            
            console.log('Debug buttons found:', {
                copyBtn: copyBtn ? 'YES' : 'NO',
                structureBtn: structureBtn ? 'YES' : 'NO', 
                jsonBtn: jsonBtn ? 'YES' : 'NO'
            });
            
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    console.log('🐛 Copy button clicked');
                    copyToHiddenFields();
                });
            }
            
            if (structureBtn) {
                structureBtn.addEventListener('click', function() {
                    console.log('🔍 Structure button clicked');
                    debugFormStructure();
                });
            }
            
            if (jsonBtn) {
                jsonBtn.addEventListener('click', function() {
                    console.log('✅ JSON button clicked');
                    testJSONGeneration();
                });
            }
        }, 500);
        
        // Try again with longer delays for modal contexts
        setTimeout(() => {
            if (!document.getElementById('debugCopyBtn')) {
                console.log('🔄 Retrying debug button setup...');
                const copyBtn = document.getElementById('debugCopyBtn');
                const structureBtn = document.getElementById('debugStructureBtn');
                const jsonBtn = document.getElementById('debugJSONBtn');
                
                if (copyBtn && !copyBtn.hasAttribute('data-listener-added')) {
                    copyBtn.addEventListener('click', () => copyToHiddenFields());
                    copyBtn.setAttribute('data-listener-added', 'true');
                }
                if (structureBtn && !structureBtn.hasAttribute('data-listener-added')) {
                    structureBtn.addEventListener('click', () => debugFormStructure());
                    structureBtn.setAttribute('data-listener-added', 'true');
                }
                if (jsonBtn && !jsonBtn.hasAttribute('data-listener-added')) {
                    jsonBtn.addEventListener('click', () => testJSONGeneration());
                    jsonBtn.setAttribute('data-listener-added', 'true');
                }
            }
        }, 1500);
    }
    
    // Start initialization
    console.log('🔧 Starting initialization...');
    initialize();
    setupFormInterception();
    setupLivewireInterception();
    setupDebugButtons();
    
    console.log('✨ Grading component fully loaded and initialized!');
    
})();

console.log('🎯 Script execution completed. Final check - Functions available:', {
    copyToHiddenFields: typeof window.copyToHiddenFields,
    debugFormStructure: typeof window.debugFormStructure,
    testJSONGeneration: typeof window.testJSONGeneration
});
</script>
