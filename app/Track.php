<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'id', 'task_id', 'status', 'spent', 'user_id'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function tasks()
    {
        return $this->belongsTo(Task::class);
    }
}
