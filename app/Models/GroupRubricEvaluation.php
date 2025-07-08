<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupRubricEvaluation extends Model
{
    protected $fillable = [
        'panel_id', // User ID of panel
        'project_id', // Group ID
        'documentation_score',
        'prototype_score',
        'presentation_score',
        'total_summary_score', // Total of documentation, prototype, and presentation scores
        'presentation_of_results',
        'summary_of_findings',
        'conclusion',
        'recommendation',
        'content',
        'project_output',
        'relevance_to_specialization',
        'project_demonstration',
        'consistency',
        'materials',
        'manner_of_presentation',
        'presentation_of_project_overview'
    ];
}
