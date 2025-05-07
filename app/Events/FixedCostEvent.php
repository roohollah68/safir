<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\FixedCost;

class FixedCostEvent {
    use Dispatchable, SerializesModels;
    public $fixedCost;
    public function __construct(FixedCost $fixedCost) {
        $this->fixedCost = $fixedCost;
    }
}