<x-filament-panels::page>
    <div class="space-y-6">
        @if($hasGroup)
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Group Details</h2>
                    <x-filament::button color="primary" wire:click="requestMeeting">
                        Request Meeting w/ Adviser
                    </x-filament::button>
                </div>

                <div class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium">Group Name</h3>
                            <p>{{ $groupInfo->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium">Group Code</h3>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $groupInfo->group_code }}</span>
                                <button 
                                    type="button" 
                                    x-data="{}" 
                                    x-on:click="
                                        navigator.clipboard.writeText('{{ $groupInfo->group_code }}');
                                        $dispatch('notify', {
                                            message: 'Group code copied to clipboard',
                                            variant: 'success'
                                        })
                                    "
                                    class="text-primary-600 hover:text-primary-500"
                                >
                                    <x-heroicon-o-clipboard-document class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium">Group Leader</h3>
                            <p>{{ $groupInfo->leader->name ?? 'No leader' }}</p>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium">Status</h3>
                            <div>
                                @if($groupInfo->status === 'Active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Active
                                    </span>
                                @elseif($groupInfo->status === 'Inactive')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium">Course</h3>
                            <p>{{ $groupInfo->course ?? 'Not specified' }}</p>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium">Adviser</h3>
                            <p>
                                @if($groupAdviser)
                                    {{ $groupAdviser }}
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">Not assigned yet</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($groupInfo->description)
                        <div>
                            <h3 class="text-lg font-medium">Description</h3>
                            <p>{{ $groupInfo->description }}</p>
                        </div>
                    @endif 

                    <div class="border border-gray-200 rounded-lg p-2">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-medium">Members ({{ $groupInfo->members->count() }})</h3>
                        </div>

                        <div class="mt-2 space-y-2">
                            @foreach($groupInfo->members as $member)
                                <div class="flex items-center space-x-2 p-2 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <p class="font-medium">{{ $member->name }}</p>
                                            @if($member->student_id)
                                                <span class="ml-2 text-xs text-gray-400">({{ $member->student_id }})</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-400">{{ $member->email }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- No group --}}
            <div class="bg-white rounded-xl shadow-lg dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                <div class="text-center mb-8">
                    <div class="mx-auto bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-4">
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Join a Group</h2>
                </div>

                @if($this->availableGroups->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($this->availableGroups as $group)
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-200 hover:scale-[1.02] flex flex-col h-full">
                                <div class="flex-1">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $group->name }}</h3>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-users class="w-4 h-4 mr-2" />
                                                    <span>{{ $group->members->count() }} / {{ $this->maxGroupSize }} members</span>
                                                </div>
                                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                                    {{-- <x-heroicon-o-crown class="w-4 h-4 mr-2" /> --}}
                                                    <span>{{ $group->leader->name ?? 'No leader' }}</span>
                                                </div>
                                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                                    <x-heroicon-o-academic-cap class="w-4 h-4 mr-2" />
                                                    <span>{{ $group->adviser->name ?? 'No adviser' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-600 mt-auto">
                                    <x-filament::button 
                                        color="primary" 
                                        wire:click="joinGroup({{ $group->id }})"
                                        class="w-full justify-center"
                                    >
                                        <div class="flex items-center">
                                            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                                            Join Group
                                        </div>
                                    </x-filament::button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Groups Available</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            There are currently no groups available to join. Please check back later or contact your administrator.
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>