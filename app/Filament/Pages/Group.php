<?php

namespace App\Filament\Pages;

use App\Models\Group as GroupModel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
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

    public function mount(): void
    {
        $user = auth()->user();
        $group = $user->group;

        if ($group) {
            $this->hasGroup = true;
            $this->groupInfo = $group->load('members');
        }
    }

    public function createGroup()
    {
        $this->validate([
            'data.name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

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

        $user = auth()->user();

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

    public function createProject()
    {
        return redirect()->route('filament.admin.pages.project');
    }

    public function leaveGroup()
    {
        $user = auth()->user();

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
        return auth()->user()->isStudent();
    }
}
