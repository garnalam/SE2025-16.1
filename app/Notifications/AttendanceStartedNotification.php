<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AttendanceStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $session;
    public $token;

    public function __construct($session, $token) {
        $this->session = $session;
        $this->token = $token;
    }

    public function via($notifiable) { return ['database', 'broadcast']; }

    public function toArray($notifiable) {
        return [
            'title' => '🔔 Điểm danh: ' . $this->session->team->name,
            'message' => 'Giáo viên đang điểm danh. Nhấn vào đây!',
            // Link điểm danh nhanh
            'url' => route('attendance.join', ['session' => $this->session->id, 'token' => $this->token]),
            'type' => 'attendance' // Để phân loại icon
        ];
    }
    
    public function toBroadcast($notifiable) {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
