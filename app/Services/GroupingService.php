<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;

class GroupingService
{
    public function addGroup(array $data)
    {
        $group = Group::create($data);

        return $group;
    }

    public function addGroupMembers(Group $group, array $memberIds, array $memberRoles)
    {
        $group->members()->attach($memberIds, ['role' => $memberRoles]);

        return $group;
    }

    public function removeGroupMembers(Group $group, array $memberIds)
    {
        $group->members()->detach($memberIds);

        return $group;
    }

    public function updateGroup(Group $group, array $data)
    {
        $group->update($data);

        return $group;
    }

    public function deleteGroup(Group $group)
    {
        $group->delete();

        return $group;
    }

    public function availableAdvisers()
    {
        $advisers = User::where('role', 'adviser')->get();

        return $advisers;
    }
}
