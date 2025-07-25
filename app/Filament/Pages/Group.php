<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use App\Models\Group as GroupModel;
use App\Models\User;
use App\Services\GroupService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\PrioritySchedulerService;

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
    public $groupAdviser = null;

    public function mount(): void
    {
        $user = Auth::user();
        $group = $user->group;

        if ($group) {
            $this->hasGroup = true;
            $this->groupInfo = $group->load('members', 'leader', 'adviser');
            $this->groupRoles = $this->groupInfo->members->pluck('group_role')->unique();
            $this->groupAdviser = User::find($group->adviser)->name ?? null;
        }
    }

    public function joinGroup($groupId)
    {
        $user = Auth::user();

        // Check if user already has a group
        if ($user->group) {
            Notification::make()
                ->title('You are already in a group')
                ->danger()
                ->send();

            return;
        }

        $group = GroupModel::where('id', $groupId)->first();

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
        $this->groupInfo = $group->load('members', 'leader', 'adviser');
    }

    public function getAvailableGroupsProperty()
    {
        return GroupService::getAvailableGroups();
    }

    public function getMaxGroupSizeProperty()
    {
        return 5;
    }

    public function requestMeeting()
    {
        $user = Auth::user();
        $group = $user->group;

        $data = [
            'title' => $group->name . ' - Meeting Request with Adviser',
            'user_id' => $user->id,
            'description' => $group->name . ' - Requested a meeting with their Adviser',
        ];

        $meetingActivity = (new PrioritySchedulerService())->scheduleMeeting($data, $group);

        if ($meetingActivity['success']) {
            $meetingTime = $meetingActivity['activity']->start_date->format('l, M d, Y \a\t g:i A');
            $message = 'Your meeting with the adviser has been scheduled for ' . $meetingTime;

            // Add information about any activities that were rescheduled
            if (!empty($meetingActivity['rescheduled'])) {
                $message .= '. ' . count($meetingActivity['rescheduled']) . ' activities were rescheduled to accommodate this meeting.';
            }

            Notification::make()
                ->title('Meeting scheduled successfully')
                ->body($message)
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failed to schedule meeting')
                ->body($meetingActivity['message'])
                ->danger()
                ->send();
        }
    }

    public function updateRole()
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

        $member = User::find($this->editingRole);

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

        $member->group_role = $this->editRoleData['role'];
        $member->save();

        Notification::make()
            ->title('Role updated successfully')
            ->success()
            ->send();

        $this->editingRole = null;
        $this->editRoleData = [];

        // Refresh group info
        $this->groupInfo = $user->group->load('members', 'leader', 'adviser');
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

        // Don't allow removing the leader
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

        // Refresh group info
        $this->groupInfo = $user->group->load('members', 'leader', 'adviser');
    }

    public function cancelEditRole()
    {
        $this->editingRole = null;
        $this->editRoleData = [];
    }

    public static function canAccess(): bool
    {
        return Auth::user()->isStudent();
    }
}
