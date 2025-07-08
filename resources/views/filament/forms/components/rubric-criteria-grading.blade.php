<div class="space-y-6">
    <!-- Rubric Header -->
    <div class="bg-green-700 text-white p-4 rounded-t-lg">
        <h3 class="text-lg font-bold text-center">{{ strtoupper($rubric->name) }}</h3>
    </div>

    @if($rubric->isIndividualType())
        <!-- Individual Rubric Table Format -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-400">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 p-2 text-left font-semibold">CRITERIA AND WEIGHT</th>
                        <th class="border border-gray-400 p-2 text-center font-semibold w-20">5<br><small>VERY ACCEPTABLE</small></th>
                        <th class="border border-gray-400 p-2 text-center font-semibold w-20">4<br><small>ACCEPTABLE</small></th>
                        <th class="border border-gray-400 p-2 text-center font-semibold w-20">3<br><small>FAIR</small></th>
                        <th class="border border-gray-400 p-2 text-center font-semibold w-20">2<br><small>NEEDS IMPROVEMENT</small></th>
                        <th class="border border-gray-400 p-2 text-center font-semibold w-20">1<br><small>POOR</small></th>
                        <th class="border border-gray-400 p-2 text-center font-semibold bg-blue-100 w-24">SCORE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rubric->sections as $section)
                        @foreach($section->criteria as $criterion)
                            <tr>
                                <td class="border border-gray-400 p-2">
                                    <div class="font-semibold">{{ $criterion->name }}</div>
                                    <div class="text-sm text-gray-600">(×{{ $criterion->weight }})</div>
                                </td>
                                @foreach($criterion->scaleLevels->sortByDesc('points') as $level)
                                    <td class="border border-gray-400 p-2 text-xs">
                                        {{ $level->description }}
                                    </td>
                                @endforeach
                                <td class="border border-gray-400 p-2 text-center bg-blue-50">
                                    <input type="number" 
                                           name="criteria[{{ $criterion->id }}][score]" 
                                           min="0" 
                                           max="{{ $criterion->max_points * $criterion->weight }}"
                                           class="w-16 h-8 text-center border border-gray-300 rounded font-bold text-lg"
                                           placeholder="0"
                                           data-criterion-id="{{ $criterion->id }}"
                                           onchange="updateTotals()">
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    <tr class="bg-gray-100 font-bold">
                        <td class="border border-gray-400 p-2 text-right" colspan="6">TOTAL</td>
                        <td class="border border-gray-400 p-2 text-center bg-blue-100">
                            <span id="total_score" class="text-lg font-bold">0</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <!-- Group Rubric Format -->
        <div class="space-y-6">
            <!-- Summary Header -->
            <div class="bg-green-700 text-white p-3">
                <table class="w-full text-center">
                    <tr>
                        <td class="font-semibold">DOCUMENTATION</td>
                        <td class="font-semibold">PROTOTYPE</td>
                        <td class="font-semibold">PRESENTATION</td>
                        <td class="font-semibold">SUM</td>
                    </tr>
                    <tr class="text-sm">
                        <td>{{ $rubric->sections->where('name', 'Documentation')->first()->total_points ?? 0 }} Points</td>
                        <td>{{ $rubric->sections->where('name', 'Prototype')->first()->total_points ?? 0 }} Points</td>
                        <td>{{ $rubric->sections->where('name', 'Presentation')->first()->total_points ?? 0 }} Points</td>
                        <td>{{ $rubric->total_points }} Points</td>
                    </tr>
                    <tr class="text-2xl font-bold">
                        <td class="bg-gray-200 text-green-700 p-2" id="documentation_total">0</td>
                        <td class="bg-gray-200 text-green-700 p-2" id="prototype_total">0</td>
                        <td class="bg-gray-200 text-green-700 p-2" id="presentation_total">0</td>
                        <td class="bg-green-500 text-white p-2" id="grand_total">0</td>
                    </tr>
                </table>
            </div>

            @foreach($rubric->sections as $section)
                <div class="border border-gray-400">
                    <!-- Section Header -->
                    <div class="bg-green-700 text-white p-3 font-bold">
                        {{ strtoupper($section->name) }} ({{ $section->total_points }} POINTS)
                    </div>

                    <!-- Section Criteria Table -->
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-400 p-2 text-left font-semibold">CRITERIA AND WEIGHT</th>
                                <th class="border border-gray-400 p-2 text-center font-semibold w-20">5<br><small>VERY ACCEPTABLE</small></th>
                                <th class="border border-gray-400 p-2 text-center font-semibold w-20">4<br><small>ACCEPTABLE</small></th>
                                <th class="border border-gray-400 p-2 text-center font-semibold w-20">3<br><small>FAIR</small></th>
                                <th class="border border-gray-400 p-2 text-center font-semibold w-20">2<br><small>NEEDS IMPROVEMENT</small></th>
                                <th class="border border-gray-400 p-2 text-center font-semibold w-20">1<br><small>POOR</small></th>
                                <th class="border border-gray-400 p-2 text-center font-semibold bg-blue-100 w-24">SCORE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section->criteria as $criterion)
                                <tr>
                                    <td class="border border-gray-400 p-2">
                                        <div class="font-semibold">{{ $criterion->name }}</div>
                                        <div class="text-sm text-gray-600">(×{{ $criterion->weight }})</div>
                                    </td>
                                    @foreach($criterion->scaleLevels->sortByDesc('points') as $level)
                                        <td class="border border-gray-400 p-2 text-xs">
                                            {{ $level->description }}
                                        </td>
                                    @endforeach
                                    <td class="border border-gray-400 p-2 text-center bg-blue-50">
                                        <input type="number" 
                                               name="criteria[{{ $criterion->id }}][score]" 
                                               min="0" 
                                               max="{{ $criterion->max_points * $criterion->weight }}"
                                               class="w-16 h-8 text-center border border-gray-300 rounded font-bold text-lg"
                                               placeholder="0"
                                               data-criterion-id="{{ $criterion->id }}"
                                               data-section="{{ strtolower($section->name) }}"
                                               onchange="updateTotals()">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Comments Section -->
    <div class="mt-6 border border-gray-400 rounded">
        <div class="bg-gray-100 p-3 font-semibold border-b border-gray-400">
            ADDITIONAL COMMENTS
        </div>
        <div class="p-3">
            <textarea name="overall_comments" 
                      rows="4" 
                      class="w-full border border-gray-300 rounded p-2"
                      placeholder="Enter any additional comments or observations..."></textarea>
        </div>
    </div>
