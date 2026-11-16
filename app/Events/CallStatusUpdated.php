<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $studentUserId;
    public $callData;

    public function __construct($studentUserId, $callData)
    {
        $this->studentUserId = $studentUserId;
        $this->callData = $callData;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('student.' . $this->studentUserId);
    }

    public function broadcastAs()
    {
        return 'call.status.updated';
    }

    public function broadcastWith()
    {
        return $this->callData;
    }
}

