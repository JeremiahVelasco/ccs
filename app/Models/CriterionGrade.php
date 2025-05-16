<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriterionGrade extends Model
{
    protected $fillable = [
        'project_grade_id',
        'rubric_criterion_id',
        'score',
        'remarks',
    ];

    public function projectGrade()
    {
        return $this->belongsTo(ProjectGrade::class);
    }

    public function criterion()
    {
        return $this->belongsTo(RubricCriterion::class, 'rubric_criterion_id');
    }
}
