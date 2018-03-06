<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'id', 'author_id', 'closed_at', 'confidential', 'description', 'due_date', 'iid',
        'last_edited_at', 'last_edited_by_id', 'milestone_id', 'moved_to_id', 'project_id',
        'relative_position', 'state', 'time_estimate', 'title', 'url', 'total_time_spent',
        'human_total_time_spent', 'human_time_estimate'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function users() {
        return $this->belongsToMany(User::class);
    }

}