<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        // 1. Lấy thông tin cần thiết
        // Quan hệ: Comment -> User (người comment)
        $commenterName = $this->comment->user->name;
        
        // Quan hệ: Comment -> Post -> Topic -> Team (Lớp học)
        $teamName = $this->comment->post?->topic?->team?->name ?? 'Lớp học';
        
        // Lấy tiêu đề bài viết (cắt ngắn cho gọn nếu dài quá)
        $postTitle = \Illuminate\Support\Str::limit($this->comment->post->title ?? 'Bài viết', 30);

        return [
            'comment_id' => $this->comment->id,
            'type' => 'new_comment', // Đánh dấu loại để frontend phân biệt
            'team_name' => $teamName,
            
            // Tiêu đề
            'title' => '💬 Bình luận mới',
            
            'message' => "[$teamName] $commenterName đã bình luận vào bài đăng của bạn",            
            // Link bấm vào bài viết
            'url' => route('topics.show', $this->comment->post->topic_id), 
            'user_avatar' => $this->comment->user->profile_photo_url,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}