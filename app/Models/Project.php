<?php

namespace App\Models;

use App\Services\ProjectPredictionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    protected $fillable = [
        'title',
        'logo',
        'group_id',
        'description',
        'deadline',
        'panelists',
        'status', // In Progress, For Review, Done
        'progress',
        'final_grade',
        'awards',
        'completion_probability',
        'last_prediction_at',
        'prediction_config',
        'prediction_version',
    ];

    protected $casts = [
        'panelists' => 'array',
        'awards' => 'array',
        'completion_probability' => 'float',
        'deadline' => 'date',
        'prediction_config' => 'array',
        'last_prediction_at' => 'datetime',
    ];

    protected $appends = [
        'panelistStatus'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($project) {
            self::instantiateDocumentationTasks($project);
        });
    }

    public static function instantiateDocumentationTasks($project)
    {
        $documentationTasks = [
            'Title Page',
            'Grant Permission Page',
            'Certification Form',
            'IT0039 Document Checklist',
            'Approval and Acceptance Sheet',
            'Acknowledgement',
            'Table of Contents',
            'List of Tables',
            'List of Figures',
            'List of Abbreviations',
            'Introduction',
            'Purpose and Description',
            'Project Context',
            'Objectives',
            'Scope and Limitations',
            'Signiface of the Study',
            'Conceptual Framework',
            'Definition of Terms',
            'Review of Related Literature',
            'Related Literatures(5 Local & 5 Foreign)',
            'Related Studies(5 Local & 5 Foreign)',
            'Related Systems(5)',
            'Synthesis',
            'Methodology',
            'Requirements Specification',
            'Project Design',
            'Project Development Model',
            'Software Testing',
            'Software Evaluation Model',
            'Data Gathering',
            'Sampling Technique',
            'Respondents of the Study',
            'Statistical Treatment',
            'Bibliography',
            'Final Documentation'
        ];

        foreach ($documentationTasks as $documentationTask) {
            Task::create([
                'project_id' => $project->id,
                'title' => $documentationTask,
                'type' => 'documentation',
                'status' => 'To-do'
            ]);
        }

        return $documentationTasks;
    }

    public function progressAttribute()
    {
        $totalTasks = $this->tasks()->count();
        $completedTasks = $this->tasks()->whereIn('status', ['Approved', 'Done'])->count();

        $this->progress = round(($completedTasks / $totalTasks) * 100);
        $this->save();

        return $this->progress;
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function documentationTasks()
    {
        return $this->hasMany(Task::class)->where('type', 'documentation');
    }

    public function developmentTasks()
    {
        return $this->hasMany(Task::class)->where('type', 'development');
    }

    public function panelists()
    {
        return User::whereIn('id', $this->panelists ?? []);
    }

    public function getPanelistStatusAttribute(): string
    {
        return $this->isPanelistsComplete() ? 'Complete' : 'Incomplete';
    }

    public function isPanelistsComplete(): bool
    {
        return count($this->panelists ?? []) === 3;
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function predictionHistory()
    {
        return $this->hasMany(PredictionHistory::class);
    }

    public function latestPrediction()
    {
        return $this->hasOne(PredictionHistory::class)->latestOfMany();
    }

    public function predictCompletion()
    {
        $predictionService = new ProjectPredictionService();
        $prediction = $predictionService->predictCompletion($this);

        $this->update([
            'completion_probability' => $prediction['probability'],
            'last_prediction_at' => now(),
            'prediction_version' => ($this->project->prediction_version ?? 0) + 1
        ]);

        return $prediction;
    }
}
