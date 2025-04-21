<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    const UPDATED_AT = null;

    protected $table = 'projects';

    protected $fillable = [
        'title',
        'desc',
        'image',
        'location'
    ];
}

