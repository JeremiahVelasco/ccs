<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    protected $fillable = [
        'name',
        'description',
        'max_score',
    ];

    public function criteria()
    {
        return $this->hasMany(RubricCriterion::class);
    }

    public function projectGrades()
    {
        return $this->hasMany(ProjectGrade::class);
    }
}
