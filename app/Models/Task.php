<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'assigned_to',
        'description',
        'deadline',
        'type', // documentation or development
        'date_accomplished',
        'status', // To-do, In Progress, For Review, Approved
        'sort'
    ];

    protected $casts = [
        'assigned_to' => 'array'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo()
    {
        return $this->hasMany(User::class);
    }

    public function scopeDevelopment($query)
    {
        return $query->where('type', 'development');
    }

    public function scopeDocumentation($query)
    {
        return $query->where('type', 'documentation');
    }
}
