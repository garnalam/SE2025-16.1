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
        // 1. Lấy tên lớp học an toàn (Dùng dấu ?-> để tránh lỗi nếu không tìm thấy lớp)
        // Logic: Submission -> Post -> Topic -> Team -> Name
        $teamName = $this->submission->post?->topic?->team?->name ?? 'Lớp học';

        // 2. Xác định tiêu đề bài tập
        $postTitle = $this->submission->post?->title ?? 'Bài tập';

        return [
            'submission_id' => $this->submission->id,
            'type' => 'grade_returned',
            'team_name' => $teamName,
            
            // Dữ liệu hiển thị
            'title' => 'Đã có điểm số',
            'message' => "[$teamName] Giáo viên đã chấm bài: " . $postTitle,
            
            // Đường dẫn: Bạn cần kiểm tra xem route này có tồn tại trong web.php không
            // Nếu lỗi route, hãy thay tạm bằng '#'
            'url' => route('submissions.index', $this->submission->post_id), 
            'grade' => $this->submission->grade,
            'user_avatar' => null, // Hoặc $this->submission->post->user->profile_photo_url nếu muốn
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}