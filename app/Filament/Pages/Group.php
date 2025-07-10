<?php

namespace App\Filament\Pages;

use App\Models\Group as GroupModel;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Group extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static string $view = 'filament.pages.group';

    protected static ?string $navigationLabel = 'My Group';

    protected static ?string $navigationGroup = 'My Group';

    public ?array $data = [];

    public $hasGroup = false;
    public $groupInfo = null;
    public $joinMode = false;
    public $editingRole = null;
    public $editRoleData = [];
    public $addingMember = false;
    public $addMemberData = [];
    public $groupRoles = [];

    public function mount(): void
    {
        $user = Auth::user();
        $group = $user->group;

        if ($group) {
            $this->hasGroup = true;
            $this->groupInfo = $group->load('members');
            $this->groupRoles = $this->groupInfo->members->pluck('group_role')->unique();
        }
    }

    public function createGroup()
    {
        $this->validate([
            'data.name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Check if user already has a group
        if ($user->group) {
            Notification::make()
                ->title('You are already in a group')
                ->danger()
                ->send();

            return;
        }

        // Create new group
        $group = GroupModel::create([
            'name' => $this->data['name'],
            'leader_id' => $user->id,
            'group_code' => $this->generateGroupCode(),
        ]);

        // Add user to group
        $user->group_id = $group->id;
        $user->group_role = 'leader';
        $user->save();

        Notification::make()
            ->title('Group created successfully')
            ->success()
            ->send();

        $this->reset('data');
        $this->hasGroup = true;
        $this->groupInfo = $group->load('members');
    }

    public function joinGroup()
    {
        $this->validate([
            'data.group_code' => 'required|string|exists:groups,group_code',
        ]);

        $user = Auth::user();

        // Check if user already has a group
        if ($user->group) {
            Notification::make()
                ->title('You are already in a group')
                ->danger()
                ->send();

            return;
        }

        $group = GroupModel::where('group_code', $this->data['group_code'])->first();

        // Check if group exists
        if (!$group) {
            Notification::make()
                ->title('Group not found')
                ->danger()
                ->send();

            return;
        }

        // Add user to group
        $user->group_id = $group->id;
        $user->group_role = 'member';
        $user->save();

        Notification::make()
            ->title('Successfully joined group')
            ->success()
            ->send();

        $this->reset('data');
        $this->hasGroup = true;
        $this->groupInfo = $group->load('members');
    }

    public function editRole($memberId)
    {
        $user = Auth::user();

        // Only group leader can edit roles
        if (!$user->isLeader() || $user->group->leader_id !== $user->id) {
            Notification::make()
                ->title('Only group leaders can edit member roles')
                ->danger()
                ->send();
            return;
        }

        $member = User::find($memberId);

        if (!$member || $member->group_id !== $user->group_id) {
            Notification::make()
                ->title('Member not found')
                ->danger()
                ->send();
            return;
        }

        // Don't allow editing the leader's own role
        if ($member->id === $user->group->leader_id) {
            Notification::make()
                ->title('Cannot edit group leader role')
                ->danger()
                ->send();
            return;
        }

        $this->editingRole = $memberId;
        $this->editRoleData = [
            'role' => $member->group_role
        ];
    }

    public function updateRole()
    {
        $this->validate([
            'editRoleData.role' => 'required|in:member,co-leader',
        ]);

        $user = Auth::user();
        $member = User::find($this->editingRole);

        if (!$member || $member->group_id !== $user->group_id) {
            Notification::make()
                ->title('Member not found')
                ->danger()
                ->send();
            return;
        }

        $member->group_role = $this->editRoleData['role'];
        $member->save();

        Notification::make()
            ->title('Member role updated successfully')
            ->success()
            ->send();

        $this->cancelEditRole();
        $this->groupInfo = $this->groupInfo->fresh('members');
    }

    public function cancelEditRole()
    {
        $this->editingRole = null;
        $this->editRoleData = [];
    }

    public function startAddingMember()
    {
        $user = Auth::user();

        // Only group leader can add members
        if (!$user->isLeader() || $user->group->leader_id !== $user->id) {
            Notification::make()
                ->title('Only group leaders can add members')
                ->danger()
                ->send();
            return;
        }

        $this->addingMember = true;
        $this->addMemberData = [];
    }

    public function addMember()
    {
        $this->validate([
            'addMemberData.user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $newMember = User::find($this->addMemberData['user_id']);

        // Check if the user is already in a group
        if ($newMember->group_id) {
            Notification::make()
                ->title('This user is already in a group')
                ->danger()
                ->send();
            return;
        }

        // Check if the user is a student
        if (!$newMember->isStudent()) {
            Notification::make()
                ->title('Only students can be added to groups')
                ->danger()
                ->send();
            return;
        }

        // Add user to group
        $newMember->group_id = $user->group_id;
        $newMember->group_role = 'member';
        $newMember->save();

        Notification::make()
            ->title('Member added successfully')
            ->success()
            ->send();

        $this->cancelAddMember();
        $this->groupInfo = $this->groupInfo->fresh('members');
    }

    public function cancelAddMember()
    {
        $this->addingMember = false;
        $this->addMemberData = [];
    }

    public function removeMember($memberId)
    {
        $user = Auth::user();

        // Only group leader can remove members
        if (!$user->isLeader() || $user->group->leader_id !== $user->id) {
            Notification::make()
                ->title('Only group leaders can remove members')
                ->danger()
                ->send();
            return;
        }

        $member = User::find($memberId);

        if (!$member || $member->group_id !== $user->group_id) {
            Notification::make()
                ->title('Member not found')
                ->danger()
                ->send();
            return;
        }

        // Don't allow removing the group leader
        if ($member->id === $user->group->leader_id) {
            Notification::make()
                ->title('Cannot remove group leader')
                ->danger()
                ->send();
            return;
        }

        $member->group_id = null;
        $member->group_role = null;
        $member->save();

        Notification::make()
            ->title('Member removed successfully')
            ->success()
            ->send();

        $this->groupInfo = $this->groupInfo->fresh('members');
    }

    public function getAvailableStudentsProperty()
    {
        return User::students()
            ->whereNull('group_id')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->name . ' (' . $user->email . ')'];
            });
    }

    public function createProject()
    {
        return redirect()->route('filament.admin.pages.project');
    }

    public function leaveGroup()
    {
        $user = Auth::user();

        // Check if user has a group
        if (!$user->group) {
            Notification::make()
                ->title('You are not in a group')
                ->danger()
                ->send();

            return;
        }

        $group = $user->group;

        // Check if user is group leader
        if ($group->leader_id === $user->id) {
            // If there are other members, set the next member as leader
            if ($group->members()->where('id', '!=', $user->id)->count() > 0) {
                $newLeader = $group->members()->where('id', '!=', $user->id)->first();
                $group->leader_id = $newLeader->id;
                $newLeader->group_role = 'leader';
                $newLeader->save();
                $group->save();

                Notification::make()
                    ->title('Group leadership transferred')
                    ->info()
                    ->send();
            } else {
                // Delete group if no other members
                $group->delete();

                Notification::make()
                    ->title('Group deleted as you were the only member')
                    ->info()
                    ->send();
            }
        }

        // Remove user from group
        $user->group_id = null;
        $user->group_role = null;
        $user->save();

        Notification::make()
            ->title('Successfully left group')
            ->success()
            ->send();

        $this->hasGroup = false;
        $this->groupInfo = null;
    }

    public function toggleJoinMode()
    {
        $this->joinMode = !$this->joinMode;
        $this->reset('data');
    }

    protected function generateGroupCode(): string
    {
        $code = strtoupper(Str::random(6));

        // Make sure code is unique
        while (GroupModel::where('group_code', $code)->exists()) {
            $code = strtoupper(Str::random(6));
        }

        return $code;
    }

    public static function canAccess(): bool
    {
        return Auth::user()->isStudent();
    }
}
