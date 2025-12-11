<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ReplyCommentNotification extends Notification implements ShouldQueue
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
        // 1. Lấy tên người trả lời
        $replierName = $this->comment->user->name;
        
        // 2. Lấy tên lớp học
        $teamName = $this->comment->post?->topic?->team?->name ?? 'Lớp học';

        return [
            'comment_id' => $this->comment->id,
            'type' => 'reply_comment', // Loại thông báo: Trả lời
            'team_name' => $teamName,
            
            // Tiêu đề
            'title' => '↩️ Phản hồi mới',
            
            // ---> NỘI DUNG BẠN MUỐN <---
            'message' => "[$teamName] $replierName đã trả lời bình luận của bạn",
            
            'url' => route('topics.show', $this->comment->post->topic_id), 
            'user_avatar' => $this->comment->user->profile_photo_url,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}