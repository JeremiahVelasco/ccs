<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectGrade extends Model
{
    protected $fillable = [
        'project_id',
        'rubric_id',
        'panel_id', // user id of the panelist who graded
        'total_score',
        'remarks',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function rubric()
    {
        return $this->belongsTo(Rubric::class);
    }

    public function criterionGrades()
    {
        return $this->hasMany(CriterionGrade::class);
    }

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by_user_id');
    }
}
