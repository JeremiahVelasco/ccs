<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationScore extends Model
{
    protected $fillable = [
        'evaluation_id',
        'rubric_criteria_id',
        'score',
        'weighted_score',
        'comments'
    ];

    protected $casts = [
        'weighted_score' => 'decimal:2'
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(RubricCriteria::class, 'rubric_criteria_id');
    }

    public function calculateWeightedScore(): float
    {
        return $this->score * $this->criteria->weight;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluationScore) {
            $evaluationScore->weighted_score = $evaluationScore->calculateWeightedScore();
        });
    }
}
