<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricCriterion extends Model
{
    protected $fillable = [
        'rubric_id',
        'name',
        'description',
        'weight',
        'max_score',
    ];

    public function rubric()
    {
        return $this->belongsTo(Rubric::class);
    }

    public function criterionGrades()
    {
        return $this->hasMany(CriterionGrade::class);
    }
}
