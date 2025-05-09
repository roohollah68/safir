<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array'
    ];

    public function getMessageAttribute()
    {
        return $this->data['message'];
    }

    public function getLinkAttribute()
    {
        return $this->data['link'] ?? '#';
    }
}