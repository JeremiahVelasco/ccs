<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'student_id',
        'name',
        'email',
        'password',
        'group_id',
        'course',
        'group_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return str_ends_with($this->email, '@gmail.com'); // TODO : edit email to fit both emails for students and faculty
        }

        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function advised()
    {
        return $this->hasMany(Group::class, 'adviser', 'id');
    }

    public function paneledProjects()
    {
        return $this->hasMany(Project::class, 'panelists');
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function specialAccess()
    {
        return $this->hasRole(['super_admin', 'faculty']);
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function isFaculty()
    {
        return $this->hasRole('faculty');
    }

    public function isAdmin()
    {
        return $this->hasRole('super_admin');
    }

    public function isLeader()
    {
        return $this->group_role === 'leader';
    }

    public static function scopeStudents($query)
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('name', 'student');
        });
    }

    public static function scopeFaculty($query)
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('name', 'faculty');
        });
    }

    public static function scopeAdmins($query)
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        });
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
