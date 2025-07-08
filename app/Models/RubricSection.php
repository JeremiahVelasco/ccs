<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricSection extends Model
{
    protected $fillable = [
        'rubric_id',
        'name',
        'total_points',
        'order'
    ];

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriteria::class)->orderBy('order');
    }
}
