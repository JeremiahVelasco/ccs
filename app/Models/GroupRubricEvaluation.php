<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupRubricEvaluation extends Model
{
    protected $table = 'group_rubric_evaluation';
    protected $fillable = [
        'panel_id', // User ID of panel
        'project_id', // Group ID
        'documentation_score',
        'prototype_score',
        'presentation_score',
        'total_summary_score', // Total of documentation, prototype, and presentation scores
        'presentation_of_results', // weight: x2
        'summary_of_findings', // weight x1
        'conclusion', // weight x1
        'recommendation', // weight x1
        'content', // weight x1
        'project_output', // weight x4
        'relevance_to_specialization', // weight x2
        'project_demonstration', // weight x2
        'consistency', // weight x3
        'materials', // weight x1
        'manner_of_presentation', // weight x1
        'presentation_of_project_overview', // weight x1
    ];

    /**
     * Get the total score attribute (alias for total_summary_score)
     */
    public function getTotalScoreAttribute()
    {
        return $this->total_summary_score;
    }

    /**
     * Relationship to the project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship to the panelist
     */
    public function panelist()
    {
        return $this->belongsTo(User::class, 'panel_id');
    }
}