</div>

<script>
function updateTotals() {
    @if($rubric->isIndividualType())
        // Individual rubric total calculation
        let total = 0;
        document.querySelectorAll('input[name*="[score]"]').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total_score').textContent = total;
    @else
        // Group rubric section totals
        let documentationTotal = 0;
        let prototypeTotal = 0;
        let presentationTotal = 0;
        
        document.querySelectorAll('input[name*="[score]"]').forEach(input => {
            const value = parseFloat(input.value) || 0;
            const section = input.getAttribute('data-section');
            
            if (section === 'documentation') {
                documentationTotal += value;
            } else if (section === 'prototype') {
                prototypeTotal += value;
            } else if (section === 'presentation') {
                presentationTotal += value;
            }
        });
        
        document.getElementById('documentation_total').textContent = documentationTotal;
        document.getElementById('prototype_total').textContent = prototypeTotal;
        document.getElementById('presentation_total').textContent = presentationTotal;
        document.getElementById('grand_total').textContent = documentationTotal + prototypeTotal + presentationTotal;
    @endif
}

// Initialize totals on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotals();
});
</script>

<style>
/* Custom styles for the rubric tables */
.rubric-table td, .rubric-table th {
    border: 1px solid #9CA3AF;
    padding: 8px;
}

.rubric-table th {
    background-color: #F3F4F6;
    font-weight: 600;
}

.score-input {
    width: 60px;
    height: 32px;
    text-align: center;
    border: 1px solid #D1D5DB;
    border-radius: 4px;
    font-weight: bold;
    font-size: 16px;
}

.score-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 1px #3B82F6;
}
</style>