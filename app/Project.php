<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'id', 'name', 'description', 'web_url', 'avatar_url', 'namespace', 'ssh_url'
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
