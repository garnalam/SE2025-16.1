<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage; // <--- Dễ bị thiếu dòng này
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function via($notifiable)
    {
        // Gửi qua Database và Real-time
        return ['database', 'broadcast'];
    }

public function toArray($notifiable)
{
    $teamName = $this->submission->post?->topic?->team?->name ?? 'Lớp học';
    $postTitle = $this->submission->post?->title ?? 'Bài tập';

    return [
        'submission_id' => $this->submission->id,
        'type' => 'grade_returned',
        'team_name' => $teamName,
        'title' => 'Đã có điểm số',
        'message' => "[$teamName] Giáo viên đã chấm bài: " . $postTitle,
        
        // 👇 SỬA DÒNG NÀY: Dẫn về trang Topic (Feed) và cuộn tới bài đăng đó
        'url' => route('topics.show', [
            'topic' => $this->submission->post->topic_id, 
            '#post-' . $this->submission->post_id 
        ], absolute: false), 
        
        'grade' => $this->submission->grade,
        'user_avatar' => null, 
    ];
}

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}