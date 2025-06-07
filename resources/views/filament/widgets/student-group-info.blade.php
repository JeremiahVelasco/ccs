<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            My Group Information
        </x-slot>

        @if($group)
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    @if($group->logo)
                        <img src="{{ Storage::url($group->logo) }}" alt="{{ $group->name }}" class="w-12 h-12 rounded-full">
                    @else
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                            <x-heroicon-o-user-group class="w-6 h-6 text-gray-500" />
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold">{{ $group->name }}</h3>
                        <p class="text-sm text-gray-600">Code: {{ $group->group_code }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-900">Group Leader</h4>
                        <p class="text-sm text-gray-600">
                            {{ $leader?->name ?? 'Not assigned' }}
                            @if($user->id === $leader?->id)
                                <span class="text-green-600 font-medium">(You)</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900">Adviser</h4>
                        <p class="text-sm text-gray-600">{{ $adviser?->name ?? 'Not assigned' }}</p>
                    </div>
                </div>

                <div>
                    <h4 class="font-medium text-gray-900">Members ({{ $members?->count() ?? 0 }})</h4>
                    <div class="mt-2 space-y-1">
                        @forelse($members ?? [] as $member)
                            <div class="flex items-center justify-between text-sm">
                                <span>{{ $member->name }}</span>
                                <div class="flex items-center space-x-2">
                                    @if($member->group_role === 'leader')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Leader</span>
                                    @endif
                                    @if($member->id === $user->id)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">You</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No members found</p>
                        @endforelse
                    </div>
                </div>

                @if($project)
                    <div>
                        <h4 class="font-medium text-gray-900">Project</h4>
                        <p class="text-sm text-gray-600">{{ $project->title }}</p>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($project->status === 'Done') bg-green-100 text-green-800
                                @elseif($project->status === 'For Review') bg-yellow-100 text-yellow-800
                                @elseif($project->status === 'In Progress') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $project->status }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-6">
                <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Group Assigned</h3>
                <p class="mt-1 text-sm text-gray-500">You haven't been assigned to a group yet.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget> 