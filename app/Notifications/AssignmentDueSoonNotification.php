<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class AssignmentDueSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $post;

    public function __construct($post)
    {
        $this->post = $post;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $teamName = $this->post->topic->team->name ?? 'Lớp học';
        
        // Tính thời gian còn lại (để hiển thị cho sinh động)
        $dueDate = Carbon::parse($this->post->due_date);
        $diff = $dueDate->diffForHumans(); // Ví dụ: "1 day from now"

        return [
            'post_id' => $this->post->id,
            'type' => 'assignment_due_soon', // Loại thông báo
            'team_name' => $teamName,
            
            // Tiêu đề màu vàng cảnh báo
            'title' => '⏳ Sắp hết hạn nộp bài',
            
            'message' => "[$teamName] Bài tập '{$this->post->title}' sẽ hết hạn trong 24 giờ nữa. Hãy nộp ngay!",
            
            'url' => route('topics.show', $this->post->topic_id),
            'user_avatar' => null, 
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}