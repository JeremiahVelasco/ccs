<x-filament-panels::page>
    @if ($this->hasProject)
        {{ $this->table }}
    @else
        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">No project found</h2>
            </div>
        </div>
    @endif
</x-filament-panels::page>