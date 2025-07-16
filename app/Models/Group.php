<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (!$group->group_code) {
                $group->group_code = self::generateUniqueCode();
            }
        });
    }

    protected $fillable = [
        'logo',
        'name',
        'leader_id',
        'group_code',
        'course', // BSITAGD, BSITWMA, BSITDC
        'section',
        'description',
        'adviser',
        'status', // Active, Inactive
        'school_year'
    ];

    /**
     * Generate a unique group code
     * 
     * @return string
     */
    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(Str::random(3) . rand(100, 999));

            $exists = self::where('group_code', $code)->exists();
        } while ($exists);

        return $code;
    }

    public function adviser()
    {
        return $this->hasOne(User::class, 'id');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function members()
    {
        return $this->hasMany(User::class);
    }

    public function isLeader(User $user): bool
    {
        return $this->leader_id === $user->id;
    }

    public function hasAdviser(): bool
    {
        return !is_null($this->group_adviser_id);
    }

    public function hasProject(): bool
    {
        return $this->project()->exists();
    }

    public static function computeMaxGroupsAndMembers()
    {
        // Use consistent school year format: current year - next year
        $currentYear = now()->year;
        $schoolYear = $currentYear . '-' . ($currentYear + 1);

        // Get the current number of groups
        $currentGroupsCount = self::query()
            ->where('school_year', $schoolYear)
            ->count();

        // Get all students for the current school year
        $students = User::query()
            ->role('student')
            ->where('school_year', $schoolYear)
            ->count();

        // Prevent division by zero - if no groups exist, return a default max group size
        if ($currentGroupsCount === 0) {
            return 5; // Default maximum group size when no groups exist
        }

        $maxGroupSize = $students / $currentGroupsCount; // 33 / 7 = 4.714285714285714 -> 5

        return $maxGroupSize;
    }

    /**
     * Get groups that have the current school year, active status, and don't exceed group limits
     */
    public static function getAvailableGroups()
    {
        $groups = self::query()
            ->where('status', 'Active')
            ->where('course', auth()->user()->course)
            ->with('members') // Load members relationship for count
            ->get()
            ->filter(function ($group) {
                // Get the maximum group size
                $maxGroupSize = self::computeMaxGroupsAndMembers();

                // Check if the group doesn't exceed the limit
                return $group->members->count() < ceil($maxGroupSize);
            });

        return $groups;
    }
}
