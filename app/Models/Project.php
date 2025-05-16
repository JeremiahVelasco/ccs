<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'logo',
        'group_id',
        'description',
        'panelists',
        'status', // In Progress, For Review, Done
        'progress',
        'final_grade',
        'awards',
        'completion_probability',
    ];

    protected $casts = [
        'panelists' => 'array',
        'awards' => 'array'
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

    public function grades()
    {
        return $this->hasMany(ProjectGrade::class);
    }
}
