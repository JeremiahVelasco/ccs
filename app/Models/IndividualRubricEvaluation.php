<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualRubricEvaluation extends Model
{
    protected $fillable = [
        'panel_id', // User ID of panel
        'project_id', // Group ID
        'student_id',
        'subject_mastery',
        'ability_to_answer_questions',
        'delivery',
        'verbal_and_nonverbal_ability',
        'grooming'
    ];
}
