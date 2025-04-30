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
        'status', // In Progress, For Review, Done
        'progress',
        'final_grade',
        'awards',
        'completion_probability',
    ];

    protected $casts = [
        'awards' => 'array'
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
            'Introduction'
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
}
