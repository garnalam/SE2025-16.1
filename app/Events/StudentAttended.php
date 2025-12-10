<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentAttended implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;
    public $student;

    public function __construct($sessionId, $student) {
        $this->sessionId = $sessionId;
        $this->student = $student;
    }

    public function broadcastOn() {
        // Kênh riêng cho từng phiên điểm danh
        return new PrivateChannel('attendance.' . $this->sessionId);
    }
}