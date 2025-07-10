<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentGroupController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $group = Group::where('id', $user->group_id)->with('members', 'leader', 'adviser')->get();

        return response()->json($group);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $group = Group::create([
            'name' => $request->name,
            'leader_id' => $user->id,
            'group_code' => $this->generateGroupCode(),
        ]);

        $user->group_id = $group->id;
        $user->group_role = 'leader';
        $user->save();

        return response()->json($group);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $group = Group::where('id', $user->group_id)->update([
            'name' => $request->name,
        ]);

        return response()->json($group);
    }

    public function destroy()
    {
        $user = auth()->user();
        $group = Group::where('id', $user->group_id)->delete();

        $user->group_id = null;
        $user->save();

        return response()->json($group);
    }

    public function joinGroup(Request $request)
    {
        $user = auth()->user();
        $group = Group::where('group_code', $request->group_code)->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $user->group_id = $group->id;
        $user->group_role = 'member';
        $user->save();

        return response()->json($group);
    }

    public function leaveGroup()
    {
        $user = auth()->user();
        $group = $user->group;

        // If user is leader, transfer leadership to the next member
        if ($group->leader_id === $user->id) {
            if ($group->members()->where('id', '!=', $user->id)->count() > 0) {
                $newLeader = $group->members()->where('id', '!=', $user->id)->first();
                $newLeader->group_role = 'leader';
                $newLeader->save();
                $group->leader_id = $newLeader->id;
                $group->save();

                $user->group_id = null;
                $user->group_role = null;
                $user->save();
            } else {
                // Delete group if no other members
                $group->delete();

                $user->group_id = null;
                $user->group_role = null;
                $user->save();
            }
        }

        return response()->json(['message' => 'You have left the group']);
    }

    public function getStudentsWithoutGroup()
    {
        $students = User::students()->whereNull('group_id')->get();
        return response()->json($students);
    }

    public function addMember(Request $request)
    {
        $user = auth()->user();
        $group = $user->group;

        $user = User::where('id', $request->user_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($group->members()->where('id', $user->id)->exists()) {
            return response()->json(['message' => 'User already in group'], 400);
        }

        // Add user to group
        $user->group_id = $group->id;
        $user->group_role = 'member';
        $user->save();

        return response()->json($group);
    }

    public function removeMember(Request $request)
    {
        $user = auth()->user();
        $group = $user->group;

        $user = User::where('id', $request->user_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($group->members()->where('id', $user->id)->exists()) {
            return response()->json(['message' => 'User already in group'], 400);
        }

        // Remove user from group
        $user->group_id = null;
        $user->group_role = null;
        $user->save();

        return response()->json($group);
    }

    protected function generateGroupCode(): string
    {
        $code = strtoupper(Str::random(6));

        // Make sure code is unique
        while (Group::where('group_code', $code)->exists()) {
            $code = strtoupper(Str::random(6));
        }

        return $code;
    }
}
