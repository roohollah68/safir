<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubProject; 

class Project extends Model
{
    use HasFactory;
    const UPDATED_AT = null;

    protected $fillable = [
        'title', 'desc', 'image', 'location', 
        'user_id', 'task_owner_id', 'deadline', 'result'
    ];

    protected $casts = [
        'deadline' => 'date'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function taskOwner() {
        return $this->belongsTo(User::class, 'task_owner_id');
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function subProjects()
    {
        return $this->hasMany(SubProject::class);
    }
}

