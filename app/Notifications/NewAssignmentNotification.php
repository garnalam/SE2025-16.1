<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $assignment; // Dữ liệu bài tập truyền vào

    public function __construct($assignment)
    {
        $this->assignment = $assignment;
    }

    // Quan trọng: Định nghĩa kênh gửi (Database để lưu, Broadcast để hiện ngay lập tức)
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    // 1. Lưu vào Database (để hiển thị trong danh sách khi bấm vào chuông)
    public function toArray($notifiable)
    {
        return [
            'title' => 'Bài tập mới: ' . $this->assignment->title,
            'url' => route('assignments.show', $this->assignment->id), // Link đến bài tập
            'created_at' => now(),
            'type' => 'assignment'
        ];
    }

    // 2. Bắn tín hiệu Real-time (để làm sáng cái chuông ngay lập tức)
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Bài tập mới: ' . $this->assignment->title,
            'url' => route('assignments.show', $this->assignment->id),
        ]);
    }
}