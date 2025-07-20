<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupService
{
    /**
     * Compute the maximum number of members per group for a given course
     * 
     * @param string $course
     * @return int
     */
    public static function computeMaxGroupsAndMembers(string $course): int
    {
        // Use consistent school year format: current year - next year
        $currentYear = now()->year;
        $schoolYear = $currentYear . '-' . ($currentYear + 1);

        // Get the current number of groups
        $currentGroupsCount = Group::query()
            ->where('school_year', $schoolYear)
            ->where('status', 'Active')
            ->where('course', $course)
            ->count();

        // Get all students for the current school year
        $students = User::query()
            ->role('student')
            ->where('school_year', $schoolYear)
            ->where('status', 'Active')
            ->where('course', $course)
            ->count();

        // Prevent division by zero - if no groups exist, return a default max group size
        if ($currentGroupsCount === 0) {
            return 5; // Default maximum group size when no groups exist
        }

        $maxGroupSize = $students / $currentGroupsCount;

        return ceil($maxGroupSize);
    }

    /**
     * Get groups that have the current school year, active status, and don't exceed group limits
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function getAvailableGroups()
    {
        $user = Auth::user();

        if (!$user || !$user->course) {
            return collect();
        }

        $groups = Group::query()
            ->where('status', 'Active')
            ->where('course', $user->course)
            ->with('members') // Load members relationship for count
            ->get()
            ->filter(function ($group) use ($user) {
                return $group->members->count();
            });

        return $groups;
    }

    /**
     * Check if a group can accept more members
     * 
     * @param Group $group
     * @return bool
     */
    public static function canAcceptMoreMembers(Group $group): bool
    {
        $maxGroupSize = self::computeMaxGroupsAndMembers($group->course);
        return $group->members->count() < $maxGroupSize;
    }

    /**
     * Get the current group size for a given group
     * 
     * @param Group $group
     * @return int
     */
    public static function getCurrentGroupSize(Group $group): int
    {
        return $group->members->count();
    }

    /**
     * Get the remaining slots available in a group
     * 
     * @param Group $group
     * @return int
     */
    public static function getRemainingSlots(Group $group): int
    {
        $maxGroupSize = self::computeMaxGroupsAndMembers($group->course);
        $currentSize = self::getCurrentGroupSize($group);

        return max(0, $maxGroupSize - $currentSize);
    }
}
