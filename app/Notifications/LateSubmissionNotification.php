<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LateSubmissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        // Lấy thông tin cần thiết
        $studentName = $this->submission->user->name;
        $assignmentTitle = $this->submission->post->title;
        $teamName = $this->submission->post->topic->team->name ?? 'Lớp học';

        return [
            'submission_id' => $this->submission->id,
            'type' => 'late_submission', // Loại thông báo: Nộp muộn
            'team_name' => $teamName,
            
            // Tiêu đề cảnh báo
            'title' => '⚠️ NỘP BÀI MUỘN',
            
            // Nội dung chi tiết
            'message' => "[$teamName] $studentName đã nộp muộn bài tập: $assignmentTitle",
            
            // Link bấm vào để chấm bài luôn
            'url' => route('submissions.index', $this->submission->post_id), 
            'user_avatar' => $this->submission->user->profile_photo_url,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}