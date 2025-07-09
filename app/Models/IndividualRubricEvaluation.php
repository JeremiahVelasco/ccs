<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualRubricEvaluation extends Model
{
    protected $table = 'individual_rubric_evaluation';

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

    /**
     * Relationship to the project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship to the panelist
     */
    public function panelist()
    {
        return $this->belongsTo(User::class, 'panel_id');
    }

    /**
     * Relationship to the student being evaluated
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
