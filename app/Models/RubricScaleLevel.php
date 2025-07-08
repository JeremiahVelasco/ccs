<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricScaleLevel extends Model
{
    protected $fillable = [
        'rubric_criteria_id',
        'points',
        'level_name',
        'description'
    ];

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(RubricCriteria::class, 'rubric_criteria_id');
    }
}
