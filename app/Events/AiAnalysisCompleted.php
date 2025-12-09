<?php

namespace App\Events;

use App\Models\Submission;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // <--- Quan trọng
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AiAnalysisCompleted implements ShouldBroadcast // <--- Phải implements cái này
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $submissionId;
    public $grade;
    public $feedback;
    public $teacherId;

    /**
     * Nhận dữ liệu từ Job
     */
    public function __construct(Submission $submission)
    {
        $this->submissionId = $submission->id;
        $this->grade = $submission->ai_suggested_grade;
        $this->feedback = $submission->ai_suggested_feedback;
        
        // Gửi cho giáo viên đang chấm bài này (User sở hữu bài post hoặc user đang login)
        // Ở đây ta lấy user sở hữu bài post (giáo viên)
        $this->teacherId = $submission->post->user_id; 
    }

    /**
     * Phát sóng trên kênh riêng của giáo viên
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->teacherId),
        ];
    }
    
    // Tên sự kiện để Frontend lắng nghe (tùy chọn, mặc định là tên Class)
    public function broadcastAs()
    {
        return 'ai.graded';
    }
}