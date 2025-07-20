<?php

namespace App\Models;

use App\Services\GroupService;
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
        return $this->belongsTo(User::class, 'adviser');
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
        return !is_null($this->adviser);
    }

    public function hasProject(): bool
    {
        return $this->project()->exists();
    }

    public function getMaxGroupSize(): int
    {
        return GroupService::computeMaxGroupsAndMembers($this->course);
    }
}
