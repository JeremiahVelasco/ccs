<?php

namespace App\Filament\Pages;

use App\Models\Activity;
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
    public $groupAdviser = null;

    public function mount(): void
    {
        $user = Auth::user();
        $group = $user->group;

        if ($group) {
            $this->hasGroup = true;
            $this->groupInfo = $group->load('members', 'leader', 'adviser');
            $this->groupRoles = $this->groupInfo->members->pluck('group_role')->unique();
            $this->groupAdviser = User::find($group->adviser)->name;
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

    public function getAvailableGroupsProperty()
    {
        return GroupModel::getAvailableGroups();
    }

    public function getMaxGroupSizeProperty()
    {
        return GroupModel::computeMaxGroupsAndMembers(auth()->user()->course);
    }

    public function requestMeeting()
    {
        $user = Auth::user();
        $group = $user->group;

        // TODO : FIX THIS

        $meetingActivity = Activity::create([
            'title' => $group->name . ' - Meeting Request with Adviser',
            'user_id' => $user->id,
            'description' => $group->name . ' - Requested a meeting with Adviser',
            'start_date' => now(),
            'end_date' => now()->addHours(1),
            'priority' => 'medium',
            'is_flexible' => false,
            'category' => 'meeting',
        ]);
    }

    public static function canAccess(): bool
    {
        return Auth::user()->isStudent();
    }
}
