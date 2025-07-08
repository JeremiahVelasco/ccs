<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSummary extends Model
{
    protected $fillable = [
        'project_id', // Group ID
        'panel_id', // User ID of panel
        'group_presentation_score',
        'member_1_score',
        'member_2_score',
        'member_3_score',
        'member_4_score',
        'member_5_score',
        'member_6_score',
        'member_7_score',
    ];
}
