<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Badge;

class BadgeAwarded extends Notification
{
    use Queueable;

    public $badge;

    /**
     * Create a new notification instance.
     */
    public function __construct(Badge $badge)
    {
        $this->badge = $badge;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // 'database' để lưu vào bảng notifications (hiện quả chuông)
        // 'broadcast' để đẩy realtime (nếu bạn đã cài Pusher/Reverb)
        return ['database']; 
    }

    /**
     * Định dạng dữ liệu lưu vào database
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => '🎉 Huy hiệu mới!',
            'message' => 'Chúc mừng! Bạn đã nhận được huy hiệu: ' . $this->badge->name,
            'url' => route('profile.show'), // Bấm vào sẽ về trang Profile
            'icon' => $this->badge->icon_path, // Icon của huy hiệu
            'type' => 'badge',
            // Các trường phụ để tương thích với NotificationBell.vue của bạn
            'team_name' => 'Hệ thống', 
            'user_avatar' => null, // Có thể để null hoặc logo hệ thống
        ];
    }
}