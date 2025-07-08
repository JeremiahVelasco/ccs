<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentsSummary extends Model
{
    protected $fillable = [
        'project_id', // Group ID
        'panel_id', // User ID of panel
        'comments'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function panel()
    {
        return $this->belongsTo(User::class, 'panel_id');
    }
}
