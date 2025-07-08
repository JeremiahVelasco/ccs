<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Evaluation extends Model
{
    protected $fillable = [
        'rubric_id',
        'panelist_id',
        'evaluable_type',
        'evaluable_id',
        'total_score',
        'comments',
        'is_completed',
        'completed_at'
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function panelist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'panelist_id');
    }

    public function evaluable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }

    public function calculateTotalScore(): float
    {
        return $this->scores()->sum('weighted_score');
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
            'total_score' => $this->calculateTotalScore()
        ]);
    }
}
