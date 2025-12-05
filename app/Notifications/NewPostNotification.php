<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $post; // Biến chứa thông tin bài đăng

    public function __construct($post)
    {
        $this->post = $post;
    }

    public function via($notifiable)
    {
        // Gửi qua Database (lưu lịch sử) và Broadcast (real-time)
        return ['database', 'broadcast'];
    }

    // 1. Cấu trúc lưu vào Database
    public function toArray($notifiable)
    {
        $typeName = $this->getTypeName();
        $teamName = $this->post->topic->team->name ?? 'Lớp học';
        
        // Lấy tên người đăng bài (Dynamic)
        $authorName = $this->post->user->name; 

        return [
            'post_id' => $this->post->id,
            'type' => $this->post->post_type,
            'team_name' => $teamName,
            
            'title' => 'Bài đăng mới',
            // Sửa nội dung: Thay "Giáo viên" bằng tên thật người đăng
            'message' => "[$teamName] $authorName đã đăng một $typeName",
            
            'url' => route('topics.show', $this->post->topic_id),
            'user_avatar' => $this->post->user->profile_photo_url,
        ];
    }

    // 2. Cấu trúc bắn tín hiệu Real-time (Pusher/Reverb)
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    
    private function getTypeName() {
        return match($this->post->post_type) {
            'text' => 'thông báo',
            'material' => 'tài liệu',
            'assignment' => 'bài tập',
            'quiz' => 'bài kiểm tra',
            'poll' => 'cuộc bình chọn',
            default => 'bài viết',
        };
    }
}