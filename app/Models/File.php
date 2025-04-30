<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'file_link',
        'file_path',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
