<div class="space-y-4">
    <h3 class="text-lg font-medium">Grading Criteria</h3>

    @foreach($criteria as $criterion)
        <div class="p-4 bg-gray-50 rounded-lg space-y-3">
            <div class="flex justify-between">
                <div>
                    <h4 class="font-medium">{{ $criterion['name'] }}</h4>
                    <p class="text-sm text-gray-600">{{ $criterion['description'] }}</p>
                </div>
                <div class="text-sm text-gray-500">
                    Weight: {{ $criterion['weight'] }}% | Max Score: {{ $criterion['max_score'] }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="sm:col-span-1">
                    <div class="space-y-2">
                        <label for="criteria.{{ $criterion['id'] }}.score"
                            class="inline-flex text-sm font-medium text-gray-700">
                            Score <span class="text-danger-500">*</span>
                        </label>

                        <input type="number" id="criteria.{{ $criterion['id'] }}.score"
                            name="criteria[{{ $criterion['id'] }}][score]" min="0" max="{{ $criterion['max_score'] }}"
                            step="0.01" required
                            class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 border-gray-300" />
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <div class="space-y-2">
                        <label for="criteria.{{ $criterion['id'] }}.comments"
                            class="inline-flex text-sm font-medium text-gray-700">
                            Comments
                        </label>

                        <textarea id="criteria.{{ $criterion['id'] }}.comments"
                            name="criteria[{{ $criterion['id'] }}][comments]" rows="2"
                            class="block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 border-gray-300"></textarea>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>