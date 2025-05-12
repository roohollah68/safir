<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubProject extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'completed'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
