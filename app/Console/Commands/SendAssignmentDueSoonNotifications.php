<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Notifications\AssignmentDueSoonNotification;
use Illuminate\Support\Facades\Notification;

class SendAssignmentDueSoonNotifications extends Command
{
    // Tên lệnh để chạy trong terminal
    protected $signature = 'assignments:notify-due-soon';
    protected $description = 'Gửi thông báo cho học sinh khi bài tập còn 24h nữa hết hạn';

    public function handle()
    {
        $this->info('Đang tìm các bài tập sắp hết hạn...');

        // 1. Tìm các bài tập có hạn nộp trong khoảng [23h tới -> 24h tới]
        // Tại sao không tìm chính xác 24h? Vì cronjob chạy theo giờ, nên ta lấy khoảng để không bị sót.
        $startWindow = now()->addHours(23);
        $endWindow = now()->addHours(24);

        $assignments = Post::whereIn('post_type', ['assignment', 'quiz'])
            ->whereBetween('due_date', [$startWindow, $endWindow])
            ->with('topic.team') // Eager load để chạy cho nhanh
            ->get();

        foreach ($assignments as $assignment) {
            
            // 2. Lấy danh sách học sinh trong lớp
            $students = $assignment->topic->team->users()
                        ->where('users.role', 'student') // <--- SỬA THÀNH: users.role (Chỉ định rõ bảng users)
                        ->get();

            foreach ($students as $student) {
                // 3. Kiểm tra: Học sinh này ĐÃ NỘP BÀI CHƯA?
                // Nếu nộp rồi thì thôi không báo nữa cho đỡ phiền
                $hasSubmitted = $assignment->submissions()
                                ->where('user_id', $student->id)
                                ->exists();

                if (!$hasSubmitted) {
                    $student->notify(new AssignmentDueSoonNotification($assignment));
                    $this->info("Đã gửi báo hết hạn bài '{$assignment->title}' cho HS: {$student->name}");
                }
            }
        }

        $this->info('Hoàn tất quét bài tập.');
    }
}