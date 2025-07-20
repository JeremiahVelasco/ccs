<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\ProjectPredictionService;

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

    // Removed appends to avoid issues with the custom accessor

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo()
    {
        if (!$this->assigned_to) {
            return collect();
        }
        return User::whereIn('id', $this->assigned_to);
    }

    public function getAssignedToUsersAttribute()
    {
        if (!$this->assigned_to) {
            return collect();
        }
        return User::whereIn('id', $this->assigned_to)->get();
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
        $this->is_faculty_approved = true;
        $project = Project::find($this->project_id);
        $projectPredictionService = app(ProjectPredictionService::class);
        $prediction = $projectPredictionService->predictCompletion($project);
        $project->completion_probability = $prediction['probability'];
        $project->save();

        $this->save();
    }

    public function revertApproval()
    {
        $this->status = 'For Review';
        $this->is_faculty_approved = false;
        $project = $this->project;
        $projectPredictionService = app(ProjectPredictionService::class);
        $prediction = $projectPredictionService->predictCompletion($project);
        $project->completion_probability = $prediction['probability'];
        $project->save();

        $this->save();
    }
}
