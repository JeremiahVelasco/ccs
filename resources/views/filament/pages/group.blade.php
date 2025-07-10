<x-filament-panels::page>
    <div class="space-y-6">
        @if($hasGroup)
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Group Details</h2>
                    <div class="space-x-2">
                        @if(!$groupInfo->hasProject())
                            <x-filament::button color="success" wire:click="createProject">
                                Create Project
                            </x-filament::button>
                        @endif
                        <x-filament::button color="danger" wire:click="leaveGroup">
                            Leave Group
                        </x-filament::button>
                    </div>
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
                            <p>{{ $groupInfo->leader->name }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium">Status</h3>
                            <div>
                                @if($groupInfo->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Pending
                                    </span>
                                @elseif($groupInfo->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Approved
                                    </span>
                                @elseif($groupInfo->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Rejected
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-lg font-medium">Course</h3>
                            <p>{{ auth()->user()->course ?? 'Not specified' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium">Adviser</h3>
                            <p>
                                @if($groupInfo->hasAdviser())
                                    {{ $groupInfo->adviserUser->name }}
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
                    
                    @if($groupInfo->hasProject())
                        <div>
                            <h3 class="text-lg font-medium">Project</h3>
                            <div class="mt-2 p-2 border border-gray-200 rounded-lg">
                                <p class="font-medium">{{ $groupInfo->project->title }}</p>
                                @if($groupInfo->project->description)
                                    <p class="text-sm text-gray-400 mt-1">
                                        {{ \Illuminate\Support\Str::limit($groupInfo->project->description, 100) }}
                                    </p>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('filament.admin.pages.project', $groupInfo->project->id) }}" class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                                        View Project Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="border border-gray-200 rounded-lg p-2">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-medium">Members ({{ $groupInfo->members->count() }})</h3>
                            @if(auth()->user()->isLeader() && auth()->user()->group->leader_id === auth()->user()->id)
                                <x-filament::button size="sm" color="success" wire:click="startAddingMember">
                                    Add Member
                                </x-filament::button>
                            @endif
                        </div>
                        
                        @if($addingMember)
                            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700/30 rounded-lg">
                                <h4 class="text-md font-medium mb-3">Add New Member</h4>
                                <form wire:submit="addMember" class="space-y-3">
                                    <div>
                                        <select 
                                            wire:model="addMemberData.user_id" 
                                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        >
                                            <option value="">Choose a student...</option>
                                            @foreach($this->availableStudents as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex gap-x-2">
                                        <x-filament::button type="submit" size="sm">
                                            Add Member
                                        </x-filament::button>
                                        <x-filament::button color="gray" size="sm" wire:click="cancelAddMember">
                                            Cancel
                                        </x-filament::button>
                                    </div>
                                </form>
                            </div>
                        @endif
                        
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
                                    
                                    @if($editingRole === $member->id)
                                        <div class="flex items-center space-x-2 gap-x-2">
                                            <select 
                                                wire:model="editRoleData.role" 
                                                class="text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                            >
                                                @foreach ($groupRoles as $role)
                                                    <option value="{{ $role }}">{{ $role }}</option>
                                                @endforeach
                                            </select>
                                            <x-filament::button size="xs" wire:click="updateRole">
                                                Save
                                            </x-filament::button>
                                            <x-filament::button color="gray" size="xs" wire:click="cancelEditRole">
                                                Cancel
                                            </x-filament::button>
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-2">
                                            @if($member->id === $groupInfo->leader_id)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                                    Leader
                                                </span>
                                            @elseif($member->group_role === 'co-leader')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    Co-Leader
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                                    Member
                                                </span>
                                            @endif
                                            
                                            <div class="gap-2">
                                                @if(auth()->user()->isLeader() && auth()->user()->group->leader_id === auth()->user()->id && $member->id !== $groupInfo->leader_id)
                                                    <x-filament::button size="xs" color="gray" wire:click="editRole({{ $member->id }})">
                                                        Edit Role
                                                    </x-filament::button>
                                                @endif
    
                                                @if(auth()->user()->isLeader() && auth()->user()->group->leader_id === auth()->user()->id && $member->id !== $groupInfo->leader_id)
                                                    <x-filament::button size="xs" color="danger" wire:click="removeMember({{ $member->id }})">
                                                        Remove Member
                                                    </x-filament::button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800">
                <h2 class="text-xl font-semibold mb-4">You don't have a group yet</h2>
                
                @if($joinMode)
                    <div class="mb-6">
                        <form wire:submit="joinGroup" class="space-y-4">
                            <div>
                                <x-filament::input.wrapper id="data.group_code" label="Enter Group Code">
                                    <x-filament::input 
                                        type="text" 
                                        wire:model="data.group_code"
                                        placeholder="Enter the 6-character group code" 
                                    />
                                </x-filament::input.wrapper>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <x-filament::button type="submit">
                                    Join Group
                                </x-filament::button>
                                
                                <x-filament::button color="gray" wire:click="toggleJoinMode">
                                    I want to create a group
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="mb-6">
                        <form wire:submit="createGroup" class="space-y-4">
                            <div>
                                <x-filament::input.wrapper id="data.name" label="Group Name">
                                    <x-filament::input 
                                        type="text" 
                                        wire:model="data.name"
                                        placeholder="Enter a name for your group" 
                                    />
                                </x-filament::input.wrapper>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <x-filament::button type="submit">
                                    Create Group
                                </x-filament::button>
                                
                                <x-filament::button color="gray" wire:click="toggleJoinMode">
                                    I want to join a group
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                @endif
                
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-lg font-medium mb-2">How groups work</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-600 dark:text-gray-300">
                        <li>You can create a new group or join an existing one using a group code</li>
                        <li>The person who creates a group becomes the group leader</li>
                        <li>You can share your group code with other students so they can join</li>
                        <li>A faculty adviser will be assigned to your group</li>
                        <li>Once your group is approved, you can create a project</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>