<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricCriteria extends Model
{
    protected $table = 'rubric_criteria';

    protected $fillable = [
        'rubric_section_id',
        'name',
        'description',
        'weight',
        'max_points',
        'order'
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(RubricSection::class, 'rubric_section_id');
    }

    public function scaleLevels(): HasMany
    {
        return $this->hasMany(RubricScaleLevel::class)->orderBy('points', 'desc');
    }

    public function evaluationScores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }

    public function getMaxWeightedScore(): float
    {
        return $this->max_points * $this->weight;
    }
}
