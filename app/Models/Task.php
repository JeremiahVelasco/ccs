<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        'file_path',
        'sort',
        'is_faculty_approved'
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

    public function markAsDone()
    {
        $this->status = 'Approved';

        $this->save();
    }

    public function revertApproval()
    {
        $this->status = 'For Review';

        $this->save();
    }

    public function hasFile()
    {
        return $this->file_path !== null && Storage::disk('public')->exists($this->file_path);
    }
}
